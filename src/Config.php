<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie config
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Config extends \Pronamic_WP_Pay_GatewayConfig {
	public $api_key;

	public function get_gateway_class() {
		return __NAMESPACE__ . '\Gateway';
	}
}
