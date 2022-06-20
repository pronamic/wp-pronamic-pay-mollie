<?php
/**
 * Mollie amount transformer test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Money\Money;

/**
 * Title: Mollie amount transformer tests
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.1.0
 */
class AmountTransformerTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @param Money  $money             Pronamic money.
	 * @param string $expected_currency Expected currency.
	 * @param string $expected_value    Expected value.
	 * @dataProvider amount_provider
	 */
	public function test_transform( $money, $expected_currency, $expected_value ) {
		$amount = AmountTransformer::transform( $money );

		$this->assertEquals( $expected_currency, $amount->get_currency() );
		$this->assertEquals( $expected_value, $amount->get_value() );
	}

	/**
	 * Amount data provider.
	 *
	 * @return array
	 */
	public function amount_provider() {
		return [
			[ new Money( 100, 'EUR' ), 'EUR', '100.00' ],
			[ new Money( 5, 'BHD' ), 'BHD', '5.000' ],
		];
	}
}
