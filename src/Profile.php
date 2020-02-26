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
}
