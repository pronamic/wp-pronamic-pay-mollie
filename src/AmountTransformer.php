<?php
/**
 * Amount transformer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Money\Money;

/**
 * Amount transformer
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.1.0
 */
class AmountTransformer {
	/**
	 * Transform Pronamic money to Mollie amount.
	 *
	 * @param Money $money Pronamic money to convert.
	 *
	 * @return Amount
	 */
	public static function transform( Money $money ) {
		$amount = new Amount(
			strval( $money->get_currency()->get_alphabetic_code() ),
			$money->format()
		);

		return $amount;
	}
}
