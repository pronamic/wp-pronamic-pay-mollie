<?php
/**
 * Object access
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Object access class
 */
class ObjectAccess {
	/**
	 * Construct object access.
	 *
	 * @param object $object Object.
	 */
	public function __construct( object $object ) {
		$this->object = $object;
	}

	/**
	 * Checks if the object has a property.
	 *
	 * @param string $property Property.
	 * @return bool True if the property exists, false if it doesn't exist.
	 */
	public function has_property( $property ) {
		return \property_exists( $this->object, $property );
	}

	/**
	 * Get property.
	 *
	 * @param string $property Property.
	 * @return mixed
	 * @throws \Exception Throws exception when property does not exists.
	 */
	public function get_property( $property ) {
		if ( ! \property_exists( $this->object, $property ) ) {
			throw new \Exception( \sprintf( 'Object does not have `%s` property.', $property ) );
		}

		return $this->object->{$property};
	}
}
