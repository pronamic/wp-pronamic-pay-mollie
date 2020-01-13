<?php
/**
 * Mollie client.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\XML\Security;

/**
 * Title: Mollie
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
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
	 * Send request with the specified action and parameters
	 *
	 * @param string $end_point              Requested endpoint.
	 * @param string $method                 HTTP method to use.
	 * @param array  $data                   Request data.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 * @throws \Exception Throws exception when error occurs.
	 */
	private function send_request( $end_point, $method = 'GET', array $data = array() ) {
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

		if ( $response instanceof \WP_Error ) {
			throw new \Exception( $response->get_error_message() );
		}

		// Body.
		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );

		// JSON error.
		$json_error = \json_last_error();

		if ( \JSON_ERROR_NONE !== $json_error ) {
			throw new \Exception(
				\sprintf( 'JSON: %s', \json_last_error_msg() ),
				$json_error
			);
		}

		// Object.
		if ( ! \is_object( $data ) ) {
			$code = \wp_remote_retrieve_response_code( $response );

			throw new \Exception(
				\sprintf( 'Could not JSON decode Mollie response to an object (HTTP Status Code: %s).', $code ),
				\intval( $code )
			);
		}

		// Mollie error from JSON response.
		if ( isset( $data->status, $data->title, $data->detail ) ) {
			throw new Error(
				$data->status,
				$data->title,
				$data->detail
			);
		}

		return $data;
	}

	/**
	 * Create payment.
	 *
	 * @param PaymentRequest $request Payment request.
	 * @return object
	 */
	public function create_payment( PaymentRequest $request ) {
		return $this->send_request( 'payments', 'POST', $request->get_array() );
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
	 * @return object
	 * @throws \InvalidArgumentException Throws exception on empty payment ID argument.
	 */
	public function get_payment( $payment_id ) {
		if ( empty( $payment_id ) ) {
			throw new \InvalidArgumentException( 'Mollie payment ID can not be empty string.' );
		}

		return $this->send_request( 'payments/' . $payment_id, 'GET' );
	}

	/**
	 * Get issuers
	 *
	 * @return array
	 */
	public function get_issuers() {
		$response = $this->send_request( 'methods/ideal?include=issuers', 'GET' );

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
	 * @return array
	 * @throws \Exception Throws exception for methods on failed request or invalid response.
	 */
	public function get_payment_methods( $sequence_type = '' ) {
		$data = array();

		if ( '' !== $sequence_type ) {
			$data['sequenceType'] = $sequence_type;
		}

		$response = $this->send_request( 'methods', 'GET', $data );

		$payment_methods = array();

		if ( ! isset( $response->_embedded ) ) {
			throw new \Exception( 'No embedded data in Mollie response.' );
		}

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
	 * @param string|null $email Customer email address.
	 * @param string|null $name  Customer name.
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
			)
		);

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
	 * @return null|object
	 * @throws \InvalidArgumentException Throws exception on empty customer ID argument.
	 */
	public function get_customer( $customer_id ) {
		if ( empty( $customer_id ) ) {
			throw new \InvalidArgumentException( 'Mollie customer ID can not be empty string.' );
		}

		try {
			return $this->send_request( 'customers/' . $customer_id, 'GET' );
		} catch ( Error $error ) {
			if ( 404 === $error->get_status() ) {
				return null;
			}

			throw $error;
		}
	}

	/**
	 * Get mandates for customer.
	 *
	 * @param string $customer_id Mollie customer ID.
	 *
	 * @return object
	 * @throws \InvalidArgumentException Throws exception on empty customer ID argument.
	 */
	public function get_mandates( $customer_id ) {
		if ( '' === $customer_id ) {
			throw new \InvalidArgumentException( 'Mollie customer ID can not be empty string.' );
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
	 * @throws \Exception Throws exception for mandates on failed request or invalid response.
	 */
	public function has_valid_mandate( $customer_id, $payment_method = null ) {
		$mandates = $this->get_mandates( $customer_id );

		$mollie_method = Methods::transform( $payment_method );

		if ( ! isset( $mandates->_embedded ) ) {
			throw new \Exception( 'No embedded data in Mollie response.' );
		}

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
	 * @throws \Exception Throws exception for mandates on failed request or invalid response.
	 */
	public function get_first_valid_mandate_datetime( $customer_id, $payment_method = null ) {
		$mandates = $this->get_mandates( $customer_id );

		$mollie_method = Methods::transform( $payment_method );

		if ( ! isset( $mandates->_embedded ) ) {
			throw new \Exception( 'No embedded data in Mollie response.' );
		}

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
