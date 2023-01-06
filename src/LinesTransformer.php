<?php
/**
 * Lines transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use InvalidArgumentException;
use Pronamic\WordPress\Mollie\AmountTransformer;
use Pronamic\WordPress\Mollie\Lines as MollieLines;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Pay\Payments\PaymentLines as WordPressLines;

/**
 * Lines transformer class
 */
class LinesTransformer {
	/**
	 * Create lines from WordPress Pay core payment lines.
	 *
	 * @param WordPressLines $payment_lines Payment lines.
	 * @return MollieLines
	 * @throws \InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function transform_wp_to_mollie( WordPressLines $payment_lines ): MollieLines {
		$lines = new MollieLines();

		$amount_transformer    = new AmountTransformer();
		$line_type_transformer = new LineTypeTransformer();

		foreach ( $payment_lines as $payment_line ) {
			$total_amount = $payment_line->get_total_amount();

			if ( ! $total_amount instanceof TaxedMoney ) {
				throw new \InvalidArgumentException( 'Payment line requires tax information.' );
			}

			$unit_price = $payment_line->get_unit_price();

			if ( null === $unit_price ) {
				throw new \InvalidArgumentException( 'Payment line unit price is required.' );
			}

			$vat_amount = $payment_line->get_tax_amount();

			if ( null === $vat_amount ) {
				throw new \InvalidArgumentException( 'Payment line VAT amount is required.' );
			}

			$tax_percentage = $total_amount->get_tax_percentage();

			if ( null === $tax_percentage ) {
				throw new \InvalidArgumentException( 'Payment line VAT rate is required.' );
			}

			$name = $payment_line->get_name();

			if ( null === $name ) {
				throw new \InvalidArgumentException( 'Payment line name is required.' );
			}

			$quantity = $payment_line->get_quantity();

			if ( null === $quantity ) {
				throw new \InvalidArgumentException( 'Payment line quantity is required.' );
			}

			$line = $lines->new_line(
				$name,
				$quantity,
				$amount_transformer->transform_wp_to_mollie( $unit_price ),
				$amount_transformer->transform_wp_to_mollie( $total_amount ),
				Number::from_mixed( $tax_percentage ),
				$amount_transformer->transform_wp_to_mollie( $vat_amount ),
			);

			$line->set_type( $line_type_transformer->transform_wp_to_mollie( $payment_line->get_type() ) );
			$line->set_sku( $payment_line->get_sku() );
			$line->set_image_url( $payment_line->get_image_url() );
			$line->set_product_url( $payment_line->get_product_url() );

			// Discount amount.
			$discount_amount = $payment_line->get_discount_amount();

			$line->set_discount_amount( null === $discount_amount ? null : $amount_transformer->transform_wp_to_mollie( $discount_amount ) );
		}

		return $lines;
	}
}
