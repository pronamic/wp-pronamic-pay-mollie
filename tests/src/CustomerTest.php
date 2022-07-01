<?php
/**
 * Mollie customer test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie customer tests
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class CustomerTest extends TestCase {
	/**
	 * Test amount setters and getters.
	 */
	public function test_setters_and_getters() {
		$customer = new Customer( 'cst_1234567890' );

		$customer->set_mode( 'test' );
		$customer->set_name( 'John Doe' );
		$customer->set_email( 'john@example.com' );
		$customer->set_locale( 'nl_NL' );

		$this->assertInstanceOf( Customer::class, $customer );

		$this->assertEquals( 'cst_1234567890', $customer->get_id() );
		$this->assertEquals( 'test', $customer->get_mode() );
		$this->assertEquals( 'John Doe', $customer->get_name() );
		$this->assertEquals( 'john@example.com', $customer->get_email() );
		$this->assertEquals( 'nl_NL', $customer->get_locale() );
	}
}
