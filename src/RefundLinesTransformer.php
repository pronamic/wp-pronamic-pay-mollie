<?php
/**
 * Refund lines transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use InvalidArgumentException;
use Pronamic\WordPress\Mollie\AmountTransformer;
use Pronamic\WordPress\Mollie\RefundLines as MollieRefundLines;
use Pronamic\WordPress\Pay\Refunds\RefundLines as WordPressLines;

/**
 * Refund lines transformer class
 */
class RefundLinesTransformer {
	/**
	 * Create refund lines from WordPress Pay core payment lines.
	 *
	 * @param WordPressLines $payment_lines Payment lines.
	 * @return MollieRefundLines
	 * @throws \InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function transform_wp_to_mollie( WordPressLines $payment_lines ): MollieRefundLines {
		$lines = new MollieRefundLines();

		$amount_transformer = new AmountTransformer();

		foreach ( $payment_lines as $payment_line ) {
			$id = $payment_line->get_id();

			if ( null === $id ) {
				throw new \InvalidArgumentException( 'Payment line identifier is required.' );
			}

			$line = $lines->new_line( $id );

			$line->set_quantity( $payment_line->get_quantity() );
			$line->set_amount( $amount_transformer->transform_wp_to_mollie( $payment_line->get_total_amount() ) );
		}

		return $lines;
	}
}
