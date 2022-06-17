<?php
/**
 * Lines
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Payments\PaymentLines;

/**
 * Lines class
 */
class Lines {
	/**
	 * The lines.
	 *
	 * @var Line[]
	 */
	private array $lines;

	/**
	 * Constructs and initialize a payment lines object.
	 */
	public function __construct() {
		$this->lines = [];
	}

	/**
	 * Add line.
	 *
	 * @param Line $line The line to add.
	 * @return void
	 */
	public function add_line( Line $line ) : void {
		$this->lines[] = $line;
	}

	/**
	 * New line.
	 *
	 * @param string $name         Description of the order line.
	 * @param int    $quantity     Quantity.
	 * @param Amount $unit_price   Unit price.
	 * @param Amount $total_amount Total amount, including VAT and  discounts.
	 * @param string $vat_rate     VAT rate.
	 * @param Amount $vat_amount   Value-added tax amount.
	 */
	public function new_line( string $name, int $quantity, Amount $unit_price, Amount $total_amount, string $vat_rate, Amount $vat_amount ) : Line {
		$line = new Line(
			$name,
			$quantity,
			$unit_price,
			$total_amount,
			$vat_rate,
			$vat_amount
		);

		$this->add_line( $line );

		return $line;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$objects = array_map(
			/**
			 * Get JSON for payment line.
			 *
			 * @param Line $line Payment line.
			 * @return object
			 */
			function( Line $line ) {
				return $line->get_json();
			},
			$this->lines
		);

		return (object) $objects;
	}

	/**
	 * Create lines from WordPress Pay core payment lines.
	 *
	 * @param PaymentLines $payment_lines Payment lines.
	 * @return Lines
	 * @throws \InvalidArgumentException Throws exception on invalid arguments.
	 */
	public static function from_wp_payment_lines( PaymentLines $payment_lines ) : Lines {
		$lines = new self();

		foreach ( $payment_lines as $payment_line ) {
			if ( $payment_line->get_total_amount()->get_number()->is_zero() ) {
				continue;
			}

			$total_amount = $payment_line->get_total_amount();

			$unit_price = $payment_line->get_unit_price();

			if ( null === $unit_price ) {
				throw new \InvalidArgumentException( 'Payment line unit price is required.' );
			}

			$vat_amount = $payment_line->get_tax_amount();

			if ( null === $vat_amount ) {
				throw new \InvalidArgumentException( 'Payment line VAT amount is required.' );
			}

			$tax_percentage = $payment_line->get_tax_percentage();

			if ( null === $tax_percentage ) {
				throw new \InvalidArgumentException( 'Payment line VAT rate is required.' );
			}

			$line = $lines->new_line(
				$payment_line->get_name(),
				$payment_line->get_quantity(),
				AmountTransformer::transform( $unit_price ),
				AmountTransformer::transform( $total_amount ),
				\number_format( $tax_percentage, 2, '.', '' ),
				AmountTransformer::transform( $vat_amount ),
			);

			$line->set_type( LineType::transform( $payment_line->get_type() ) );
			$line->set_category( $payment_line->get_product_category() );
			$line->set_sku( $payment_line->get_sku() );
			$line->set_image_url( $payment_line->get_image_url() );
			$line->set_product_url( $payment_line->get_product_url() );

			// Discount amount.
			$discount_amount = $payment_line->get_discount_amount();

			$line->set_discount_amount( null === $discount_amount ? null : AmountTransformer::transform( $discount_amount ) );
		}

		return $lines;
	}
}
