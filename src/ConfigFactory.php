<?php
/**
 * Mollie config factory.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\GatewayConfigFactory;

/**
 * Title: Mollie config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class ConfigFactory extends GatewayConfigFactory {
	public function get_config( $post_id ) {
		$config = new Config();

		$config->api_key = get_post_meta( $post_id, '_pronamic_gateway_mollie_api_key', true );
		$config->mode    = get_post_meta( $post_id, '_pronamic_gateway_mode', true );

		return $config;
	}
}
