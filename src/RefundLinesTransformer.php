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

use Pronamic\WordPress\Mollie\AmountTransformer;
use Pronamic\WordPress\Mollie\OrderRefundLinesRequest as MollieRefundLines;
use Pronamic\WordPress\Pay\Refunds\RefundLines as WordPressLines;

/**
 * Refund lines transformer class
 */
class RefundLinesTransformer {
	/**
	 * Create refund lines from WordPress Pay core payment lines.
	 *
	 * @param WordPressLines $refund_lines Refund lines.
	 * @return MollieRefundLines
	 * @throws \InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function transform_wp_to_mollie( WordPressLines $refund_lines ): MollieRefundLines {
		$lines = new MollieRefundLines();

		$amount_transformer = new AmountTransformer();

		foreach ( $refund_lines as $refund_line ) {
			$id = $refund_line->get_meta( 'mollie_order_line_id' );

			if ( null === $id ) {
				throw new \InvalidArgumentException( 'Line identifier is required.' );
			}

			$line = $lines->new_line( $id );

			$line->set_quantity( $refund_line->get_quantity()->to_int() );
			$line->set_amount( $amount_transformer->transform_wp_to_mollie( $refund_line->get_total_amount() ) );
		}

		return $lines;
	}
}
