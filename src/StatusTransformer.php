<?php
/**
 * Status transformer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Payments\PaymentStatus as WordPressStatus;
use Pronamic\WordPress\Mollie\Statuses as MollieStatus;

/**
 * Status transformer class
 *
 * @link https://docs.mollie.com/payments/status-changes
 */
class StatusTransformer {
	/**
	 * Transform an Mollie state to an more global status.
	 *
	 * @param string $status Mollie status.
	 * @return string|null Pay status.
	 */
	public static function transform_mollie_to_wp( $status ) {
		switch ( $status ) {
			case MollieStatus::PENDING:
			case MollieStatus::OPEN:
				return WordPressStatus::OPEN;
			case MollieStatus::CANCELED:
				return WordPressStatus::CANCELLED;
			case MollieStatus::AUTHORIZED:
				return WordPressStatus::AUTHORIZED;
			case MollieStatus::PAID:
				return WordPressStatus::SUCCESS;
			case MollieStatus::EXPIRED:
				return WordPressStatus::EXPIRED;
			case MollieStatus::FAILED:
				return WordPressStatus::FAILURE;
			default:
				return null;
		}
	}
}
