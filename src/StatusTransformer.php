<?php
/**
 * Status transformer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
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
		return match ( $status ) {
			MollieStatus::PENDING, MollieStatus::OPEN => WordPressStatus::OPEN,
			MollieStatus::CANCELED => WordPressStatus::CANCELLED,
			MollieStatus::AUTHORIZED => WordPressStatus::AUTHORIZED,
			MollieStatus::PAID => WordPressStatus::SUCCESS,
			MollieStatus::EXPIRED => WordPressStatus::EXPIRED,
			MollieStatus::FAILED => WordPressStatus::FAILURE,
			default => null,
		};
	}
}
