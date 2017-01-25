<?php

/**
 * Title: Mollie statuses constants
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.9
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Mollie_Statuses {
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

	/////////////////////////////////////////////////

	/**
	 * Transform an Mollie state to an more global status
	 *
	 * @param string $status
	 */
	public static function transform( $status ) {
		switch ( $status ) {
			case self::PENDING :
			case self::OPEN :
				return Pronamic_WP_Pay_Statuses::OPEN;
			case self::CANCELLED :
				return Pronamic_WP_Pay_Statuses::CANCELLED;
			case self::PAID_OUT :
				return Pronamic_WP_Pay_Statuses::SUCCESS;
			case self::ACTIVE :
			case self::PAID :
				return Pronamic_WP_Pay_Statuses::SUCCESS;
			case self::EXPIRED :
				return Pronamic_WP_Pay_Statuses::EXPIRED;
			default :
				return null;
		}
	}
}
