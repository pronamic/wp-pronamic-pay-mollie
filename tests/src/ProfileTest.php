<?php
/**
 * Mollie profile test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie profile tests
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class ProfileTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Test amount setters and getters.
	 */
	public function test_setters_and_getters() {
		$profile = new Profile();

		$profile->set_id( 'pfl_1234567890' );
		$profile->set_mode( 'test' );
		$profile->set_name( 'John Doe' );
		$profile->set_email( 'john@example.com' );

		$this->assertInstanceOf( Profile::class, $profile );

		$this->assertEquals( 'pfl_1234567890', $profile->get_id() );
		$this->assertEquals( 'test', $profile->get_mode() );
		$this->assertEquals( 'John Doe', $profile->get_name() );
		$this->assertEquals( 'john@example.com', $profile->get_email() );
	}
}
