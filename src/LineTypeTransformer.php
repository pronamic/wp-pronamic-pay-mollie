<?php
/**
 * Line type transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\LineType as MollieLineType;
use Pronamic\WordPress\Pay\Payments\PaymentLineType;


/**
 * Line type transformer class
 *
 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
 */
class LineTypeTransformer {
	/**
	 * Line type map.
	 *
	 * @var array<string, string>
	 */
	private static $map = [
		PaymentLineType::DIGITAL  => MollieLineType::DIGITAL,
		PaymentLineType::DISCOUNT => MollieLineType::DISCOUNT,
		PaymentLineType::FEE      => MollieLineType::SURCHARGE,
		PaymentLineType::PHYSICAL => MollieLineType::PHYSICAL,
		PaymentLineType::SHIPPING => MollieLineType::SHIPPING_FEE,
	];

	/**
	 * Transform WordPress payment line type to Mollie line type.
	 *
	 * @since 4.3.0
	 * @param string $payment_line_type WordPress payment line type to transform to Mollie line type.
	 * @return string|null
	 */
	public static function transform_wp_to_mollie( $payment_line_type ) {
		if ( ! is_scalar( $payment_line_type ) ) {
			return null;
		}

		if ( isset( self::$map[ $payment_line_type ] ) ) {
			return self::$map[ $payment_line_type ];
		}

		return null;
	}
}
