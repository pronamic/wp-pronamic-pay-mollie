<?php
/**
 * Mollie statuses.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\Statuses as Core_Statuses;

/**
 * Title: Mollie statuses constants
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class Statuses {
	/**
	 * Authorized.
	 *
	 * @var string
	 */
	const AUTHORIZED = 'authorized';

	/**
	 * Open.
	 *
	 * @var string
	 */
	const OPEN = 'open';

	/**
	 * Canceled.
	 *
	 * @var string
	 */
	const CANCELED = 'canceled';

	/**
	 * Paid.
	 *
	 * @var string
	 */
	const PAID = 'paid';

	/**
	 * Expired.
	 *
	 * @var string
	 */
	const EXPIRED = 'expired';

	/**
	 * Failed.
	 *
	 * @since 2.0.3
	 * @var string
	 */
	const FAILED = 'failed';

	/**
	 * Pending.
	 *
	 * @var string
	 */
	const PENDING = 'pending';

	/**
	 * Active.
	 *
	 * @since 1.1.9
	 * @var string
	 */
	const ACTIVE = 'active';

	/**
	 * Transform an Mollie state to an more global status.
	 *
	 * @param string $status Mollie status.
	 *
	 * @return string|null Pay status.
	 */
	public static function transform( $status ) {
		switch ( $status ) {
			case self::PENDING:
			case self::OPEN:
				return Core_Statuses::OPEN;

			case self::CANCELED:
				return Core_Statuses::CANCELLED;

			case self::ACTIVE:
				return Core_Statuses::ACTIVE;

			case self::PAID:
				return Core_Statuses::SUCCESS;

			case self::EXPIRED:
				return Core_Statuses::EXPIRED;

			case self::FAILED:
				return Core_Statuses::FAILURE;

			default:
				return null;
		}
	}
}
