<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.15
 * @since 1.0.0
 */
class ConfigFactory extends \Pronamic_WP_Pay_GatewayConfigFactory {
	public function get_config( $post_id ) {
		$config = new Config();

		$config->api_key = get_post_meta( $post_id, '_pronamic_gateway_mollie_api_key', true );
		$config->mode    = get_post_meta( $post_id, '_pronamic_gateway_mode', true );

		return $config;
	}
}
