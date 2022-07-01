<?php
/**
 * Mollie methods test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie methods test
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class MethodsTest extends TestCase {
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
		return [
			[ PaymentMethods::BANCONTACT, Methods::BANCONTACT ],
			[ PaymentMethods::BANK_TRANSFER, Methods::BANKTRANSFER ],
			[ PaymentMethods::CREDIT_CARD, Methods::CREDITCARD ],
			[ PaymentMethods::DIRECT_DEBIT, Methods::DIRECT_DEBIT ],
			[ PaymentMethods::DIRECT_DEBIT_IDEAL, Methods::DIRECT_DEBIT ],
			[ PaymentMethods::SOFORT, Methods::SOFORT ],
			[ PaymentMethods::IDEAL, Methods::IDEAL ],
			[ PaymentMethods::KBC, Methods::KBC ],
			[ PaymentMethods::BELFIUS, Methods::BELFIUS ],
			[ 'not existing payment method', null ],
			[ 'not existing payment method', 'test', 'test' ],
			[ null, null ],
			[ 0, null ],
			[ false, null ],
			[ new \stdClass(), null ],
		];
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
		return [
			[ Methods::BANCONTACT, \Pronamic\WordPress\Pay\Core\PaymentMethods::BANCONTACT ],
			[ Methods::BANKTRANSFER, \Pronamic\WordPress\Pay\Core\PaymentMethods::BANK_TRANSFER ],
			[ Methods::CREDITCARD, \Pronamic\WordPress\Pay\Core\PaymentMethods::CREDIT_CARD ],
			[ Methods::DIRECT_DEBIT, \Pronamic\WordPress\Pay\Core\PaymentMethods::DIRECT_DEBIT ],
			[ Methods::PAYPAL, \Pronamic\WordPress\Pay\Core\PaymentMethods::PAYPAL ],
			[ Methods::SOFORT, \Pronamic\WordPress\Pay\Core\PaymentMethods::SOFORT ],
			[ Methods::IDEAL, \Pronamic\WordPress\Pay\Core\PaymentMethods::IDEAL ],
			[ Methods::KBC, \Pronamic\WordPress\Pay\Core\PaymentMethods::KBC ],
			[ Methods::BELFIUS, \Pronamic\WordPress\Pay\Core\PaymentMethods::BELFIUS ],
			[ 'not existing payment method', null ],
			[ null, null ],
			[ 0, null ],
			[ false, null ],
			[ new \stdClass(), null ],
		];
	}
}
