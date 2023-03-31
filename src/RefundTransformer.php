<?php
/**
 * Refund transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTimeImmutable;
use Pronamic\WordPress\Mollie\AmountTransformer;
use Pronamic\WordPress\Mollie\Refund as MollieRefund;
use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Pay\Payments\Payment as PronamicPayment;
use Pronamic\WordPress\Pay\Refunds\Refund as PronamicRefund;
use Pronamic\WordPress\Pay\Refunds\RefundLines as PronamicRefundLines;

/**
 * Refund transformer class
 */
class RefundTransformer {
	/**
	 * Create Pronamic refund from Mollie refund.
	 *
	 * @param MollieRefund    $mollie_refund    Mollie refund.
	 * @param PronamicPayment $pronamic_payment Pronamic payment.
	 * @return PronamicRefund
	 */
	public function transform_mollie_to_pronamic( MollieRefund $mollie_refund, PronamicPayment $pronamic_payment ): PronamicRefund {
		$amount_transformer = new AmountTransformer();

		$amount = $amount_transformer->transform_mollie_to_wp( $mollie_refund->get_amount() );

		$pronamic_refund = new PronamicRefund( $pronamic_payment, $amount );

		return $this->update_mollie_to_pronamic( $mollie_refund, $pronamic_refund );
	}

	/**
	 * Update Pronamic refund from Mollie refund.
	 *
	 * @param MollieRefund   $mollie_refund   Mollie refund.
	 * @param PronamicRefund $pronamic_refund Pronamic refund.
	 * @return PronamicRefund
	 */
	public function update_mollie_to_pronamic( MollieRefund $mollie_refund, PronamicRefund $pronamic_refund ): PronamicRefund {
		$pronamic_payment = $pronamic_refund->get_payment();

		$amount_transformer = new AmountTransformer();
		$line_transformer   = new LineTransformer();

		$amount = $amount_transformer->transform_mollie_to_wp( $mollie_refund->get_amount() );

		$pronamic_refund->created_at = DateTimeImmutable::create_from_interface( $mollie_refund->get_created_at() );
		$pronamic_refund->amount     = $amount;
		$pronamic_refund->psp_id     = $mollie_refund->get_id();
		$pronamic_refund->set_description( $mollie_refund->get_description() );

		$map_refund_lines = [];

		foreach ( $pronamic_refund->lines as $line ) {
			$id = $line->get_id();

			$map_refund_lines[ $id ] = $line;
		}

		$map_payment_lines = [];

		if ( null !== $pronamic_payment->lines ) {
			foreach ( $pronamic_payment->lines as $line ) {
				$meta_id = $line->get_meta( 'mollie_order_line_id' );

				if ( null !== $meta_id ) {
					$map_payment_lines[ $meta_id ] = $line;
				}
			}
		}

		if ( null !== $mollie_refund->lines ) {
			foreach ( $mollie_refund->lines as $mollie_line ) {
				$id = (string) $mollie_line->get_id();

				if ( \array_key_exists( $id, $map_refund_lines ) ) {
					$pronamic_refund_line = $map_refund_lines[ $id ];

					$line_transformer->update_mollie_to_pronamic( $mollie_line, $pronamic_refund_line );
				} else {
					$pronamic_refund_line = $line_transformer->transform_mollie_to_pronamic( $mollie_line, $pronamic_refund );
				}

				if ( \array_key_exists( $id, $map_payment_lines ) ) {
					$pronamic_payment_line = $map_payment_lines[ $id ];

					$pronamic_refund_line->set_payment_line( $pronamic_payment_line );
				}
			}
		}

		$pronamic_refund->meta['mollie_refund_id'] = $mollie_refund->get_id();

		return $pronamic_refund;
	}
}
