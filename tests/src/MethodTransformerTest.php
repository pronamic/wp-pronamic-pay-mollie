<?php
/**
 * Mollie methods test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Methods as MollieMethod;
use Pronamic\WordPress\Pay\Core\PaymentMethods as PronamicMethod;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie methods test
 * Description:
 * Copyright: 2005-2026 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class MethodTransformerTest extends TestCase {
	/**
	 * Test transform.
	 *
	 * @param string      $payment_method WordPress Pay payment method.
	 * @param string      $expected       Expected Mollie method.
	 * @param string|null $fallback       Default payment method.
	 *
	 * @dataProvider method_matrix_provider
	 */
	public function test_transform( $payment_method, $expected, $fallback = null ) {
		$transformer = new MethodTransformer();

		$mollie_method = $transformer->transform_wp_to_mollie( $payment_method, $fallback );

		$this->assertEquals( $expected, $mollie_method );
	}

	/**
	 * Method data provider.
	 *
	 * @return array
	 */
	public function method_matrix_provider() {
		return [
			[ PronamicMethod::BANCONTACT, MollieMethod::BANCONTACT ],
			[ PronamicMethod::BANK_TRANSFER, MollieMethod::BANKTRANSFER ],
			[ PronamicMethod::CREDIT_CARD, MollieMethod::CREDITCARD ],
			[ PronamicMethod::DIRECT_DEBIT, MollieMethod::DIRECT_DEBIT ],
			[ PronamicMethod::DIRECT_DEBIT_IDEAL, MollieMethod::DIRECT_DEBIT ],
			[ PronamicMethod::IDEAL, MollieMethod::IDEAL ],
			[ PronamicMethod::KBC, MollieMethod::KBC ],
			[ PronamicMethod::BELFIUS, MollieMethod::BELFIUS ],
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
		$transformer = new MethodTransformer();

		$wp_method = $transformer->transform_mollie_to_wp( $payment_method );

		$this->assertEquals( $expected, $wp_method );
	}

	/**
	 * Transform gateway method data provider.
	 *
	 * @return array
	 */
	public function transform_gateway_method_matrix_provider() {
		return [
			[ MollieMethod::BANCONTACT, PronamicMethod::BANCONTACT ],
			[ MollieMethod::BANKTRANSFER, PronamicMethod::BANK_TRANSFER ],
			[ MollieMethod::CREDITCARD, PronamicMethod::CARD ],
			[ MollieMethod::DIRECT_DEBIT, PronamicMethod::DIRECT_DEBIT ],
			[ MollieMethod::PAYPAL, PronamicMethod::PAYPAL ],
			[ MollieMethod::IDEAL, PronamicMethod::IDEAL ],
			[ MollieMethod::KBC, PronamicMethod::KBC ],
			[ MollieMethod::BELFIUS, PronamicMethod::BELFIUS ],
			[ 'not existing payment method', null ],
			[ null, null ],
			[ 0, null ],
			[ false, null ],
			[ new \stdClass(), null ],
		];
	}
}
