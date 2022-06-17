<?php
/**
 * Line type
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Payments\PaymentLineType;

/**
 * Line type class
 *
 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
 */
class LineType {
	/**
	 * Constant for 'digital' type.
	 *
	 * @var string
	 */
	const DIGITAL = 'digital';

	/**
	 * Constant for 'discount' type.
	 *
	 * @var string
	 */
	const DISCOUNT = 'discount';

	/**
	 * Constant for 'gift_card' type.
	 *
	 * @var string
	 */
	const GIFT_CARD = 'gift_card';

	/**
	 * Constant for 'physical' type.
	 *
	 * @var string
	 */
	const PHYSICAL = 'physical';

	/**
	 * Constant for 'shipping_fee' type.
	 *
	 * @var string
	 */
	const SHIPPING_FEE = 'shipping_fee';

	/**
	 * Constant for 'store_credit' type.
	 *
	 * @var string
	 */
	const STORE_CREDIT = 'store_credit';

	/**
	 * Constant for 'surcharge' type.
	 *
	 * @var string
	 */
	const SURCHARGE = 'surcharge';

	/**
	 * Line type map.
	 *
	 * @var array<string, string>
	 */
	private static $map = [
		PaymentLineType::DIGITAL  => self::DIGITAL,
		PaymentLineType::DISCOUNT => self::DISCOUNT,
		PaymentLineType::FEE      => self::SURCHARGE,
		PaymentLineType::PHYSICAL => self::PHYSICAL,
		PaymentLineType::SHIPPING => self::SHIPPING_FEE,
	];

	/**
	 * Transform WordPress payment line type to Mollie line type.
	 *
	 * @since 4.3.0
	 * @param string $payment_line_type WordPress payment line type to transform to Mollie line type.
	 * @return string|null
	 */
	public static function transform( $payment_line_type ) {
		if ( ! is_scalar( $payment_line_type ) ) {
			return null;
		}

		if ( isset( self::$map[ $payment_line_type ] ) ) {
			return self::$map[ $payment_line_type ];
		}

		return null;
	}
}
