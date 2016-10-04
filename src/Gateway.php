<?php

/**
 * Title: Mollie
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.6
 * @since 1.1.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Gateway extends Pronamic_WP_Pay_Gateway {
	/**
	 * Slug of this gateway
	 *
	 * @var string
	 */
	const SLUG = 'mollie';

	/////////////////////////////////////////////////

	/**
	 * Constructs and initializes an Mollie gateway
	 *
	 * @param Pronamic_WP_Pay_Gateways_Mollie_Config $config
	 */
	public function __construct( Pronamic_WP_Pay_Gateways_Mollie_Config $config ) {
		parent::__construct( $config );

		$this->set_method( Pronamic_WP_Pay_Gateway::METHOD_HTTP_REDIRECT );
		$this->set_has_feedback( true );
		$this->set_amount_minimum( 1.20 );
		$this->set_slug( self::SLUG );

		$this->client = new Pronamic_WP_Pay_Gateways_Mollie_Client( $config->api_key );
		$this->client->set_mode( $config->mode );
	}

	/////////////////////////////////////////////////

	/**
	 * Get issuers
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_issuers()
	 */
	public function get_issuers() {
		$groups = array();

		$result = $this->client->get_issuers();

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return $groups;
		}

		$groups[] = array(
			'options' => $result,
		);

		return $groups;
	}

	/////////////////////////////////////////////////

	public function get_issuer_field() {
		if ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $this->get_payment_method() ) {
			return array(
				'id'       => 'pronamic_ideal_issuer_id',
				'name'     => 'pronamic_ideal_issuer_id',
				'label'    => __( 'Choose your bank', 'pronamic_ideal' ),
				'required' => true,
				'type'     => 'select',
				'choices'  => $this->get_transient_issuers(),
			);
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Is there a valid mandate for customer?
	 *
	 * @see Pronamic_WP_Pay_Gateway::has_valid_mandate()
	 */
	public function has_valid_mandate() {
		$meta_key    = sprintf( '_pronamic_pay_mollie_customer_id_%s', $this->config->mode );
		$customer_id = get_user_meta( get_current_user_id(), $meta_key, true );

		if ( 1 ) {
			/* Payments */
			$payments = $this->client->get_payments();

			if ( $payments ) {
				echo '<h3>Payments</h3>';

				foreach ( $payments->data as $payment ) {
					printf(
						'%s <code>%s</code> %s (%s)<br>',
						esc_html( $payment->createdDatetime ),
						esc_html( $payment->id ),
						esc_html( $payment->amount ),
						esc_html( $payment->status )
					);
				}
			}

			/* Mandates */
			$mandates = $this->client->get_mandates( $customer_id );

			if ( $mandates ) {
				echo '<h3>Mandates</h3>';

				foreach ( $mandates->data as $mandate ) {
					printf(
						'<code>%s</code> %s (%s)<br>',
						esc_html( $mandate->id ),
						esc_html( $mandate->method ),
						esc_html( $mandate->status )
					);
				}
			}

			echo '<hr>';
		}

		return $this->client->has_valid_mandate( $customer_id );
	}

	/**
	 * Get formatted date and time of first valid mandate.
	 *
	 * @see Pronamic_WP_Pay_Gateway::has_valid_mandate()
	 */
	public function get_first_valid_mandate_datetime() {
		$meta_key    = sprintf( '_pronamic_pay_mollie_customer_id_%s', $this->config->mode );
		$customer_id = get_user_meta( get_current_user_id(), $meta_key, true );

		return $this->client->get_first_valid_mandate_datetime( $customer_id );
	}

	/////////////////////////////////////////////////

	/**
	 * Get payment methods
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_payment_methods()
	 */
	public function get_payment_methods() {
		$groups = array();

		$result = $this->client->get_payment_methods();

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return $groups;
		}

		$groups[] = array(
			'options' => $result,
		);

		return $groups;
	}

	/////////////////////////////////////////////////

	/**
	 * Get supported payment methods
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_supported_payment_methods()
	 */
	public function get_supported_payment_methods() {
		return array(
			Pronamic_WP_Pay_PaymentMethods::IDEAL,
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD,
			Pronamic_WP_Pay_PaymentMethods::BANCONTACT,
			Pronamic_WP_Pay_PaymentMethods::PAYPAL,
			Pronamic_WP_Pay_PaymentMethods::SOFORT,
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL,
		);
	}

	/////////////////////////////////////////////////

	/**
	 * Get webhook URL for Mollie.
	 *
	 * @return string
	 */
	private function get_webhook_url() {
		$url = home_url( '/' );

		$host = parse_url( $url, PHP_URL_HOST );

		if ( 'localhost' === $host ) {
			// Mollie doesn't allow localhost
			return null;
		} elseif ( '.dev' === substr( $host, -4 ) ) {
			// Mollie doesn't allow the TLD .dev
			return null;
		}

		$url = add_query_arg( 'mollie_webhook', '', $url );

		return $url;
	}

	/////////////////////////////////////////////////

	/**
	 * Start
	 *
	 * @see Pronamic_WP_Pay_Gateway::start()
	 */
	public function start( Pronamic_Pay_Payment $payment ) {
		$request = new Pronamic_WP_Pay_Gateways_Mollie_PaymentRequest();

		$payment_method = $payment->get_method();

		$request->amount       = $payment->get_amount();
		$request->description  = $payment->get_description();
		$request->redirect_url = $payment->get_return_url();
		$request->webhook_url  = $this->get_webhook_url();
		$request->locale       = Pronamic_WP_Pay_Mollie_LocaleHelper::transform( $payment->get_language() );
		$request->method       = Pronamic_WP_Pay_Mollie_Methods::transform( $payment_method );

		if ( empty( $request->method ) && ! empty( $payment_method ) ) {
			// Leap of faith if the WordPress payment method could not transform to a Mollie method?
			$request->method = $payment_method;
		}

		// Customer ID
		$customer_id = $this->client->get_customer_id( $payment->get_customer_name() );
		$payment->set_meta( 'mollie_customer_id', $customer_id );

		// Subscriptions
		$subscription = $payment->get_subscription();

		if ( $subscription && Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL === $payment_method ) {
			if ( is_object( $this->client->get_error() ) ) {
				// Set error if customer could not be created
				$this->error = $this->client->get_error();

				// Prevent subscription payment from being created without customer
				return;
			}

			$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::RECURRING;
			$request->method         = Pronamic_WP_Pay_Mollie_Methods::DIRECT_DEBIT;

			if ( $subscription->has_valid_payment() && ! $customer_id ) {
				// Get customer ID from first payment
				$first       = $subscription->get_first_payment();
				$customer_id = $first->get_meta( 'mollie_customer_id' );

				$payment->set_meta( 'mollie_customer_id', $customer_id );
			}

			$can_user_interact       = in_array( $payment->get_source(), array( 'gravityformsideal' ), true );

			if ( ! $this->client->has_valid_mandate( $customer_id ) && ( ! $subscription->has_valid_payment() || $can_user_interact ) ) {
				// First payment or if user interaction is possible and no valid mandates are found
				$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::FIRST;
				$request->method         = Pronamic_WP_Pay_Mollie_Methods::IDEAL;
			}

			if ( Pronamic_WP_Pay_Mollie_Recurring::RECURRING === $request->recurring_type ) {
				// Recurring payment
				$payment->set_action_url( $payment->get_return_url() );

				if ( $subscription->has_valid_payment() ) {
					// Use subscription amount if this is not the initial payment.
					$payment->amount = $subscription->get_amount();
				}
			}
		}

		if ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $payment_method ) {
			// If payment method is iDEAL we set the user chosen issuer ID.
			$request->issuer = $payment->get_issuer();
		}

		$request->customer_id = $customer_id;

		$result = $this->client->create_payment( $request );

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return;
		}

		if ( $subscription && Pronamic_WP_Pay_Mollie_Recurring::RECURRING === $request->recurring_type ) {
			$subscription->set_status( Pronamic_WP_Pay_Mollie_Statuses::transform( $result->status ) );
		}

		$payment->set_transaction_id( $result->id );

		if ( '' === $payment->get_action_url() ) {
			$payment->set_action_url( $result->links->paymentUrl );
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Update status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function update_status( Pronamic_Pay_Payment $payment ) {
		$mollie_payment = $this->client->get_payment( $payment->get_transaction_id() );

		if ( ! $mollie_payment ) {
			$this->error = $this->client->get_error();

			return;
		}

		$status = Pronamic_WP_Pay_Mollie_Statuses::transform( $mollie_payment->status );

		$payment->set_status( $status );

		$subscription = $payment->get_subscription();

		if ( $subscription && '' === $subscription->get_transaction_id() ) {
			// First payment or non-subscription recurring payment,
			// use payment status for subscription too.
			$subscription->set_status( $status );
		}

		if ( isset( $mollie_payment->details ) ) {
			$details = $mollie_payment->details;

			if ( isset( $details->consumerName ) ) {
				$payment->set_consumer_name( $details->consumerName );
			}

			if ( isset( $details->consumerAccount ) ) {
				$payment->set_consumer_iban( $details->consumerAccount );
			}
		}
	}
}
