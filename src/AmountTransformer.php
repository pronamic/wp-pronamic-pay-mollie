<?php
/**
 * Amount transformer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
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
	 * @return Amount
	 * @throws \InvalidArgumentException Throws exception on invalid alphabetic currency code in given money object.
	 */
	public static function transform( Money $money ) {
		$amount = new Amount(
			$money->get_currency()->get_alphabetic_code(),
			/**
			 * Make sure to send the right amount of decimals and omit the
			 * thousands separator. Non-string values are not accepted.
			 * 
			 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
			 */
			$money->number_format( null, '.', '' )
		);

		return $amount;
	}
}
