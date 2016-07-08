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
		/* Mandates */
		$mandates = $this->client->get_mandates( 'cst_xMUuzjtSW5' );

		echo '<h3>Mandates</h3>';

		foreach( $mandates->data as $mandate ) {
			printf(
				'<code>%s</code> %s (%s)<br>',
				$mandate->id,
				$mandate->method,
				$mandate->status
			);
		}

		/* Subscriptions */
		$subscriptions = $this->client->get_subscriptions( 'cst_xMUuzjtSW5' );

		echo '<h3>Subscriptions</h3>';

		foreach( $subscriptions->data as $subscription ) {
			printf(
				'<code>%s</code> %s (%s)<br>',
				$subscription->id,
				$subscription->description,
				$subscription->status
			);
		}

		echo '<hr>';

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
			Pronamic_WP_Pay_PaymentMethods::IDEAL       => Pronamic_WP_Pay_Mollie_Methods::IDEAL,
			Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD => Pronamic_WP_Pay_Mollie_Methods::CREDITCARD,
			Pronamic_WP_Pay_PaymentMethods::MISTER_CASH => Pronamic_WP_Pay_Mollie_Methods::MISTERCASH,
			Pronamic_WP_Pay_PaymentMethods::PAYPAL      => Pronamic_WP_Pay_Mollie_Methods::PAYPAL,
			Pronamic_WP_Pay_PaymentMethods::SOFORT      => Pronamic_WP_Pay_Mollie_Methods::SOFORT,
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

		$customer_id = $this->client->get_customer_id();

		$payment->set_meta( 'mollie_customer_id', $customer_id );

		$request->amount       = $payment->get_amount();
		$request->description  = $payment->get_description();
		$request->redirect_url = $payment->get_return_url();
		$request->webhook_url  = $this->get_webhook_url();
		$request->locale       = Pronamic_WP_Pay_Mollie_LocaleHelper::transform( $payment->get_language() );
		$request->customer_id  = $customer_id;
		$request->method       = Pronamic_WP_Pay_Mollie_Methods::transform( $payment_method );

		if ( empty( $request->method ) && ! empty( $payment_method ) ) {
			// Leap of faith if the WordPress payment method could not transform to a Mollie method?
			$request->method = $payment_method;
		}

		// Recurring payments
		if ( $payment->get_recurring() ) {
			if ( is_object( $this->client->get_error() ) ) {
				// Set error if customer could not be created
				$this->error = $this->client->get_error();

				// Prevent payment from being created without customer
				return;
			}

			$recurring_type = $payment->get_recurring();

			if ( Pronamic_WP_Pay_Recurring::SUBSCRIPTION === $payment->get_recurring() ) {
				$subscription = $this->create_subscription( $payment );

				$recurring_type = Pronamic_WP_Pay_Recurring::RECURRING;

				if ( is_wp_error( $subscription ) ) {
					$recurring_type = Pronamic_WP_Pay_Mollie_Recurring::FIRST;
				}
			}

			switch ( $recurring_type ) {
				case Pronamic_WP_Pay_Recurring::FIRST :
					$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::FIRST;

					break;
				case Pronamic_WP_Pay_Recurring::RECURRING :
					$request->recurring_type = Pronamic_WP_Pay_Mollie_Recurring::RECURRING;
					$payment->set_action_url( $payment->get_return_url() );

					break;
			}
		}

		if ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $payment_method ) {
			// If payment method is iDEAL we set the user chosen issuer ID.
			$request->issuer = $payment->get_issuer();
		}

		if ( Pronamic_WP_Pay_Recurring::SUBSCRIPTION === $payment->get_recurring() && Pronamic_WP_Pay_Recurring::RECURRING === $recurring_type ) {
			return;
		}

		$result = $this->client->create_payment( $request );

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return;
		}

		$payment->set_transaction_id( $result->id );

		if( '' === $payment->get_action_url() ) {
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

		if ( ! $mollie_payment && Pronamic_WP_Pay_Recurring::SUBSCRIPTION === $payment->get_recurring() ) {
			$customer_id     = $payment->get_meta( 'mollie_customer_id' );
			$subscription_id = $payment->get_transaction_id();

			$mollie_payment = $this->client->get_subscription( $customer_id, $subscription_id );
		}

		if ( ! $mollie_payment ) {
			$this->error = $this->client->get_error();

			return;
		}

		$payment->set_status( Pronamic_WP_Pay_Mollie_Statuses::transform( $mollie_payment->status ) );

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

	/////////////////////////////////////////////////

	/**
	 * Create subscription.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function create_subscription( Pronamic_Pay_Payment $payment ) {
		$data = $this->client->create_subscription(
			$payment->meta['mollie_customer_id'], // customer_id
			$payment->get_recurring_amount(), // amount
			$payment->get_recurring_frequency(), // times
			sprintf( // interval
				'%s %s',
				$payment->get_recurring_interval(),
				$payment->get_recurring_interval_period()
			),
			$payment->get_recurring_description(), // description
			$this->get_webhook_url()
		);

		$this->update_subscription_payment_note( $payment, (array) $data );

		if ( ! $data ) {
			return $this->client->get_error();
		}

		$payment->set_transaction_id( $data->id );
	}

	/**
	 * Cancel subscription.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function cancel_subscription( Pronamic_Pay_Payment $payment ) {
		$subscription_id = $payment->get_transaction_id();

		if ( ! $subscription_id ) {
			return;
		}

		$data = $this->client->cancel_subscription(
			$payment->get_meta( 'mollie_customer_id' ),
			$subscription_id
		);

		if ( ! $data ) {
			$this->error = $this->client->get_error();

			return;
		}

		$this->update_subscription_payment_note( $payment, (array) $data );
	}

	/**
	 * Update subscription payment note.
	 *
	 * @param Pronamic_Pay_Payment $payment
	 * @param array $data
	 */
	private function update_subscription_payment_note( Pronamic_Pay_Payment $payment, $data ) {
		$labels = array(
			'resource'          => __( 'Resource', 'pronamic_ideal' ),
			'id'                => __( 'Subscription ID', 'pronamic_ideal' ),
			'customerId'        => __( 'Customer ID', 'pronamic_ideal' ),
			'mode'              => __( 'Mode', 'pronamic_ideal' ),
			'createdDatetime'   => __( 'Created date', 'pronamic_ideal' ),
			'status'            => __( 'Status', 'pronamic_ideal' ),
			'amount'            => __( 'Amount', 'pronamic_ideal' ),
			'times'             => __( 'Times', 'pronamic_ideal' ),
			'interval'          => __( 'Interval', 'pronamic_ideal' ),
			'description'       => __( 'Description', 'pronamic_ideal' ),
			'method'            => __( 'Method', 'pronamic_ideal' ),
			'cancelledDatetime' => __( 'Cancelled date', 'pronamic_ideal' ),
		);

		$note = '';

		$note .= '<p>';
		$note .= __( 'Mollie subscription data in response message:', 'pronamic_ideal' );
		$note .= '</p>';

		$note .= '<dl>';

		foreach ( $labels as $key => $label ) {
			if ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) {
				$note .= sprintf( '<dt>%s</dt>', esc_html( $label ) );
				$note .= sprintf( '<dd>%s</dd>', esc_html( $data[ $key ] ) );
			}
		}

		$note .= '</dl>';

		$payment->add_note( $note );
	}
}
