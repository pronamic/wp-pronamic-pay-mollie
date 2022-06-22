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
 * Client class
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
	 * @param string $url    URL.
	 * @param string $method HTTP method to use.
	 * @param mixed  $data   Request data.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 * @throws \Exception Throws exception when error occurs.
	 */
	public function send_request( $url, $method = 'GET', $data = null ) {
		// Request.
		$args = [
			'method'  => $method,
			'headers' => [
				'Authorization' => 'Bearer ' . $this->api_key,
			],
		];

		if ( null !== $data ) {
			$args['headers']['Content-Type'] = 'application/json';

			$args['body'] = \wp_json_encode( $data );
		}

		$response = Http::request( $url, $args );

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
	 * Post data to URL.
	 *
	 * @param string $url  URL.
	 * @param mixed  $data Data.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	private function post( string $url, $data = null ) {
		return $this->send_request( $url, 'POST', $data );
	}

	/**
	 * Get data from URL.
	 *
	 * @param string $url URL.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	private function get( string $url ) {
		return $this->send_request( $url, 'GET' );
	}

	/**
	 * Get URL.
	 *
	 * @param string   $endpoint   URL endpoint.
	 * @param string[] $parts      Parts.
	 * @param string[] $parameters Parameters.
	 * @return string
	 */
	private function get_url( $endpoint, array $parts = [], array $parameters = [] ) {
		$url = self::API_URL . \strtr( $endpoint, $parts );

		if ( \count( $parameters ) > 0 ) {
			$url .= '?' . \http_build_query( $parameters, '', '&' );
		}

		return $url;
	}

	/**
	 * Get profile.
	 *
	 * @param string $profile_id Mollie profile ID.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	public function get_profile( $profile_id ) {
		return $this->get(
			$this->get_url(
				'profiles/*id*',
				[
					'*id*' => $profile_id,
				]
			)
		);
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
	 * Create order.
	 *
	 * @param OrderRequest $request Order request.
	 * @return Order
	 */
	public function create_order( OrderRequest $request ) {
		$object = $this->post(
			$this->get_url(
				'orders',
				[],
				[
					'embed' => 'payments',
				]
			),
			$request
		);

		$order = Order::from_json( $object );

		return $order;
	}

	/**
	 * Create payment.
	 *
	 * @param PaymentRequest $request Payment request.
	 * @return Payment
	 */
	public function create_payment( PaymentRequest $request ) {
		$object = $this->post(
			$this->get_url( 'payments' ),
			$request
		);

		$payment = Payment::from_json( $object );

		return $payment;
	}

	/**
	 * Create shipment for an order.
	 *
	 * @param string $order_id Order ID.
	 * @return Shipment
	 */
	public function create_shipment( $order_id ) {
		$response = $this->post(
			$this->get_url(
				'orders/*orderId*/shipments',
				[
					'*orderId*' => $order_id,
				]
			)
		);

		$shipment = Shipment::from_json( $response );

		return $shipment;
	}

	/**
	 * Get order.
	 *
	 * @param string $order_id Order ID.
	 * @return Order
	 */
	public function get_order( string $order_id ) : Order {
		$response = $this->get(
			$this->get_url(
				'orders/*id*',
				[
					'*id*' => $order_id,
				],
				[
					'embed' => 'payments',
				]
			)
		);

		$order = Order::from_json( $response );

		return $order;
	}

	/**
	 * Get payments.
	 *
	 * @return bool|object
	 */
	public function get_payments() {
		return $this->get( $this->get_url( 'payments' ) );
	}

	/**
	 * Get payment.
	 *
	 * @param string               $payment_id Mollie payment ID.
	 * @param array<string, mixed> $parameters Parameters.
	 * @return Payment
	 * @throws \InvalidArgumentException Throws exception on empty payment ID argument.
	 */
	public function get_payment( $payment_id, $parameters = [] ) {
		if ( empty( $payment_id ) ) {
			throw new \InvalidArgumentException( 'Mollie payment ID can not be empty string.' );
		}

		$object = $this->get(
			$this->get_url(
				'payments/*id*',
				[
					'*id*' => $payment_id,
				],
				$parameters
			)
		);

		$payment = Payment::from_json( $object );

		return $payment;
	}

	/**
	 * Get issuers
	 *
	 * @return array<string>
	 */
	public function get_issuers() {
		$response = $this->get(
			$this->get_url(
				'methods/ideal',
				[],
				[
					'include' => 'issuers',
				]
			)
		);

		$issuers = [];

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
	 * @param string $resource      Resource type to query, e.g. `payments`, `orders`.
	 * @return array<string>
	 * @throws \Exception Throws exception for methods on failed request or invalid response.
	 */
	public function get_payment_methods( $sequence_type = '', $resource = 'payments' ) {
		$data = [
			'includeWallets' => Methods::APPLE_PAY,
			'resource'       => $resource,
		];

		if ( '' !== $sequence_type ) {
			$data['sequenceType'] = $sequence_type;
		}

		$response = $this->get(
			$this->get_url(
				'methods',
				[],
				$data
			)
		);

		$payment_methods = [];

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
		$response = $this->post(
			$this->get_url( 'customers' ),
			$customer
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
			return $this->get(
				$this->get_url(
					'customers/*id*',
					[
						'*id*' => $customer_id,
					]
				)
			);
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
	 * @throws \Exception Throws exception when mandate creation failed.
	 */
	public function create_mandate( $customer_id, BankAccountDetails $consumer_bank_details ) {
		$response = $this->post(
			$this->get_url(
				'customers/*customerId*/mandates',
				[
					'*customerId*' => $customer_id,
				]
			),
			[
				'method'          => Methods::DIRECT_DEBIT,
				'consumerName'    => $consumer_bank_details->get_name(),
				'consumerAccount' => $consumer_bank_details->get_iban(),
			]
		);

		if ( ! \property_exists( $response, 'id' ) ) {
			throw new \Exception( 'Missing mandate ID.' );
		}

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

		return $this->get(
			$this->get_url(
				'customers/*customerId*/mandates/*id*',
				[
					'*customerId*' => $customer_id,
					'*id*'         => $mandate_id,
				]
			)
		);
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

		return $this->get(
			$this->get_url(
				'customers/*customerId*/mandates',
				[
					'*customerId*' => $customer_id,
				],
				[
					'limit' => '250',
				]
			)
		);
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
				$valid_mandates = [];
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
		$response = $this->post(
			$this->get_url(
				'payments/*id*/refunds',
				[
					'*id*' => $payment_id,
				]
			),
			$refund_request
		);

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
		$object = $this->get(
			$this->get_url(
				'payments/*paymentId*/chargebacks',
				[
					'*paymentId*' => $payment_id,
				],
				$parameters
			)
		);

		$chargebacks = [];

		if ( \property_exists( $object, '_embedded' ) && \property_exists( $object->_embedded, 'chargebacks' ) ) {
			foreach ( $object->_embedded->chargebacks as $chargeback_object ) {
				$chargebacks[] = Chargeback::from_json( $chargeback_object );
			}
		}

		return $chargebacks;
	}
}
