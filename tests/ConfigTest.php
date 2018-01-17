<?php

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
class Pronamic_WP_Pay_Mollie_ConfigTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test config.
	 */
	public function test_config() {
		$config = new Pronamic_WP_Pay_Gateways_Mollie_Config();

		$config->api_key = 'test';

		$this->assertEquals( 'test', $config->api_key );
		$this->assertEquals( 'Pronamic_WP_Pay_Gateways_Mollie_Gateway', $config->get_gateway_class() );
	}
}
