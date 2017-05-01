<?php

/**
 * Title: Mollie
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.14
 * @since 1.1.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Gateway extends Pronamic_WP_Pay_Gateway {
	/**
	 * Slug of this gateway
	 *
	 * @var string
	 */
	const SLUG = 'mollie';

	/**
	 * Meta key for customer ID.
	 *
	 * @var string
	 */
	private $meta_key_customer_id = '_pronamic_pay_mollie_customer_id';

	/////////////////////////////////////////////////

	/**
	 * Constructs and initializes an Mollie gateway
	 *
	 * @param Pronamic_WP_Pay_Gateways_Mollie_Config $config
	 */
	public function __construct( Pronamic_WP_Pay_Gateways_Mollie_Config $config ) {
		parent::__construct( $config );

		$this->supports = array(
			'payment_status_request',
			'recurring_direct_debit',
			'recurring_credit_card',
			'recurring',
		);

		$this->set_method( Pronamic_WP_Pay_Gateway::METHOD_HTTP_REDIRECT );
		$this->set_has_feedback( true );
		$this->set_amount_minimum( 1.20 );
		$this->set_slug( self::SLUG );

		$this->client = new Pronamic_WP_Pay_Gateways_Mollie_Client( $config->api_key );
		$this->client->set_mode( $config->mode );

		if ( 'test' === $config->mode ) {
			$this->meta_key_customer_id = '_pronamic_pay_mollie_customer_id_test';
		}
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

	/**
	 * Get Mollie customer ID by the specified WordPress user ID.
	 *
	 * @param int $user_id
	 * @return string
	 */
	private function get_customer_id_by_wp_user_id( $user_id ) {
		return get_user_meta( $user_id, $this->meta_key_customer_id, true );
	}

	private function update_wp_user_customer_id( $user_id, $customer_id ) {
		update_user_meta( $user_id, $this->meta_key_customer_id, $customer_id );
	}

	/**
	 * Is there a valid mandate for customer?
	 *
	 * @see Pronamic_WP_Pay_Gateway::has_valid_mandate()
	 */
	public function has_valid_mandate( $payment_method = '' ) {
		return $this->client->has_valid_mandate( $this->get_customer_id_by_wp_user_id( get_current_user_id() ), $payment_method );
	}

	/**
	 * Get formatted date and time of first valid mandate.
	 *
	 * @see Pronamic_WP_Pay_Gateway::has_valid_mandate()
	 */
	public function get_first_valid_mandate_datetime( $payment_method = '' ) {
		return $this->client->get_first_valid_mandate_datetime( $this->get_customer_id_by_wp_user_id( get_current_user_id() ), $payment_method );
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
			Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER,
			Pronamic_WP_Pay_PaymentMethods::BITCOIN,
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD,
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT,
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL,
			Pronamic_WP_Pay_PaymentMethods::BANCONTACT,
			Pronamic_WP_Pay_PaymentMethods::PAYPAL,
			Pronamic_WP_Pay_PaymentMethods::SOFORT,
			Pronamic_WP_Pay_PaymentMethods::IDEAL,
			Pronamic_WP_Pay_PaymentMethods::KBC,
			Pronamic_WP_Pay_PaymentMethods::BELFIUS,
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
		$user_id = $payment->post->post_author;

		$customer_id = $this->get_customer_id_by_wp_user_id( $user_id );

		if ( empty( $customer_id ) || ! $this->client->get_customer( $customer_id ) ) {
			$customer_id = $this->client->create_customer( $payment->get_email(), $payment->get_customer_name() );

			if ( $customer_id ) {
				$this->update_wp_user_customer_id( $user_id, $customer_id );
			}
		}

		$payment->set_meta( 'mollie_customer_id', $customer_id );

		// Subscriptions
		$subscription = $payment->get_subscription();

		$subscription_methods = array(
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD,
			Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL,
		);

		if ( $subscription && in_array( $payment_method, $subscription_methods, true ) ) {
			if ( is_object( $this->client->get_error() ) ) {
				// Set error if customer could not be created
				$this->error = $this->client->get_error();

				// Prevent subscription payment from being created without customer
				return;
			}

			$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::RECURRING;

			if ( Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL === $payment_method ) {
				// Use direct debit for recurring payments with payment method `Direct Debit (mandate via iDEAL)`.
				$request->method = Pronamic_WP_Pay_Mollie_Methods::DIRECT_DEBIT;
			}

			if ( $subscription->has_valid_payment() && ! $customer_id ) {
				// Get customer ID from first payment
				$first       = $subscription->get_first_payment();
				$customer_id = $first->get_meta( 'mollie_customer_id' );

				$payment->set_meta( 'mollie_customer_id', $customer_id );
			}

			// Mandate payment method to check for.
			$mandate_method = $payment_method;

			if ( Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL === $mandate_method ) {
				$mandate_method = Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT;
			}

			if ( ! $payment->get_recurring() ) {
				// First payment without valid mandate
				$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::FIRST;

				if ( Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL === $payment_method ) {
					// Use IDEAL for first payments with DIRECT_DEBIT_IDEAL payment method
					$request->method = Pronamic_WP_Pay_Mollie_Methods::IDEAL;
				}
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

		if ( Pronamic_WP_Pay_Mollie_Methods::IDEAL === $request->method ) {
			// If payment method is iDEAL we set the user chosen issuer ID.
			$request->issuer = $payment->get_issuer();
		}

		$request->customer_id = $customer_id;

		$result = $this->client->create_payment( $request );

		if ( ! $result ) {
			if ( false !== $subscription ) {
				$subscription->set_status( Pronamic_WP_Pay_Statuses::FAILURE );
			}

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
			$payment->set_status( Pronamic_WP_Pay_Statuses::FAILURE );

			if ( '' !== $payment->get_transaction_id() ) {
				// Use payment status as subscription status only if there's a transaction ID

				$subscription = $payment->get_subscription();
				$subscription->set_status( Pronamic_WP_Pay_Statuses::FAILURE );
			}

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
