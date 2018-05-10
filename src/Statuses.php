<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\Statuses as Core_Statuses;

/**
 * Title: Mollie statuses constants
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 2.0.0
 * @since 1.0.0
 */
class Statuses {
	/**
	 * Open
	 *
	 * @var string
	 */
	const OPEN = 'open';

	/**
	 * Cancelled
	 *
	 * @var string
	 */
	const CANCELLED = 'cancelled';

	/**
	 * Paid out
	 *
	 * @var string
	 */
	const PAID_OUT = 'paidout';

	/**
	 * Paid
	 *
	 * @var string
	 */
	const PAID = 'paid';

	/**
	 * Expired
	 *
	 * @var string
	 */
	const EXPIRED = 'expired';

	/**
	 * Pending
	 *
	 * @var string
	 */
	const PENDING = 'pending';

	/**
	 * Active
	 *
	 * @since 1.1.9
	 * @var string
	 */
	const ACTIVE = 'active';

	/**
	 * Transform an Mollie state to an more global status
	 *
	 * @param string $status
	 */
	public static function transform( $status ) {
		switch ( $status ) {
			case self::PENDING:
			case self::OPEN:
				return Core_Statuses::OPEN;

			case self::CANCELLED:
				return Core_Statuses::CANCELLED;

			case self::PAID_OUT:
				return Core_Statuses::SUCCESS;

			case self::ACTIVE:
				return Core_Statuses::ACTIVE;

			case self::PAID:
				return Core_Statuses::SUCCESS;

			case self::EXPIRED:
				return Core_Statuses::EXPIRED;

			default:
				return null;
		}
	}
}
