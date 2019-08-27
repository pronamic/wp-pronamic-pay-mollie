<?php
/**
 * Amount
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use InvalidArgumentException;
use stdClass;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;

/**
 * Amount
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.1.0
 */
class Amount {
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
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		return (object) array(
			'currency' => $this->get_currency(),
			'value'    => $this->get_value(),
		);
	}

	/**
	 * Create amount from object.
	 *
	 * @param stdClass $object Object.
	 *
	 * @return Amount
	 * @throws InvalidArgumentException Throws invalid argument exception when object does not contains the required properties.
	 */
	public static function from_object( stdClass $object ) {
		if ( ! isset( $object->currency ) ) {
			throw new InvalidArgumentException( 'Object must contain `currency` property.' );
		}

		if ( ! isset( $object->value ) ) {
			throw new InvalidArgumentException( 'Object must contain `value` property.' );
		}

		return new self(
			$object->currency,
			$object->value
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
			(object) array(
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/amount.json' ),
			),
			Constraint::CHECK_MODE_EXCEPTIONS
		);

		return self::from_object( $json );
	}

	/**
	 * Create string representation of amount.
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf( '%1$s %2$s', $this->get_currency(), $this->get_value() );
	}
}
