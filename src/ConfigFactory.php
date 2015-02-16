<?php

/**
 * Title: Mollie config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_ConfigFactory extends Pronamic_WP_Pay_GatewayConfigFactory {
	public function get_config( $post_id ) {
		$config = new Pronamic_WP_Pay_Gateways_Mollie_Config();

		$config->api_key  = get_post_meta( $post_id, '_pronamic_gateway_mollie_api_key', true );

		return $config;
	}
}
