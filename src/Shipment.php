<?php
/**
 * Shipment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Shipment class
 */
class Shipment extends BaseResource {
	/**
	 * Create shipment from JSON.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/get-shipment
	 * @param object $json JSON object.
	 * @return Shipment
	 * @throws \JsonSchema\Exception\ValidationException Throws JSON schema validation exception when JSON is invalid.
	 */
	public static function from_json( $json ) {
		$validator = new \JsonSchema\Validator();

		$validator->validate(
			$json,
			(object) [
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/shipment.json' ),
			],
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		$object_access = new ObjectAccess( $json );

		$shipment = new Shipment( $object_access->get_property( 'id' ) );

		return $shipment;
	}
}
