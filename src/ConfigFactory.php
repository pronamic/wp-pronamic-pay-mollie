<?php
/**
 * Mollie config factory.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\GatewayConfigFactory;

/**
 * Title: Mollie config factory
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class ConfigFactory extends GatewayConfigFactory {
	/**
	 * Get configuration by post ID.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return Config
	 */
	public function get_config( $post_id ) {
		$config = new Config();

		$config->post_id = intval( $post_id );
		$config->api_key = $this->get_meta( $post_id, '_pronamic_gateway_mollie_api_key' );
		$config->mode    = $this->get_meta( $post_id, '_pronamic_gateway_mode' );

		return $config;
	}
}
