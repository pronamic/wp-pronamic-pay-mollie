<?php
/**
 * Mollie statuses.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Payments\PaymentStatus;

/**
 * Statuses class
 *
 * @link https://docs.mollie.com/payments/status-changes
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
				return PaymentStatus::OPEN;
			case self::CANCELED:
				return PaymentStatus::CANCELLED;
			case self::AUTHORIZED:
				return PaymentStatus::RESERVED;
			case self::PAID:
				return PaymentStatus::SUCCESS;
			case self::EXPIRED:
				return PaymentStatus::EXPIRED;
			case self::FAILED:
				return PaymentStatus::FAILURE;
			default:
				return null;
		}
	}
}
