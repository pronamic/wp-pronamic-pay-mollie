<?php
/**
 * Mollie methods test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods;

/**
 * Title: Mollie methods test
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class MethodsTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @param string      $payment_method WordPress Pay payment method.
	 * @param string      $expected       Expected Mollie method.
	 * @param string|null $default        Default payment method.
	 *
	 * @dataProvider method_matrix_provider
	 */
	public function test_transform( $payment_method, $expected, $default = null ) {
		$mollie_method = Methods::transform( $payment_method, $default );

		$this->assertEquals( $expected, $mollie_method );
	}

	/**
	 * Method data provider.
	 *
	 * @return array
	 */
	public function method_matrix_provider() {
		return array(
			array( PaymentMethods::BANCONTACT, Methods::BANCONTACT ),
			array( PaymentMethods::BANK_TRANSFER, Methods::BANKTRANSFER ),
			array( PaymentMethods::CREDIT_CARD, Methods::CREDITCARD ),
			array( PaymentMethods::DIRECT_DEBIT, Methods::DIRECT_DEBIT ),
			array( PaymentMethods::DIRECT_DEBIT_IDEAL, Methods::DIRECT_DEBIT ),
			array( PaymentMethods::SOFORT, Methods::SOFORT ),
			array( PaymentMethods::IDEAL, Methods::IDEAL ),
			array( PaymentMethods::KBC, Methods::KBC ),
			array( PaymentMethods::BELFIUS, Methods::BELFIUS ),
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
	 * @param string $payment_method Mollie payment method.
	 * @param string $expected       Expected value.
	 *
	 * @dataProvider transform_gateway_method_matrix_provider
	 */
	public function test_transform_gateway_method( $payment_method, $expected ) {
		$wp_method = Methods::transform_gateway_method( $payment_method );

		$this->assertEquals( $expected, $wp_method );
	}

	/**
	 * Transform gateway method data provider.
	 *
	 * @return array
	 */
	public function transform_gateway_method_matrix_provider() {
		return array(
			array( Methods::BANCONTACT, \Pronamic\WordPress\Pay\Core\PaymentMethods::BANCONTACT ),
			array( Methods::BANKTRANSFER, \Pronamic\WordPress\Pay\Core\PaymentMethods::BANK_TRANSFER ),
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
