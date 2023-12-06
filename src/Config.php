<?php
/**
 * Mollie config.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use JsonSerializable;
use Pronamic\WordPress\Pay\Core\GatewayConfig;

/**
 * Config class
 */
class Config extends GatewayConfig implements JsonSerializable {
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
	 * Mollie profile ID.
	 *
	 * @var string|null
	 */
	public $profile_id;

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

		return \str_starts_with( $this->api_key, 'test_' );
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return object
	 */
	public function jsonSerialize(): object {
		return (object) [
			'@type'   => __CLASS__,
			'api_key' => $this->api_key,
		];
	}
}
