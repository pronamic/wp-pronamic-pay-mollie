<?php

/**
 * Title: Mollie
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.7
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

	public function get_payments() {
		$result = null;

		$response = $this->send_request( 'payments/', 'GET' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 == $response_code ) { // WPCS: loose comparison ok.
			$body = wp_remote_retrieve_body( $response );

			// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
			$result = json_decode( $body );
		}

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
	 * @since 1.1.6
	 * @param Pronamic_WP_Pay_PaymentData $data
	 * @return array
	 */
	public function get_customer_id( $name = '' ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();

		// Get customer ID from user meta
		$meta_key = '_pronamic_pay_mollie_customer_id';

		if ( 'test' === $this->mode ) {
			$meta_key = '_pronamic_pay_mollie_customer_id_test';
		}

		$customer_id = get_user_meta( $user->ID, $meta_key, true );

		// Return customer ID if valid
		if ( $customer_id ) {
			$response = $this->send_request( 'customers/' . $customer_id, 'GET' );

			$response_code = wp_remote_retrieve_response_code( $response );

			switch ( $response_code ) {
				case 200 :
					return $customer_id;

					break;
				case 404:
					return false;

					break;
			}
		}

		// Create new customer
		if ( '' === $name ) {
			$name = trim( sprintf( '%s %s', $user->user_firstname, $user->user_lastname ) );
		}

		// Create new customer
		$response = $this->send_request( 'customers/', 'POST', array(
			'name'  => $name,
			'email' => $user->user_email,
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

		update_user_meta( $user->ID, $meta_key, $customer_id );

		return $customer_id;
	}

	/**
	 * Get mandates for customer.
	 *
	 * @param $customer_id
	 *
	 * @return array
	 */
	public function get_mandates( $customer_id ) {
		$mandates = false;

		if ( '' === $customer_id ) {
			return false;
		}

		$response = $this->send_request( 'customers/' . $customer_id . '/mandates?count=250', 'GET' );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 == $response_code ) { // WPCS: loose comparison ok.
			$body = wp_remote_retrieve_body( $response );

			// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
			$result = json_decode( $body );

			if ( null !== $result ) {
				$mandates = $result;

				// DELETE all mandates
				if ( 0 ) {
					foreach ( $mandates->data as $mandate ) {
						$this->send_request( 'customers/' . $customer_id . '/mandates/' . $mandate->id, 'DELETE' );
					}
				}
			}
		} else {
			$body = wp_remote_retrieve_body( $response );

			$mollie_result = json_decode( $body );

			$this->error = new WP_Error( 'mollie_error', $mollie_result->error->message, $mollie_result->error );
		}

		return $mandates;
	}

	/**
	 * Is there a valid mandate for customer?
	 *
	 * @param $customer_id
	 *
	 * @return boolean
	 */
	public function has_valid_mandate( $customer_id ) {
		$mandates = $this->get_mandates( $customer_id );

		if ( $mandates ) {
			foreach ( $mandates->data as $mandate ) {
				if ( 'valid' === $mandate->status ) {
					return true;
				}
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
	public function get_first_valid_mandate_datetime( $customer_id ) {
		$mandates = $this->get_mandates( $customer_id );

		if ( $mandates ) {
			$valid_mandates = array();

			foreach ( $mandates->data as $mandate ) {
				if ( 'valid' === $mandate->status ) {
					$valid_mandates[ $mandate->createdDatetime ] = $mandate;
				}
			}

			ksort( $valid_mandates );

			$mandate = array_shift( $valid_mandates );

			return sprintf(
				__( '%1$s at %2$s', 'pronamic_ideal' ),
				get_date_from_gmt( $mandate->createdDatetime, get_option( 'date_format' ) ),
				get_date_from_gmt( $mandate->createdDatetime, get_option( 'time_format' ) )
			);
		}

		return null;
	}

	//////////////////////////////////////////////////

	/***
	 * Create subscription.
	 *
	 * @param $customer_id
	 * @param $amount
	 * @param $times
	 * @param $interval
	 * @param $description
	 * @param $webhook_url
	 *
	 * @return bool|array
	 *
	 * @see https://www.mollie.com/nl/docs/reference/subscriptions/create
	 *
	 * @since unreleased
	 */
	public function create_subscription( $customer_id, $amount, $times, $interval, $description, $webhook_url ) {
		if ( null === $customer_id ) {
			return false;
		}

		$request = array(
			'amount'      => $amount,
			'times'       => $times,
			'interval'    => $interval,
			'description' => $description,
			'method'      => null,
			'webhookUrl'  => $webhook_url,
		);

		$response = $this->send_request( 'customers/' . $customer_id . '/subscriptions', 'POST', $request );

		$response_code = wp_remote_retrieve_response_code( $response );

		$body = wp_remote_retrieve_body( $response );

		if ( 201 != $response_code ) { // WPCS: loose comparison ok.
			$mollie_result = json_decode( $body );

			$this->error = new WP_Error( 'mollie_error', $mollie_result->error->message, $mollie_result->error );

			return false;
		}

		// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
		$result = json_decode( $body );

		if ( null !== $result ) {
			return $result;
		}

		return false;
	}

	/***
	 * Cancel subscription.
	 *
	 * @param $customer_id
	 * @param $subscription_id
	 *
	 * @return bool|array
	 *
	 * @see https://www.mollie.com/nl/docs/reference/subscriptions/delete
	 *
	 * @since unreleased
	 */
	public function cancel_subscription( $customer_id, $subscription_id ) {
		if ( null === $subscription_id ) {
			return false;
		}

		$response = $this->send_request( 'customers/' . $customer_id . '/subscriptions/' . $subscription_id, 'DELETE' );

		$response_code = wp_remote_retrieve_response_code( $response );

		$body = wp_remote_retrieve_body( $response );

		if ( 200 != $response_code ) { // WPCS: loose comparison ok.
			$mollie_result = json_decode( $body );

			$this->error = new WP_Error( 'mollie_error', $mollie_result->error->message, $mollie_result->error );

			return false;
		}

		// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
		$result = json_decode( $body );

		if ( null !== $result ) {
			return $result;
		}

		return false;
	}

	/***
	 * Get subscriptions.
	 *
	 * @param $customer_id
	 *
	 * @return bool|array
	 *
	 * @see https://www.mollie.com/nl/docs/reference/subscriptions/list
	 *
	 * @since unreleased
	 */
	public function get_subscriptions( $customer_id ) {
		if ( null === $customer_id ) {
			return false;
		}

		$response = $this->send_request( 'customers/' . $customer_id . '/subscriptions?count=250', 'GET' );

		// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
		$result = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) { // WPCS: loose comparison ok.
			$this->error = new WP_Error( 'mollie_error', $result->error->message, $result->error );

			return false;
		}

		if ( null !== $result ) {
			// DELETE all subscriptions
			if ( 0 ) {
				foreach ( $result->data as $subscription ) {
					$resp = $this->send_request( 'customers/' . $customer_id . '/subscriptions/' . $subscription->id, 'DELETE' );
				}
			}

			return $result;
		}

		return false;
	}

	/***
	 * Get subscription.
	 *
	 * @param $customer_id
	 * @param $subscription_id
	 *
	 * @return array|bool
	 * @see https://www.mollie.com/nl/docs/reference/subscriptions/get
	 *
	 * @since unreleased
	 */
	public function get_subscription( $customer_id, $subscription_id ) {
		if ( null === $customer_id ) {
			return false;
		}

		$response = $this->send_request( 'customers/' . $customer_id . '/subscriptions/' . $subscription_id, 'GET' );

		// NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
		$result = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) { // WPCS: loose comparison ok.
			$this->error = new WP_Error( 'mollie_error', $result->error->message, $result->error );

			return false;
		}

		if ( null !== $result ) {
			return $result;
		}

		return false;
	}
}
