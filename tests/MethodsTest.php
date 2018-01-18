<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie methods test
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.11
 * @since 1.0.0
 */
class MethodsTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @dataProvider method_matrix_provider
	 */
	public function test_transform( $payment_method, $expected ) {
		$mollie_method = Methods::transform( $payment_method );

		$this->assertEquals( $expected, $mollie_method );
	}

	public function method_matrix_provider() {
		return array(
			array( \Pronamic_WP_Pay_PaymentMethods::BANCONTACT, Methods::MISTERCASH ),
			array( \Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER, Methods::BANKTRANSFER ),
			array( \Pronamic_WP_Pay_PaymentMethods::BITCOIN, Methods::BITCOIN ),
			array( \Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD, Methods::CREDITCARD ),
			array( \Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT, Methods::DIRECT_DEBIT ),
			array( \Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL, Methods::DIRECT_DEBIT ),
			array( \Pronamic_WP_Pay_PaymentMethods::MISTER_CASH, Methods::MISTERCASH ),
			array( \Pronamic_WP_Pay_PaymentMethods::SOFORT, Methods::SOFORT ),
			array( \Pronamic_WP_Pay_PaymentMethods::IDEAL, Methods::IDEAL ),
			array( \Pronamic_WP_Pay_PaymentMethods::KBC, Methods::KBC ),
			array( \Pronamic_WP_Pay_PaymentMethods::BELFIUS, Methods::BELFIUS ),
			array( 'not existing payment method', null ),
			array( null, null ),
			array( 0, null ),
			array( false, null ),
			array( new \stdClass(), null ),
		);
	}
}
