<?php
/**
 * Mollie transformer methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods as WordPressMethod;
use Pronamic\WordPress\Mollie\Methods as MollieMethod;

/**
 * Methods transformer class
 */
class MethodTransformer {
	/**
	 * Payments methods map.
	 *
	 * @var array<string>
	 */
	private static $map = [
		WordPressMethod::APPLE_PAY               => MollieMethod::APPLE_PAY,
		WordPressMethod::BANCONTACT              => MollieMethod::BANCONTACT,
		WordPressMethod::BANK_TRANSFER           => MollieMethod::BANKTRANSFER,
		WordPressMethod::BILLIE                  => MollieMethod::BILLIE,
		WordPressMethod::CREDIT_CARD             => MollieMethod::CREDITCARD,
		WordPressMethod::DIRECT_DEBIT            => MollieMethod::DIRECT_DEBIT,
		WordPressMethod::DIRECT_DEBIT_BANCONTACT => MollieMethod::DIRECT_DEBIT,
		WordPressMethod::DIRECT_DEBIT_IDEAL      => MollieMethod::DIRECT_DEBIT,
		WordPressMethod::DIRECT_DEBIT_SOFORT     => MollieMethod::DIRECT_DEBIT,
		WordPressMethod::EPS                     => MollieMethod::EPS,
		WordPressMethod::GIROPAY                 => MollieMethod::GIROPAY,
		WordPressMethod::KLARNA_PAY_LATER        => MollieMethod::KLARNA_PAY_LATER,
		WordPressMethod::KLARNA_PAY_NOW          => MollieMethod::KLARNA_PAY_NOW,
		WordPressMethod::KLARNA_PAY_OVER_TIME    => MollieMethod::KLARNA_SLICE_IT,
		WordPressMethod::PAYPAL                  => MollieMethod::PAYPAL,
		WordPressMethod::PRZELEWY24              => MollieMethod::PRZELEWY24,
		WordPressMethod::SOFORT                  => MollieMethod::SOFORT,
		WordPressMethod::IDEAL                   => MollieMethod::IDEAL,
		WordPressMethod::IN3                     => MollieMethod::IN3,
		WordPressMethod::KBC                     => MollieMethod::KBC,
		WordPressMethod::BELFIUS                 => MollieMethod::BELFIUS,
		WordPressMethod::TWINT                   => MollieMethod::TWINT,
	];

	/**
	 * Transform WordPress payment method to Mollie method.
	 *
	 * @since 1.1.6
	 *
	 * @param string|null $payment_method Payment method.
	 * @param mixed       $fallback       Default payment method.
	 * @return string|null
	 */
	public static function transform_wp_to_mollie( $payment_method, $fallback = null ) {
		if ( ! \is_scalar( $payment_method ) ) {
			return null;
		}

		if ( \array_key_exists( $payment_method, self::$map ) ) {
			return self::$map[ $payment_method ];
		}

		if ( ! empty( $fallback ) ) {
			return $fallback;
		}

		return null;
	}

	/**
	 * Transform Mollie method to WordPress payment method.
	 *
	 * @param string|null $method Mollie method.
	 * @return string|null
	 */
	public static function transform_mollie_to_wp( $method ) {
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
