<?php

/**
 * Title: Mollie
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Client {
	/**
	 * Mollie API endpoint URL
	 *
	 * @var string
	 */
	const API_URL = 'https://api.mollie.nl/v1/';

	/////////////////////////////////////////////////

	/**
	 * Mollie API Key ID
	 *
	 * @var string
	 */
	private $api_key;

	/////////////////////////////////////////////////

	/**
	 * Error
	 *
	 * @var WP_Error
	 */
	private $error;

	/////////////////////////////////////////////////

	/**
	 * Constructs and initializes an Mollie client object
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/////////////////////////////////////////////////

	/**
	 * Error
	 *
	 * @return WP_Error
	 */
	public function get_error() {
		return $this->error;
	}

	//////////////////////////////////////////////////

	/**
	 * Send request with the specified action and parameters
	 *
	 * @param string $action
	 * @param array $parameters
	 */
	private function send_request( $end_point, $method = 'POST', array $data = array() ) {
		$url = self::API_URL . $end_point;

		return wp_remote_request( $url, array(
			'method'    => $method,
			'headers'   => array(
				'Authorization' => 'Bearer ' . $this->api_key,
			),
			'body'      => $data,
		) );
	}

	/////////////////////////////////////////////////

	public function create_payment( Pronamic_WP_Pay_Gateways_Mollie_PaymentRequest $request ) {
		$data = $request->get_array();

		$response = $this->send_request( 'payments/', 'POST', $data );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 201 != $response_code ) { // WPCS: loose comparison ok.
			$body = wp_remote_retrieve_body( $response );

			$mollie_result = json_decode( $body );

			$this->error = new WP_Error( 'mollie_error', $mollie_result->error->message, $mollie_result->error );

			return null;
		}

		// OK
		$body = wp_remote_retrieve_body( $response );

		// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
		$result = json_decode( $body );

		return $result;
	}

	public function get_payment( $payment_id ) {
		$result = null;

		$response = $this->send_request( 'payments/' . $payment_id, 'GET' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 == $response_code ) { // WPCS: loose comparison ok.
			$body = wp_remote_retrieve_body( $response );

			// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
			$result = json_decode( $body );
		}

		return $result;
	}

	//////////////////////////////////////////////////

	/**
	 * Get issuers
	 *
	 * @return array
	 */
	public function get_issuers() {
		$issuers = false;

		$response = $this->send_request( 'issuers/', 'GET' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 == $response_code ) { // WPCS: loose comparison ok.
			$body = wp_remote_retrieve_body( $response );

			// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
			$result = json_decode( $body );

			if ( null !== $result ) {
				$issuers = array();

				foreach ( $result->data as $issuer ) {
					if ( Pronamic_WP_Pay_Mollie_Methods::IDEAL === $issuer->method ) {
						$id   = Pronamic_WP_Pay_XML_Security::filter( $issuer->id );
						$name = Pronamic_WP_Pay_XML_Security::filter( $issuer->name );

						$issuers[ $id ] = $name;
					}
				}
			}
		}

		return $issuers;
	}

	//////////////////////////////////////////////////

	/**
	 * Get payment methods
	 *
	 * @return array
	 */
	public function get_payment_methods() {
		$payment_methods = false;

		$response = $this->send_request( 'methods/', 'GET' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 == $response_code ) { // WPCS: loose comparison ok.
			$body = wp_remote_retrieve_body( $response );

			// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
			$result = json_decode( $body );

			if ( null !== $result ) {
				$payment_methods = array();

				foreach ( $result->data as $payment_method ) {
					$id   = Pronamic_WP_Pay_XML_Security::filter( $payment_method->id );
					$name = Pronamic_WP_Pay_XML_Security::filter( $payment_method->description );

					$payment_methods[ $id ] = $name;
				}
			}
		}

		return $payment_methods;
	}

	//////////////////////////////////////////////////

	/**
	 * Get customer.
	 *
	 * @param Pronamic_WP_Pay_PaymentData $data
	 *
	 * @return array
	 */
	public function get_customer_id( Pronamic_WP_Pay_PaymentData $data ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user_id = get_current_user_id();

		$customer_id = get_user_meta( $user_id, '_pronamic_pay_mollie_customer_id', true );

		if ( $customer_id ) {
			return $customer_id;
		}

		$response = $this->send_request( 'customers/', 'POST', array(
			'name'  => $data->get_customer_name(),
			'email' => $data->get_email(),
		) );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 201 != $response_code ) { // WPCS: loose comparison ok.
			$body = wp_remote_retrieve_body( $response );

			$mollie_result = json_decode( $body );

			$this->error = new WP_Error( 'mollie_error', $mollie_result->error->message, $mollie_result->error );

			return false;
		}

		// OK
		$body = wp_remote_retrieve_body( $response );

		// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
		$result = json_decode( $body );

		if ( ! is_object( $result ) ) {
			return false;
		}

		$customer_id = $result->id;

		update_user_meta( $user_id, '_pronamic_pay_mollie_customer_id', $customer_id );

		return $customer_id;
	}
}
