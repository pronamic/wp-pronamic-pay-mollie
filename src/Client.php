<?php

/**
 * Title: Mollie
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.13
 * @since 1.0.0
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
	 * Mode
	 *
	 * @since 1.1.9
	 * @var string
	 */
	private $mode;

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
	 * Set mode
	 *
	 * @since 1.1.9
	 * @param string $mode
	 */
	public function set_mode( $mode ) {
		$this->mode = $mode;
	}

	//////////////////////////////////////////////////

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
	private function send_request( $end_point, $method = 'GET', array $data = array(), $expected_response_code = 200 ) {
		// Request
		$url = self::API_URL . $end_point;

		$response = wp_remote_request( $url, array(
			'method'    => $method,
			'headers'   => array(
				'Authorization' => 'Bearer ' . $this->api_key,
			),
			'body'      => $data,
		) );

		// Response code
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( $expected_response_code != $response_code ) { // WPCS: loose comparison ok.
			$this->error = new WP_Error( 'mollie_error', 'Unexpected response code.' );
		}

		// Body
		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );

		if ( ! is_object( $data ) ) {
			$this->error = new WP_Error( 'mollie_error', 'Could not parse response.' );

			return false;
		}

		// Mollie error
		if ( isset( $data->error, $data->error->message ) ) {
			$this->error = new WP_Error( 'mollie_error', $data->error->message, $data->error );

			return false;
		}

		return $data;
	}

	/////////////////////////////////////////////////

	public function create_payment( Pronamic_WP_Pay_Gateways_Mollie_PaymentRequest $request ) {
		return $this->send_request( 'payments/', 'POST', $request->get_array(), 201 );
	}

	public function get_payments() {
		return $this->send_request( 'payments/', 'GET' );
	}

	public function get_payment( $payment_id ) {
		if ( '' === $payment_id ) {
			return false;
		}

		return $this->send_request( 'payments/' . $payment_id, 'GET' );
	}

	//////////////////////////////////////////////////

	/**
	 * Get issuers
	 *
	 * @return array
	 */
	public function get_issuers() {
		$response = $this->send_request( 'issuers/', 'GET' );

		if ( false === $response ) {
			return false;
		}

		$issuers = array();

		if ( isset( $response->data ) ) {
			foreach ( $response->data as $issuer ) {
				if ( Pronamic_WP_Pay_Mollie_Methods::IDEAL === $issuer->method ) {
					$id   = Pronamic_WP_Pay_XML_Security::filter( $issuer->id );
					$name = Pronamic_WP_Pay_XML_Security::filter( $issuer->name );

					$issuers[ $id ] = $name;
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
		$response = $this->send_request( 'methods/', 'GET' );

		if ( false === $response ) {
			return false;
		}

		$payment_methods = array();

		if ( isset( $response->data ) ) {
			foreach ( $response->data as $payment_method ) {
				$id   = Pronamic_WP_Pay_XML_Security::filter( $payment_method->id );
				$name = Pronamic_WP_Pay_XML_Security::filter( $payment_method->description );

				$payment_methods[ $id ] = $name;
			}
		}

		return $payment_methods;
	}

	//////////////////////////////////////////////////

	/**
	 * Create customer.
	 *
	 * @since 1.1.6
	 * @param Pronamic_WP_Pay_PaymentData $data
	 * @return array
	 */
	public function create_customer( $email, $name ) {
		if ( empty( $email ) || empty( $name ) ) {
			return false;
		}

		$response = $this->send_request( 'customers/', 'POST', array(
			'name'  => $name,
			'email' => $email,
		), 201 );

		if ( false === $response ) {
			return false;
		}

		if ( ! isset( $response->id ) ) {
			return false;
		}

		return $response->id;
	}

	/**
	 * Get customer.
	 *
	 * @param $customer_id
	 *
	 * @since unreleased
	 *
	 * @return array
	 */
	public function get_customer( $customer_id ) {
		if ( empty( $customer_id ) ) {
			return false;
		}

		$response = $this->send_request( 'customers/' . $customer_id, 'GET', array(), 200 );

		if ( false === $response ) {
			return false;
		}

		if ( is_wp_error( $this->error ) ) {
			return false;
		}

		return $response;
	}

	/**
	 * Get mandates for customer.
	 *
	 * @param $customer_id
	 *
	 * @return array
	 */
	public function get_mandates( $customer_id ) {
		if ( '' === $customer_id ) {
			return false;
		}

		return $this->send_request( 'customers/' . $customer_id . '/mandates?count=250', 'GET' );
	}

	/**
	 * Is there a valid mandate for customer?
	 *
	 * @param $customer_id
	 *
	 * @return boolean
	 */
	public function has_valid_mandate( $customer_id, $payment_method = null ) {
		$mandates = $this->get_mandates( $customer_id );

		if ( ! $mandates ) {
			return false;
		}

		$mollie_method = Pronamic_WP_Pay_Mollie_Methods::transform( $payment_method );

		foreach ( $mandates->data as $mandate ) {
			if ( $mollie_method !== $mandate->method ) {
				continue;
			}

			if ( 'valid' === $mandate->status ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get formatted date and time of first valid mandate.
	 *
	 * @param $customer_id
	 *
	 * @return string
	 */
	public function get_first_valid_mandate_datetime( $customer_id, $payment_method = null ) {
		$mandates = $this->get_mandates( $customer_id );

		if ( ! $mandates ) {
			return null;
		}

		$mollie_method = Pronamic_WP_Pay_Mollie_Methods::transform( $payment_method );

		foreach ( $mandates->data as $mandate ) {
			if ( $mollie_method !== $mandate->method ) {
				continue;
			}

			if ( 'valid' !== $mandate->status ) {
				continue;
			}

			if ( ! isset( $valid_mandates ) ) {
				$valid_mandates = array();
			}

			$valid_mandates[ $mandate->createdDatetime ] = $mandate;
		}

		if ( isset( $valid_mandates ) ) {
			ksort( $valid_mandates );

			$mandate = array_shift( $valid_mandates );

			$created = new DateTime( $mandate->createdDatetime );

			return sprintf(
				__( '%1$s at %2$s', 'pronamic_ideal' ),
				date_i18n( get_option( 'date_format' ), $created->getTimestamp() ),
				date_i18n( get_option( 'time_format' ), $created->getTimestamp() )
			);
		}

		return null;
	}
}
