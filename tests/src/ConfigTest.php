<?php
/**
 * Mollie config test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie config tests
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
 */
class ConfigTest extends TestCase {
	/**
	 * Test config.
	 */
	public function test_config() {
		$config = new Config();

		$config->api_key = 'test_dHar4XY7LxsDOtmnkVtjNVWXLSlXsM';

		$this->assertTrue( $config->is_test_mode() );

		$config->api_key = 'live_dHar4XY7LxsDOtmnkVtjNVWXLSlXsM';

		$this->assertFalse( $config->is_test_mode() );
	}
}
