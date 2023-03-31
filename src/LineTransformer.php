<?php
/**
 * Line transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Line as MollieLine;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Pay\Refunds\Refund as PronamicRefund;
use Pronamic\WordPress\Pay\Refunds\RefundLine as PronamicRefundLine;

/**
 * Line transformer class
 */
class LineTransformer {
	/**
	 * Transform Mollie line to Pronamic line.
	 *
	 * @param MollieLine     $mollie_line     Mollie line.
	 * @param PronamicRefund $pronamic_refund Pronamic refund.
	 * @return PronamicRefundLine
	 * @throws \InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function transform_mollie_to_pronamic( MollieLine $mollie_line, PronamicRefund $pronamic_refund ): PronamicRefundLine {
		$pronamic_refund_line = $pronamic_refund->lines->new_line();

		return $this->update_mollie_to_pronamic( $mollie_line, $pronamic_refund_line );
	}

	/**
	 * Update Pronamic refund from Mollie refund.
	 *
	 * @param MollieLine         $mollie_line          Mollie line.
	 * @param PronamicRefundLine $pronamic_refund_line Pronamic refund line.
	 * @return PronamicRefundLine
	 */
	public function update_mollie_to_pronamic( MollieLine $mollie_line, PronamicRefundLine $pronamic_refund_line ): PronamicRefundLine {
		$total_amount = new TaxedMoney(
			$mollie_line->total_amount->get_value(),
			$mollie_line->total_amount->get_currency(),
			$mollie_line->vat_amount->get_value(),
			$mollie_line->vat_rate
		);

		$pronamic_refund_line->set_id( (string) $mollie_line->get_id() );
		$pronamic_refund_line->set_quantity( Number::from_int( $mollie_line->quantity ) );
		$pronamic_refund_line->set_total_amount( $total_amount );
		$pronamic_refund_line->meta['mollie_order_line_id'] = $mollie_line->get_id();

		return $pronamic_refund_line;
	}
}
