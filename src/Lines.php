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

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Pronamic\WordPress\Pay\Payments\PaymentLines;
use Traversable;

/**
 * Lines
 *
 * @author     Reüel van der Steege
 * @version    4.3.0
 * @since      4.3.0
 * @implements \IteratorAggregate<int, Line>
 */
class Lines implements Countable, IteratorAggregate {
	/**
	 * The lines.
	 *
	 * @var array
	 */
	private array $lines;

	/**
	 * Constructs and initialize a payment lines object.
	 */
	public function __construct() {
		$this->lines = [];
	}

	/**
	 * Get iterator.
	 *
	 * @return ArrayIterator<int, Line>
	 */
	public function getIterator() : Traversable {
		return new ArrayIterator( $this->lines );
	}

	/**
	 * Get array.
	 *
	 * @return array<int, Line>
	 */
	public function get_array() : array {
		return $this->lines;
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
	 * Count lines.
	 *
	 * @return int
	 */
	public function count() : int {
		return count( $this->lines );
	}

	/**
	 * Get JSON.
	 *
	 * @return array
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

		return $objects;
	}

	/**
	 * Create lines from object.
	 *
	 * @param array $items Lines.
	 *
	 * @return Lines
	 * @throws InvalidArgumentException Throws invalid argument exception when object does not contain the required properties.
	 */
	public static function from_array( array $items ) : Lines {
		$lines = new self();

		array_map(
			/**
			 * Get JSON for line.
			 *
			 * @param object $line Line.
			 */
			function( object $line ) use ( $lines ) {
				$validator = new Validator();

				$validator->validate(
					$line,
					(object) [
						'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/line.json' ),
					],
					Constraint::CHECK_MODE_EXCEPTIONS
				);

				$lines->add_line( Line::from_json( $line ) );
			},
			$items
		);

		return $lines;
	}

	/**
	 * Create amount from JSON string.
	 *
	 * @param object $json JSON object.
	 *
	 * @return Amount
	 *
	 * @throws InvalidArgumentException Throws invalid argument exception when input JSON is not an object.
	 */
	public static function from_json( $json ) {
		if ( ! \is_array( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an array.' );
		}

		return self::from_array( $json );
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

			$unit_price = $payment_line->get_unit_price();

			if ( null === $unit_price ) {
				throw new \InvalidArgumentException( 'Payment line unit price is required.' );
			}

			$total_amount = $payment_line->get_total_amount();

			if ( null === $total_amount ) {
				throw new \InvalidArgumentException( 'Payment line total amount is required.' );
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
