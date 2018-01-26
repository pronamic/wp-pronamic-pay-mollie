<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Mollie methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.11
 * @since 1.0.0
 */
class Methods {
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
	 * Constant for the Mister Cash method.
	 *
	 * @var string
	 */
	const MISTERCASH = 'mistercash';

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
	 * @see https://www.mollie.com/en/giftcards
	 * @since 1.1.10
	 * @var string
	 */
	const PODIUMCADEAUKAART = 'podiumcadeaukaart';

	/**
	 * Constant for the KBC/CBC Payment Button method.
	 *
	 * @see https://www.mollie.com/en/kbccbc
	 * @since 1.1.10
	 * @var string
	 */
	const KBC = 'kbc';

	/**
	 * Constant for the Belfius Direct Net method.
	 *
	 * @see https://www.mollie.com/en/belfiusdirectnet
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
		PaymentMethods::BANK_TRANSFER           => Methods::BANKTRANSFER,
		PaymentMethods::BITCOIN                 => Methods::BITCOIN,
		PaymentMethods::CREDIT_CARD             => Methods::CREDITCARD,
		PaymentMethods::DIRECT_DEBIT            => Methods::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_BANCONTACT => Methods::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_IDEAL      => Methods::DIRECT_DEBIT,
		PaymentMethods::DIRECT_DEBIT_SOFORT     => Methods::DIRECT_DEBIT,
		PaymentMethods::BANCONTACT              => Methods::MISTERCASH,
		PaymentMethods::MISTER_CASH             => Methods::MISTERCASH,
		PaymentMethods::PAYPAL                  => Methods::PAYPAL,
		PaymentMethods::SOFORT                  => Methods::SOFORT,
		PaymentMethods::IDEAL                   => Methods::IDEAL,
		PaymentMethods::KBC                     => Methods::KBC,
		PaymentMethods::BELFIUS                 => Methods::BELFIUS,
	);

	/**
	 * Transform WordPress payment method to Mollie method.
	 *
	 * @since 1.1.6
	 * @param string $method
	 * @return string
	 */
	public static function transform( $payment_method ) {
		if ( ! is_scalar( $payment_method ) ) {
			return null;
		}

		if ( isset( self::$map[ $payment_method ] ) ) {
			return self::$map[ $payment_method ];
		}

		return null;
	}
}
