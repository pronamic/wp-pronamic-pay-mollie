<?php
/**
 * Mollie listener test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use WP_UnitTestCase;

/**
 * Listener test.
 *
 * @author Reüel van der Steege
 * @version 2.0.5
 */
class ListenerTest extends WP_UnitTestCase {
	/**
	 * Test listen.
	 */
	public function test_listen_processing() {
		$listener = new Listener();

		// Test not processing.
		$this->assertSame( null, $listener->listen() );
	}
}
