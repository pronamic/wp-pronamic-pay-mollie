<?php
/**
 * Mollie config factory test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use WP_UnitTestCase;

/**
 * Config factory test.
 *
 * @author Reüel van der Steege
 * @version 2.0.5
 */
class ConfigFactoryTest extends WP_UnitTestCase {
	/**
	 * Test get config.
	 */
	public function test_get_config() {
		$factory = new ConfigFactory();

		$config = $factory->get_config( 1 );

		$this->assertInstanceOf( __NAMESPACE__ . '\Config', $config );
	}
}
