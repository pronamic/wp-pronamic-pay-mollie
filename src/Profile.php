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
 * Profile class
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

		$object_access = new ObjectAccess( $object );

		$profile->set_id( $object_access->get_optional( 'id' ) );
		$profile->set_mode( $object_access->get_optional( 'mode' ) );
		$profile->set_name( $object_access->get_optional( 'name' ) );
		$profile->set_email( $object_access->get_optional( 'email' ) );

		return $profile;
	}
}
