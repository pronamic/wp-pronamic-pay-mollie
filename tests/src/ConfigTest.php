<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie config test
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test config.
	 */
	public function test_config() {
		$config = new Config();

		$config->api_key = 'test';

		$this->assertEquals( 'test', $config->api_key );
		$this->assertEquals( __NAMESPACE__ . '\Gateway', $config->get_gateway_class() );
	}
}
