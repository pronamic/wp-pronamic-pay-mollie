<?php
/**
 * Mollie methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Methods class
 */
class Methods {
	/**
	 * Constant for the Apple Pay method.
	 *
	 * @var string
	 */
	const APPLE_PAY = 'applepay';

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
	 * Constant for the Klarna - Pay Later method.
	 *
	 * @var string
	 */
	const KLARNA_PAY_LATER = 'klarnapaylater';

	/**
	 * Constant for the Klarna - Pay Now method.
	 *
	 * @var string
	 */
	const KLARNA_PAY_NOW = 'klarnapaynow';

	/**
	 * Constant for the Klarna - Slice It method.
	 *
	 * @var string
	 */
	const KLARNA_SLICE_IT = 'klarnasliceit';

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
	 * Constant for the Przelewy24 method.
	 *
	 * @var string
	 */
	const PRZELEWY24 = 'przelewy24';

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
	 * @var array<string>
	 */
	private static $map = [
		PaymentMethods::APPLE_PAY               => self::APPLE_PAY,
		PaymentMethods::BANCONTACT              => self::BANCONTACT,
		PaymentMethods::BANK_TRANSFER           => self::BANKTRANSFER,
		PaymentMethods::CREDIT_CARD             => self::CREDITCARD,
		PaymentMethods::DIRECT_DEBIT            => self::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_BANCONTACT => self::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_IDEAL      => self::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_SOFORT     => self::DIRECT_DEBIT,
		PaymentMethods::EPS                     => self::EPS,
		PaymentMethods::GIROPAY                 => self::GIROPAY,
		PaymentMethods::KLARNA_PAY_LATER        => self::KLARNA_PAY_LATER,
		PaymentMethods::KLARNA_PAY_NOW          => self::KLARNA_PAY_NOW,
		PaymentMethods::KLARNA_PAY_OVER_TIME    => self::KLARNA_SLICE_IT,
		PaymentMethods::PAYPAL                  => self::PAYPAL,
		PaymentMethods::PRZELEWY24              => self::PRZELEWY24,
		PaymentMethods::SOFORT                  => self::SOFORT,
		PaymentMethods::IDEAL                   => self::IDEAL,
		PaymentMethods::KBC                     => self::KBC,
		PaymentMethods::BELFIUS                 => self::BELFIUS,
	];

	/**
	 * Transform WordPress payment method to Mollie method.
	 *
	 * @since 1.1.6
	 *
	 * @param string|null $payment_method Payment method.
	 * @param mixed       $default        Default payment method.
	 * @return string|null
	 */
	public static function transform( $payment_method, $default = null ) {
		if ( ! \is_scalar( $payment_method ) ) {
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
	 * @param string|null $method Mollie method.
	 * @return string|null
	 */
	public static function transform_gateway_method( $method ) {
		if ( ! \is_scalar( $method ) ) {
			return null;
		}

		$payment_method = \array_search( $method, self::$map, true );

		if ( false === $payment_method ) {
			return null;
		}

		return \strval( $payment_method );
	}
}
