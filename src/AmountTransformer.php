<?php
/**
 * Amount transformer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
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
		$alphabetic_code = $money->get_currency()->get_alphabetic_code();

		if ( null === $alphabetic_code ) {
			throw new \InvalidArgumentException( 'Alphabetic currency code is required to transform money to Mollie amount object.' );
		}

		$amount = new Amount(
			\strval( $alphabetic_code ),
			$money->get_value()
		);

		return $amount;
	}
}
