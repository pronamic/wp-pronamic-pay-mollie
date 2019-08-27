<?php
/**
 * Mollie client.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\XML\Security;
use WP_Error;

/**
 * Title: Mollie
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class Client {
	/**
	 * Mollie API endpoint URL
	 *
	 * @var string
	 */
	const API_URL = 'https://api.mollie.com/v2/';

	/**
	 * Mollie API Key ID
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Mode
	 *
	 * @since 1.1.9
	 * @var string
	 */
	private $mode;

	/**
	 * Error
	 *
	 * @var WP_Error
	 */
	private $error;

	/**
	 * Constructs and initializes an Mollie client object
	 *
	 * @param string $api_key Mollie API key.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Set mode
	 *
	 * @since 1.1.9
	 * @param string $mode Mode (test or live).
	 */
	public function set_mode( $mode ) {
		$this->mode = $mode;
	}

	/**
	 * Error
	 *
	 * @return WP_Error
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Send request with the specified action and parameters
	 *
	 * @param string $end_point              Requested endpoint.
	 * @param string $method                 HTTP method to use.
	 * @param array  $data                   Request data.
	 * @param int    $expected_response_code Expected response code.
	 *
	 * @return bool|object
	 */
	private function send_request( $end_point, $method = 'GET', array $data = array(), $expected_response_code = 200 ) {
		// Request.
		$url = self::API_URL . $end_point;

		$response = wp_remote_request(
			$url,
			array(
				'method'  => $method,
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
				),
				'body'    => $data,
			)
		);

		// Response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $expected_response_code != $response_code ) {
			$this->error = new WP_Error( 'mollie_error', 'Unexpected response code.' );
		}

		// Body.
		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );

		if ( ! is_object( $data ) ) {
			$this->error = new WP_Error( 'mollie_error', 'Could not parse response.' );

			return false;
		}

		// Mollie error.
		if ( isset( $data->status, $data->title, $data->detail ) ) {
			$this->error = new \WP_Error( 'mollie_error', $data->detail, $data );

			return false;
		}

		return $data;
	}

	/**
	 * Create payment.
	 *
	 * @param PaymentRequest $request Payment request.
	 *
	 * @return bool|object
	 */
	public function create_payment( PaymentRequest $request ) {
		return $this->send_request( 'payments', 'POST', $request->get_array(), 201 );
	}

	/**
	 * Get payments.
	 *
	 * @return bool|object
	 */
	public function get_payments() {
		return $this->send_request( 'payments', 'GET' );
	}

	/**
	 * Get payment.
	 *
	 * @param string $payment_id Payment ID.
	 *
	 * @return bool|object
	 */
	public function get_payment( $payment_id ) {
		if ( empty( $payment_id ) ) {
			return false;
		}

		return $this->send_request( 'payments/' . $payment_id, 'GET' );
	}

	/**
	 * Get issuers
	 *
	 * @return array|bool
	 */
	public function get_issuers() {
		$response = $this->send_request( 'methods/ideal?include=issuers', 'GET' );

		if ( false === $response ) {
			return false;
		}

		$issuers = array();

		if ( isset( $response->issuers ) ) {
			foreach ( $response->issuers as $issuer ) {
				$id   = Security::filter( $issuer->id );
				$name = Security::filter( $issuer->name );

				$issuers[ $id ] = $name;
			}
		}

		return $issuers;
	}

	/**
	 * Get payment methods
	 *
	 * @param string $sequence_type Sequence type.
	 *
	 * @return array|bool
	 */
	public function get_payment_methods( $sequence_type = '' ) {
		$data = array();

		if ( '' !== $sequence_type ) {
			$data['sequenceType'] = $sequence_type;
		}

		$response = $this->send_request( 'methods', 'GET', $data );

		if ( false === $response ) {
			return false;
		}

		$payment_methods = array();

		if ( isset( $response->_embedded->methods ) ) {
			foreach ( $response->_embedded->methods as $payment_method ) {
				$id   = Security::filter( $payment_method->id );
				$name = Security::filter( $payment_method->description );

				$payment_methods[ $id ] = $name;
			}
		}

		return $payment_methods;
	}

	/**
	 * Create customer.
	 *
	 * @since 1.1.6
	 *
	 * @param string $email Customer email address.
	 * @param string $name  Customer name.
	 *
	 * @return string|bool
	 */
	public function create_customer( $email, $name ) {
		if ( empty( $email ) ) {
			return false;
		}

		$response = $this->send_request(
			'customers',
			'POST',
			array(
				'name'  => $name,
				'email' => $email,
			),
			201
		);

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
	 * @param string $customer_id Mollie customer ID.
	 *
	 * @since unreleased
	 *
	 * @return object|bool
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
	 * @param string $customer_id Mollie customer ID.
	 *
	 * @return object|bool
	 */
	public function get_mandates( $customer_id ) {
		if ( '' === $customer_id ) {
			return false;
		}

		return $this->send_request( 'customers/' . $customer_id . '/mandates?limit=250', 'GET' );
	}

	/**
	 * Is there a valid mandate for customer?
	 *
	 * @param string      $customer_id    Mollie customer ID.
	 * @param string|null $payment_method Payment method to find mandates for.
	 *
	 * @return boolean
	 */
	public function has_valid_mandate( $customer_id, $payment_method = null ) {
		$mandates = $this->get_mandates( $customer_id );

		if ( ! $mandates ) {
			return false;
		}

		$mollie_method = Methods::transform( $payment_method );

		foreach ( $mandates->_embedded as $mandate ) {
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
	 * @param string $customer_id    Mollie customer ID.
	 * @param string $payment_method Payment method.
	 *
	 * @return null|DateTime
	 */
	public function get_first_valid_mandate_datetime( $customer_id, $payment_method = null ) {
		$mandates = $this->get_mandates( $customer_id );

		if ( ! $mandates ) {
			return null;
		}

		$mollie_method = Methods::transform( $payment_method );

		foreach ( $mandates->_embedded as $mandate ) {
			if ( $mollie_method !== $mandate->method ) {
				continue;
			}

			if ( 'valid' !== $mandate->status ) {
				continue;
			}

			if ( ! isset( $valid_mandates ) ) {
				$valid_mandates = array();
			}

			// @codingStandardsIgnoreStart
			$valid_mandates[ $mandate->createdAt ] = $mandate;
			// @codingStandardsIgnoreEnd
		}

		if ( isset( $valid_mandates ) ) {
			ksort( $valid_mandates );

			$mandate = array_shift( $valid_mandates );

			// @codingStandardsIgnoreStart
			$create_date = new DateTime( $mandate->createdAt );
			// @codingStandardsIgnoreEnd

			return $create_date;
		}

		return null;
	}
}
