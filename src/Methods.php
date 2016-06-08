<?php

/**
 * Title: Mollie methods
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.6
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Mollie_Methods {
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

	/////////////////////////////////////////////////

	/**
	 * Transform WordPress payment method to Mollie method.
	 *
	 * @since 1.1.6
	 * @param string $method
	 * @return string
	 */
	public static function transform( $payment_method ) {
		switch ( $payment_method ) {
			case Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER :
				return Pronamic_WP_Pay_Mollie_Methods::BANKTRANSFER;
			case Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD :
				return Pronamic_WP_Pay_Mollie_Methods::CREDITCARD;
			case Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT :
				return Pronamic_WP_Pay_Mollie_Methods::DIRECT_DEBIT;
			case Pronamic_WP_Pay_PaymentMethods::MISTER_CASH :
				return Pronamic_WP_Pay_Mollie_Methods::MISTERCASH;
			case Pronamic_WP_Pay_PaymentMethods::SOFORT :
				return Pronamic_WP_Pay_Mollie_Methods::SOFORT;
			case Pronamic_WP_Pay_PaymentMethods::IDEAL :
				return Pronamic_WP_Pay_Mollie_Methods::IDEAL;
			default :
				return null;
		}
	}
}
