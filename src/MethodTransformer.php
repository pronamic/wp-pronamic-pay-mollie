<?php
/**
 * Mollie transformer methods.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods as PronamicMethod;
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
		PronamicMethod::ALMA                    => MollieMethod::ALMA,
		PronamicMethod::APPLE_PAY               => MollieMethod::APPLE_PAY,
		PronamicMethod::BANCOMAT_PAY            => MollieMethod::BANCOMAT_PAY,
		PronamicMethod::BANCONTACT              => MollieMethod::BANCONTACT,
		PronamicMethod::BELFIUS                 => MollieMethod::BELFIUS,
		PronamicMethod::BILLIE                  => MollieMethod::BILLIE,
		PronamicMethod::BLIK                    => MollieMethod::BLIK,
		PronamicMethod::CARD                    => MollieMethod::CREDITCARD,
		PronamicMethod::CREDIT_CARD             => MollieMethod::CREDITCARD,
		PronamicMethod::EPS                     => MollieMethod::EPS,
		PronamicMethod::GIFT_CARD               => MollieMethod::GIFT_CARD,
		PronamicMethod::GIROPAY                 => MollieMethod::GIROPAY,
		PronamicMethod::GOOGLE_PAY              => MollieMethod::CREDITCARD,
		PronamicMethod::IDEAL                   => MollieMethod::IDEAL,
		PronamicMethod::IN3                     => MollieMethod::IN3,
		PronamicMethod::KBC                     => MollieMethod::KBC,
		PronamicMethod::KLARNA                  => MollieMethod::KLARNA,
		PronamicMethod::KLARNA_PAY_LATER        => MollieMethod::KLARNA,
		PronamicMethod::KLARNA_PAY_NOW          => MollieMethod::KLARNA,
		PronamicMethod::KLARNA_PAY_OVER_TIME    => MollieMethod::KLARNA,
		PronamicMethod::MB_WAY                  => MollieMethod::MB_WAY,
		PronamicMethod::MULTIBANCO              => MollieMethod::MULTIBANCO,
		PronamicMethod::MYBANK                  => MollieMethod::MYBANK,
		PronamicMethod::PAY_BY_BANK             => MollieMethod::PAY_BY_BANK,
		PronamicMethod::PAYCONIQ                => MollieMethod::PAYCONIQ,
		PronamicMethod::PAYPAL                  => MollieMethod::PAYPAL,
		PronamicMethod::PAYSAFECARD             => MollieMethod::PAYSAFECARD,
		PronamicMethod::POSTEPAY                => MollieMethod::CREDITCARD,
		PronamicMethod::PRZELEWY24              => MollieMethod::PRZELEWY24,
		PronamicMethod::SATISPAY                => MollieMethod::SATISPAY,
		PronamicMethod::BANK_TRANSFER           => MollieMethod::BANKTRANSFER,
		PronamicMethod::DIRECT_DEBIT            => MollieMethod::DIRECT_DEBIT,
		PronamicMethod::DIRECT_DEBIT_BANCONTACT => MollieMethod::DIRECT_DEBIT,
		PronamicMethod::DIRECT_DEBIT_IDEAL      => MollieMethod::DIRECT_DEBIT,
		PronamicMethod::RIVERTY                 => MollieMethod::RIVERTY,
		PronamicMethod::SWISH                   => MollieMethod::SWISH,
		PronamicMethod::TRUSTLY                 => MollieMethod::TRUSTLY,
		PronamicMethod::TWINT                   => MollieMethod::TWINT,
		PronamicMethod::VOUCHERS                => MollieMethod::VOUCHERS,
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
	public static function transform_wp_to_mollie( $payment_method, mixed $fallback = null ) {
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

	/**
	 * Transform Mollie method to Pronamic payment method(s).
	 *
	 * @param string $method Mollie method.
	 * @return array<string>
	 */
	public function from_mollie_to_pronamic( $method ) {
		return \array_keys(
			\array_filter(
				self::$map,
				fn( $value ) => $value === $method
			)
		);
	}
}
