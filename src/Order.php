<?php
/**
 * Order
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Order class
 */
class Order extends BaseResource {
	/**
	 * Payments.
	 *
	 * @var Payment[]|null
	 */
	private ?array $payments;

	/**
	 * Status.
	 *
	 * @var string
	 */
	private string $status;

	/**
	 * Get embedded payments.
	 *
	 * @return Payment[]|null
	 */
	public function get_payments() : ?array {
		return $this->payments;
	}

	/**
	 * Set embedded payments.
	 *
	 * @param Payment[]|null $payments Payments.
	 */
	public function set_payments( ?array $payments ) : void {
		$this->payments = $payments;
	}

	/**
	 * Get status.
	 *
	 * @return string
	 */
	public function get_status() : string {
		return $this->status;
	}

	/**
	 * Create order from JSON.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/get-order
	 * @param object $json JSON object.
	 * @return Order
	 * @throws \JsonSchema\Exception\ValidationException Throws JSON schema validation exception when JSON is invalid.
	 */
	public static function from_json( $json ) {
		$validator = new \JsonSchema\Validator();

		$validator->validate(
			$json,
			(object) [
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/order.json' ),
			],
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		$object_access = new ObjectAccess( $json );

		$order = new Order( $object_access->get_property( 'id' ) );

		$order->status = $object_access->get_property( 'status' );

		if ( property_exists( $json, '_embedded' ) ) {
			if ( property_exists( $json->_embedded, 'payments' ) ) {
				$payments = array_map(
					/**
					 * Get JSON for payments.
					 *
					 * @param object $payment Payment.
					 * @return Payment
					 */
					function( object $payment ) {
						return Payment::from_json( $payment );
					},
					$json->_embedded->payments
				);

				$order->set_payments( $payments );
			}
		}

		return $order;
	}
}
