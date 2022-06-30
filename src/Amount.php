<?php
/**
 * Amount
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use InvalidArgumentException;
use JsonSerializable;
use stdClass;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

/**
 * Amount class
 */
class Amount implements JsonSerializable {
	/**
	 * Currency.
	 *
	 * @var string
	 */
	private $currency;

	/**
	 * Amount value.
	 *
	 * @var string
	 */
	private $value;

	/**
	 * Construct an amount.
	 *
	 * @param string $currency Currency code (ISO 4217).
	 * @param string $value    Amount formatted with correct number of decimals for currency.
	 */
	public function __construct( $currency, $value ) {
		$this->currency = $currency;
		$this->value    = $value;
	}

	/**
	 * Get currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Get amount.
	 *
	 * @return string
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Create amount from object.
	 *
	 * @param stdClass $object Object.
	 * @return Amount
	 */
	public static function from_object( stdClass $object ) {
		$object_access = new ObjectAccess( $object );

		return new self(
			$object_access->get_property( 'currency' ),
			$object_access->get_property( 'value' )
		);
	}

	/**
	 * Create amount from JSON string.
	 *
	 * @param object $json JSON object.
	 *
	 * @return Amount
	 *
	 * @throws InvalidArgumentException Throws invalid argument exception when input JSON is not an object.
	 * @throws \JsonSchema\Exception\ValidationException Throws JSON schema validation exception when JSON is invalid.
	 */
	public static function from_json( $json ) {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		$validator = new Validator();

		$validator->validate(
			$json,
			(object) [
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/amount.json' ),
			],
			Constraint::CHECK_MODE_EXCEPTIONS
		);

		return self::from_object( $json );
	}

	/**
	 * JSON serialize.
	 *
	 * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed
	 */
	public function jsonSerialize() {
		return (object) [
			'currency' => $this->currency,
			'value'    => $this->value,
		];
	}
}
