<?php
/**
 * Mollie profile.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Mollie profile.
 *
 * @link    https://docs.mollie.com/reference/v2/profiles-api/create-profile
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
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
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}
}
