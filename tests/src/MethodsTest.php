<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie methods test
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class MethodsTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @dataProvider method_matrix_provider
	 */
	public function test_transform( $payment_method, $expected, $default = null ) {
		$mollie_method = Methods::transform( $payment_method, $default );

		$this->assertEquals( $expected, $mollie_method );
	}

	public function method_matrix_provider() {
		return array(
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::BANCONTACT, Methods::BANCONTACT ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::BANK_TRANSFER, Methods::BANKTRANSFER ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::BITCOIN, Methods::BITCOIN ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::CREDIT_CARD, Methods::CREDITCARD ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::DIRECT_DEBIT, Methods::DIRECT_DEBIT ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::DIRECT_DEBIT_IDEAL, Methods::DIRECT_DEBIT ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::SOFORT, Methods::SOFORT ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::IDEAL, Methods::IDEAL ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::KBC, Methods::KBC ),
			array( \Pronamic\WordPress\Pay\Core\PaymentMethods::BELFIUS, Methods::BELFIUS ),
			array( 'not existing payment method', null ),
			array( 'not existing payment method', 'test', 'test' ),
			array( null, null ),
			array( 0, null ),
			array( false, null ),
			array( new \stdClass(), null ),
		);
	}

	/**
	 * Test transform gateway method.
	 *
	 * @dataProvider transform_gateway_method_matrix_provider
	 */
	public function test_transform_gateway_method( $payment_method, $expected ) {
		$wp_method = Methods::transform_gateway_method( $payment_method );

		$this->assertEquals( $expected, $wp_method );
	}

	public function transform_gateway_method_matrix_provider() {
		return array(
			array( Methods::BANCONTACT, \Pronamic\WordPress\Pay\Core\PaymentMethods::BANCONTACT ),
			array( Methods::BANKTRANSFER, \Pronamic\WordPress\Pay\Core\PaymentMethods::BANK_TRANSFER ),
			array( Methods::BITCOIN, \Pronamic\WordPress\Pay\Core\PaymentMethods::BITCOIN ),
			array( Methods::CREDITCARD, \Pronamic\WordPress\Pay\Core\PaymentMethods::CREDIT_CARD ),
			array( Methods::DIRECT_DEBIT, \Pronamic\WordPress\Pay\Core\PaymentMethods::DIRECT_DEBIT ),
			array( Methods::PAYPAL, \Pronamic\WordPress\Pay\Core\PaymentMethods::PAYPAL ),
			array( Methods::SOFORT, \Pronamic\WordPress\Pay\Core\PaymentMethods::SOFORT ),
			array( Methods::IDEAL, \Pronamic\WordPress\Pay\Core\PaymentMethods::IDEAL ),
			array( Methods::KBC, \Pronamic\WordPress\Pay\Core\PaymentMethods::KBC ),
			array( Methods::BELFIUS, \Pronamic\WordPress\Pay\Core\PaymentMethods::BELFIUS ),
			array( 'not existing payment method', null ),
			array( null, null ),
			array( 0, null ),
			array( false, null ),
			array( new \stdClass(), null ),
		);
	}
}
