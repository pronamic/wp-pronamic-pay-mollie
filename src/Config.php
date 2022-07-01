<?php
/**
 * Mollie config.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\GatewayConfig;

/**
 * Config class
 */
class Config extends GatewayConfig {
	/**
	 * ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * API key.
	 *
	 * @var string|null
	 */
	public $api_key;

	/**
	 * Bank transfer due date days.
	 *
	 * @var string|null
	 */
	public $due_date_days;

	/**
	 * Function to check for test API key.
	 *
	 * @return bool True if test mode, false otherwise.
	 */
	public function is_test_mode() {
		if ( null === $this->api_key ) {
			return false;
		}

		return ( 'test_' === substr( $this->api_key, 0, 5 ) );
	}
}
