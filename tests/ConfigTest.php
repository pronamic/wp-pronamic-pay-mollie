<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie config test
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.15
 * @since 1.0.0
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
