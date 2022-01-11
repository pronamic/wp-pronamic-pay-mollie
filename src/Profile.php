<?php
/**
 * Mollie profile.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Mollie profile.
 *
 * @link    https://docs.mollie.com/reference/v2/profiles-api/create-profile
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class Profile {
	/**
	 * ID.
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * Mode.
	 *
	 * @var string|null
	 */
	private $mode;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * Email.
	 *
	 * @var string|null
	 */
	private $email;

	/**
	 * Get ID.
	 *
	 * @return string|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param string|null $id ID.
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get mode.
	 *
	 * @return string|null
	 */
	public function get_mode() {
		return $this->mode;
	}

	/**
	 * Set mode.
	 *
	 * @param string|null $mode Mode.
	 * @return void
	 */
	public function set_mode( $mode ) {
		$this->mode = $mode;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string|null $name Name.
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Get email.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string|null $email Email.
	 * @return void
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Create profile from object.
	 *
	 * @param object $object Object.
	 * @return Profile
	 */
	public static function from_object( $object ) {
		$profile = new self();

		if ( property_exists( $object, 'id' ) ) {
			$profile->set_id( $object->id );
		}

		if ( property_exists( $object, 'mode' ) ) {
			$profile->set_mode( $object->mode );
		}

		if ( property_exists( $object, 'name' ) ) {
			$profile->set_name( $object->name );
		}

		if ( property_exists( $object, 'email' ) ) {
			$profile->set_email( $object->email );
		}

		return $profile;
	}
}
