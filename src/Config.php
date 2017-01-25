<?php

/**
 * Title: Mollie config
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Config extends Pronamic_WP_Pay_GatewayConfig {
	public $api_key;

	public function get_gateway_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_Gateway';
	}
}
