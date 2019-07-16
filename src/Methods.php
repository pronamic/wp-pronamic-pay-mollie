<?php
/**
 * Mollie methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Mollie methods
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class Methods {
	/**
	 * Constant for the Bancontact method.
	 *
	 * @var string
	 */
	const BANCONTACT = 'bancontact';

	/**
	 * Constant for the iDEAL method.
	 *
	 * @var string
	 */
	const IDEAL = 'ideal';

	/**
	 * Constant for the Credit Card method.
	 *
	 * @var string
	 */
	const CREDITCARD = 'creditcard';

	/**
	 * Constant for the Direct Debit method.
	 *
	 * @var string
	 */
	const DIRECT_DEBIT = 'directdebit';

	/**
	 * Constant for the Sofort method.
	 *
	 * @var string
	 */
	const SOFORT = 'sofort';

	/**
	 * Constant for the Bank transfer method.
	 *
	 * @var string
	 */
	const BANKTRANSFER = 'banktransfer';

	/**
	 * Constant for the Bitcoin method.
	 *
	 * @var string
	 */
	const BITCOIN = 'bitcoin';

	/**
	 * Constant for the EPS method.
	 *
	 * @var string
	 */
	const EPS = 'eps';

	/**
	 * Constant for the Giropay method.
	 *
	 * @var string
	 */
	const GIROPAY = 'giropay';

	/**
	 * Constant for the PayPal method.
	 *
	 * @var string
	 */
	const PAYPAL = 'paypal';

	/**
	 * Constant for the Paysafecard method.
	 *
	 * @var string
	 */
	const PAYSAFECARD = 'paysafecard';

	/**
	 * Constant for the Gift cards method.
	 *
	 * @link https://www.mollie.com/en/giftcards
	 * @since 1.1.10
	 * @var string
	 */
	const PODIUMCADEAUKAART = 'podiumcadeaukaart';

	/**
	 * Constant for the KBC/CBC Payment Button method.
	 *
	 * @link https://www.mollie.com/en/kbccbc
	 * @since 1.1.10
	 * @var string
	 */
	const KBC = 'kbc';

	/**
	 * Constant for the Belfius Direct Net method.
	 *
	 * @link https://www.mollie.com/en/belfiusdirectnet
	 * @since 1.1.10
	 * @var string
	 */
	const BELFIUS = 'belfius';

	/**
	 * Payments methods map.
	 *
	 * @var array
	 */
	private static $map = array(
		PaymentMethods::BANCONTACT              => self::BANCONTACT,
		PaymentMethods::BANK_TRANSFER           => self::BANKTRANSFER,
		PaymentMethods::BITCOIN                 => self::BITCOIN,
		PaymentMethods::CREDIT_CARD             => self::CREDITCARD,
		PaymentMethods::DIRECT_DEBIT            => self::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_BANCONTACT => self::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_IDEAL      => self::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_SOFORT     => self::DIRECT_DEBIT,
		PaymentMethods::EPS                     => self::EPS,
		PaymentMethods::GIROPAY                 => self::GIROPAY,
		PaymentMethods::PAYPAL                  => self::PAYPAL,
		PaymentMethods::SOFORT                  => self::SOFORT,
		PaymentMethods::IDEAL                   => self::IDEAL,
		PaymentMethods::KBC                     => self::KBC,
		PaymentMethods::BELFIUS                 => self::BELFIUS,
	);

	/**
	 * Transform WordPress payment method to Mollie method.
	 *
	 * @since 1.1.6
	 *
	 * @param string $payment_method Payment method.
	 * @param mixed  $default        Default payment method.
	 *
	 * @return string
	 */
	public static function transform( $payment_method, $default = null ) {
		if ( ! is_scalar( $payment_method ) ) {
			return null;
		}

		if ( isset( self::$map[ $payment_method ] ) ) {
			return self::$map[ $payment_method ];
		}

		if ( ! empty( $default ) ) {
			return $default;
		}

		return null;
	}

	/**
	 * Transform Mollie method to WordPress payment method.
	 *
	 * @since unreleased
	 *
	 * @param string $method Mollie method.
	 *
	 * @return string
	 */
	public static function transform_gateway_method( $method ) {
		if ( ! is_scalar( $method ) ) {
			return null;
		}

		$payment_method = array_search( $method, self::$map, true );

		if ( ! $payment_method ) {
			return null;
		}

		return $payment_method;
	}
}
