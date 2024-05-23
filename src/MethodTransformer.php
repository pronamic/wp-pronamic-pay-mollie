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
		PronamicMethod::APPLE_PAY               => MollieMethod::APPLE_PAY,
		PronamicMethod::BANCONTACT              => MollieMethod::BANCONTACT,
		PronamicMethod::BANK_TRANSFER           => MollieMethod::BANKTRANSFER,
		PronamicMethod::BILLIE                  => MollieMethod::BILLIE,
		PronamicMethod::BLIK                    => MollieMethod::BLIK,
		PronamicMethod::CARD                    => MollieMethod::CREDITCARD,
		PronamicMethod::CREDIT_CARD             => MollieMethod::CREDITCARD,
		PronamicMethod::DIRECT_DEBIT            => MollieMethod::DIRECT_DEBIT,
		PronamicMethod::DIRECT_DEBIT_BANCONTACT => MollieMethod::DIRECT_DEBIT,
		PronamicMethod::DIRECT_DEBIT_IDEAL      => MollieMethod::DIRECT_DEBIT,
		PronamicMethod::DIRECT_DEBIT_SOFORT     => MollieMethod::DIRECT_DEBIT,
		PronamicMethod::EPS                     => MollieMethod::EPS,
		PronamicMethod::GIROPAY                 => MollieMethod::GIROPAY,
		PronamicMethod::KLARNA_PAY_LATER        => MollieMethod::KLARNA_PAY_LATER,
		PronamicMethod::KLARNA_PAY_NOW          => MollieMethod::KLARNA_PAY_NOW,
		PronamicMethod::KLARNA_PAY_OVER_TIME    => MollieMethod::KLARNA_SLICE_IT,
		PronamicMethod::MYBANK                  => MollieMethod::MYBANK,
		PronamicMethod::PAYPAL                  => MollieMethod::PAYPAL,
		PronamicMethod::PRZELEWY24              => MollieMethod::PRZELEWY24,
		PronamicMethod::SOFORT                  => MollieMethod::SOFORT,
		PronamicMethod::IDEAL                   => MollieMethod::IDEAL,
		PronamicMethod::IN3                     => MollieMethod::IN3,
		PronamicMethod::KBC                     => MollieMethod::KBC,
		PronamicMethod::BELFIUS                 => MollieMethod::BELFIUS,
		PronamicMethod::TWINT                   => MollieMethod::TWINT,
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
				function ( $value ) use ( $method ) {
					return ( $value === $method );
				}
			)
		);
	}
}
