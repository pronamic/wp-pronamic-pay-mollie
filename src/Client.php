<?php
/**
 * Mollie client.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Http\Facades\Http;
use Pronamic\WordPress\Pay\Banks\BankAccountDetails;
use Pronamic\WordPress\Pay\Core\XML\Security;

/**
 * Title: Mollie
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.4
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
	 * Constructs and initializes an Mollie client object
	 *
	 * @param string $api_key Mollie API key.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Send request with the specified action and parameters
	 *
	 * @param string                            $url    URL.
	 * @param string                            $method HTTP method to use.
	 * @param array<string, string|object|null> $data   Request data.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 * @throws \Exception Throws exception when error occurs.
	 */
	public function send_request( $url, $method = 'GET', array $data = array() ) {
		// Request.
		$response = Http::request(
			$url,
			array(
				'method'  => $method,
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
				),
				'body'    => $data,
			)
		);

		$data = $response->json();

		// Object.
		if ( ! \is_object( $data ) ) {
			$code = $response->status();

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
	 * Get URL.
	 *
	 * @param string $endpoint URL endpoint.
	 * @return string
	 */
	public function get_url( $endpoint ) {
		$url = self::API_URL . $endpoint;

		return $url;
	}

	/**
	 * Send request to endpoint.
	 *
	 * @param string                            $endpoint Endpoint.
	 * @param string                            $method   HTTP method to use.
	 * @param array<string, string|object|null> $data     Request data.
	 * @return object
	 */
	public function send_request_to_endpoint( $endpoint, $method = 'GET', array $data = array() ) {
		return $this->send_request( $this->get_url( $endpoint ), $method, $data );
	}

	/**
	 * Get profile.
	 *
	 * @param string $profile Mollie profile ID.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	public function get_profile( $profile ) {
		return $this->send_request_to_endpoint( 'profiles/' . $profile, 'GET' );
	}

	/**
	 * Get current profile.
	 *
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	public function get_current_profile() {
		return $this->get_profile( 'me' );
	}

	/**
	 * Create payment.
	 *
	 * @param PaymentRequest $request Payment request.
	 * @return Payment
	 */
	public function create_payment( PaymentRequest $request ) {
		$object = $this->send_request_to_endpoint( 'payments', 'POST', $request->get_array() );

		$payment = Payment::from_json( $object );

		return $payment;
	}

	/**
	 * Get payments.
	 *
	 * @return bool|object
	 */
	public function get_payments() {
		return $this->send_request_to_endpoint( 'payments', 'GET' );
	}

	/**
	 * Get payment.
	 *
	 * @param string               $payment_id Mollie payment ID.
	 * @param array<string, mixed> $parameters Parameters.
	 * @return Payment
	 * @throws \InvalidArgumentException Throws exception on empty payment ID argument.
	 */
	public function get_payment( $payment_id, $parameters = array() ) {
		if ( empty( $payment_id ) ) {
			throw new \InvalidArgumentException( 'Mollie payment ID can not be empty string.' );
		}

		$object = $this->send_request_to_endpoint( 'payments/' . $payment_id, 'GET', $parameters );

		$payment = Payment::from_json( $object );

		return $payment;
	}

	/**
	 * Get issuers
	 *
	 * @return array<string>
	 */
	public function get_issuers() {
		$response = $this->send_request_to_endpoint( 'methods/ideal?include=issuers', 'GET' );

		$issuers = array();

		if ( isset( $response->issuers ) ) {
			foreach ( $response->issuers as $issuer ) {
				$id   = Security::filter( $issuer->id );
				$name = Security::filter( $issuer->name );

				if ( null === $id || null === $name ) {
					continue;
				}

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
	 * @return array<string>
	 * @throws \Exception Throws exception for methods on failed request or invalid response.
	 */
	public function get_payment_methods( $sequence_type = '' ) {
		$data = array(
			'includeWallets' => Methods::APPLE_PAY,
		);

		if ( '' !== $sequence_type ) {
			$data['sequenceType'] = $sequence_type;
		}

		$response = $this->send_request_to_endpoint( 'methods', 'GET', $data );

		$payment_methods = array();

		if ( ! isset( $response->_embedded ) ) {
			throw new \Exception( 'No embedded data in Mollie response.' );
		}

		if ( isset( $response->_embedded->methods ) ) {
			foreach ( $response->_embedded->methods as $payment_method ) {
				$id   = Security::filter( $payment_method->id );
				$name = Security::filter( $payment_method->description );

				if ( null === $id || null === $name ) {
					continue;
				}

				$payment_methods[ $id ] = $name;
			}
		}

		return $payment_methods;
	}

	/**
	 * Create customer.
	 *
	 * @param Customer $customer Customer.
	 * @return Customer
	 * @throws Error Throws Error when Mollie error occurs.
	 * @since 1.1.6
	 */
	public function create_customer( Customer $customer ) {
		$response = $this->send_request_to_endpoint(
			'customers',
			'POST',
			$customer->get_array()
		);

		if ( \property_exists( $response, 'id' ) ) {
			$customer->set_id( $response->id );
		}

		return $customer;
	}

	/**
	 * Get customer.
	 *
	 * @param string $customer_id Mollie customer ID.
	 *
	 * @return null|object
	 * @throws \InvalidArgumentException Throws exception on empty customer ID argument.
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	public function get_customer( $customer_id ) {
		if ( empty( $customer_id ) ) {
			throw new \InvalidArgumentException( 'Mollie customer ID can not be empty string.' );
		}

		try {
			return $this->send_request_to_endpoint( 'customers/' . $customer_id, 'GET' );
		} catch ( Error $error ) {
			if ( 404 === $error->get_status() ) {
				return null;
			}

			throw $error;
		}
	}

	/**
	 * Create mandate.
	 *
	 * @param string             $customer_id           Customer ID.
	 * @param BankAccountDetails $consumer_bank_details Consumer bank details.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 * @since unreleased
	 */
	public function create_mandate( $customer_id, BankAccountDetails $consumer_bank_details ) {
		$response = $this->send_request_to_endpoint(
			'customers/' . $customer_id . '/mandates',
			'POST',
			array(
				'method'          => Methods::DIRECT_DEBIT,
				'consumerName'    => $consumer_bank_details->get_name(),
				'consumerAccount' => $consumer_bank_details->get_iban(),
			)
		);

		return $response;
	}

	/**
	 * Get mandate.
	 *
	 * @param string $mandate_id Mollie mandate ID.
	 * @param string $customer_id Mollie customer ID.
	 * @return object
	 * @throws \InvalidArgumentException Throws exception on empty mandate ID argument.
	 */
	public function get_mandate( $mandate_id, $customer_id ) {
		if ( '' === $mandate_id ) {
			throw new \InvalidArgumentException( 'Mollie mandate ID can not be empty string.' );
		}

		if ( '' === $customer_id ) {
			throw new \InvalidArgumentException( 'Mollie customer ID can not be empty string.' );
		}

		return $this->send_request_to_endpoint( 'customers/' . $customer_id . '/mandates/' . $mandate_id, 'GET' );
	}

	/**
	 * Get mandates for customer.
	 *
	 * @param string $customer_id Mollie customer ID.
	 * @return object
	 * @throws \InvalidArgumentException Throws exception on empty customer ID argument.
	 */
	public function get_mandates( $customer_id ) {
		if ( '' === $customer_id ) {
			throw new \InvalidArgumentException( 'Mollie customer ID can not be empty string.' );
		}

		return $this->send_request_to_endpoint( 'customers/' . $customer_id . '/mandates?limit=250', 'GET' );
	}

	/**
	 * Is there a valid mandate for customer?
	 *
	 * @param string      $customer_id    Mollie customer ID.
	 * @param string|null $payment_method Payment method to find mandates for.
	 * @param string|null $search         Search.
	 *
	 * @return string|bool
	 * @throws \Exception Throws exception for mandates on failed request or invalid response.
	 */
	public function has_valid_mandate( $customer_id, $payment_method = null, $search = null ) {
		$mandates = $this->get_mandates( $customer_id );

		$mollie_method = Methods::transform( $payment_method );

		if ( ! isset( $mandates->_embedded ) ) {
			throw new \Exception( 'No embedded data in Mollie response.' );
		}

		foreach ( $mandates->_embedded->mandates as $mandate ) {
			if ( null !== $mollie_method && $mollie_method !== $mandate->method ) {
				continue;
			}

			// Search consumer account or card number.
			if ( null !== $search ) {
				switch ( $mollie_method ) {
					case Methods::DIRECT_DEBIT:
					case Methods::PAYPAL:
						if ( $search !== $mandate->details->consumerAccount ) {
							continue 2;
						}

						break;
					case Methods::CREDITCARD:
						if ( $search !== $mandate->details->cardNumber ) {
							continue 2;
						}

						break;
				}
			}

			if ( 'valid' === $mandate->status ) {
				return $mandate->id;
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

		foreach ( $mandates->_embedded->mandates as $mandate ) {
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

	/**
	 * Create refund.
	 *
	 * @param string        $payment_id     Mollie payment ID.
	 * @param RefundRequest $refund_request Refund request.
	 * @return Refund
	 */
	public function create_refund( $payment_id, RefundRequest $refund_request ) {
		$response = $this->send_request_to_endpoint( 'payments/' . $payment_id . '/refunds', 'POST', $refund_request->get_array() );

		return Refund::from_json( $response );
	}

	/**
	 * Get payment chargebacks.
	 *
	 * @param string               $payment_id Mollie payment ID.
	 * @param array<string, mixed> $parameters Parameters.
	 * @return array<Chargeback>
	 */
	public function get_payment_chargebacks( $payment_id, $parameters ) {
		$object = $this->send_request_to_endpoint( 'payments/' . $payment_id . '/chargebacks', 'GET', $parameters );

		$chargebacks = array();

		if ( \property_exists( $object, '_embedded' ) && \property_exists( $object->_embedded, 'chargebacks' ) ) {
			foreach ( $object->_embedded->chargebacks as $chargeback_object ) {
				$chargebacks[] = Chargeback::from_json( $chargeback_object );
			}
		}

		return $chargebacks;
	}
}
