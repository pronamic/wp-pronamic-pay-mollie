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
			return false;
		} elseif ( '.dev' === substr( $host, -4 ) ) {
			// Mollie doesn't allow the TLD .dev
			return false;
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

		if ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $payment_method ) {
			// If payment method is iDEAL we set the user chosen issuer ID.
			$request->issuer = $payment->get_issuer();
		}

		$result = $this->client->create_payment( $request );

		if ( ! $result ) {
			$this->error = $this->client->get_error();

			return;
		}

		$payment->set_transaction_id( $result->id );
		$payment->set_action_url( $result->links->paymentUrl );
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
}
