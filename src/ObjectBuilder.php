<?php
/**
 * Object builder
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use JsonSerializable;

/**
 * Object builder class
 */
class ObjectBuilder implements JsonSerializable {
	/**
	 * Data.
	 *
	 * @var mixed[] Data.
	 */
	private $data = [];

	/**
	 * Set optional value.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 * @return void
	 */
	public function set_optional( string $key, $value ) {
		if ( null === $value ) {
			return;
		}

		$this->set_value( $key, $value );
	}

	/**
	 * Set required value.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 * @return void
	 */
	public function set_required( string $key, $value ) {
		$this->set_value( $key, $value );
	}

	/**
	 * Set value.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 * @return void
	 */
	private function set_value( string $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * JSON serialize.
	 *
	 * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed
	 */
	public function jsonSerialize() {
		return (object) $this->data;
	}
}
