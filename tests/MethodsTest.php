<?php

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
class Pronamic_WP_Pay_Mollie_MethodsTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @dataProvider method_matrix_provider
	 */
	public function test_transform( $payment_method, $expected ) {
		$mollie_method = Pronamic_WP_Pay_Mollie_Methods::transform( $payment_method );

		$this->assertEquals( $expected, $mollie_method );
	}

	public function method_matrix_provider() {
		return array(
			array( Pronamic_WP_Pay_PaymentMethods::BANCONTACT, Pronamic_WP_Pay_Mollie_Methods::MISTERCASH ),
			array( Pronamic_WP_Pay_PaymentMethods::BANK_TRANSFER, Pronamic_WP_Pay_Mollie_Methods::BANKTRANSFER ),
			array( Pronamic_WP_Pay_PaymentMethods::BITCOIN, Pronamic_WP_Pay_Mollie_Methods::BITCOIN ),
			array( Pronamic_WP_Pay_PaymentMethods::CREDIT_CARD, Pronamic_WP_Pay_Mollie_Methods::CREDITCARD ),
			array( Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT, Pronamic_WP_Pay_Mollie_Methods::DIRECT_DEBIT ),
			array( Pronamic_WP_Pay_PaymentMethods::DIRECT_DEBIT_IDEAL, Pronamic_WP_Pay_Mollie_Methods::DIRECT_DEBIT ),
			array( Pronamic_WP_Pay_PaymentMethods::MISTER_CASH, Pronamic_WP_Pay_Mollie_Methods::MISTERCASH ),
			array( Pronamic_WP_Pay_PaymentMethods::SOFORT, Pronamic_WP_Pay_Mollie_Methods::SOFORT ),
			array( Pronamic_WP_Pay_PaymentMethods::IDEAL, Pronamic_WP_Pay_Mollie_Methods::IDEAL ),
			array( Pronamic_WP_Pay_PaymentMethods::KBC, Pronamic_WP_Pay_Mollie_Methods::KBC ),
			array( Pronamic_WP_Pay_PaymentMethods::BELFIUS, Pronamic_WP_Pay_Mollie_Methods::BELFIUS ),
			array( 'not existing payment method', null ),
			array( null, null ),
			array( 0, null ),
			array( false, null ),
			array( new stdClass(), null ),
		);
	}
}
