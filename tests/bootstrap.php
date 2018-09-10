<?php
/**
 * Bootstrap tests
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

/**
 * Autoload.
 */
require_once __DIR__ . '/../vendor/autoload.php';

require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

/**
 * Manually load plugin.
 */
function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../../../../pronamic-ideal.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
