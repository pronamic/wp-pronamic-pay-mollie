<?php
/**
 * Mollie config.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\GatewayConfig;

/**
 * Title: Mollie config
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
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
	 * Profile ID.
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
}
