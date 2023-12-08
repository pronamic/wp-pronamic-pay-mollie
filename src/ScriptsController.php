<?php
/**
 * Scripts controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Scripts controller class
 */
class ScriptsController {
	/**
	 * Instance of this class.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return self A single instance of this class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		if ( \has_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] ) ) {
			return;
		}

		\add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		\add_action( 'wp_print_scripts', [ $this, 'print_scripts' ] );
	}

	/**
	 * Enqueue scripts.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
	 * @return void
	 */
	public function enqueue_scripts() {
		/**
		 * Mollie.js.
		 * 
		 * @link https://docs.mollie.com/reference/mollie-js/overview
		 */
		\wp_register_script(
			'mollie.js',
			'https://js.mollie.com/v1/mollie.js',
			[],
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is part of URL.
			null,
			true
		);

		/**
		 * Card field style.
		 * 
		 * @link https://github.com/mollie/components-examples
		 */
		$file = '../assets/dist/card-field.css';

		\wp_register_style(
			'pronamic-pay-mollie-card-field',
			\plugins_url( $file, __FILE__ ),
			[],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
		);

		/**
		 * WooCommerce legacy checkout script.
		 */
		$file = '../assets/dist/wc-legacy-checkout.js';

		\wp_register_script(
			'pronamic-pay-mollie-wc-legacy-checkout',
			\plugins_url( $file, __FILE__ ),
			[
				'jquery',
				'mollie.js',
				'wc-checkout',
			],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
			true
		);
	}

	/**
	 * Print scripts.
	 * 
	 * @link https://developer.wordpress.org/reference/functions/wp_print_scripts/
	 * @return void
	 */
	public function print_scripts() {
		/**
		 * WooCommerce legacy checkout.
		 * 
		 * @link https://github.com/woocommerce/woocommerce/blob/8.3.0/plugins/woocommerce/includes/class-wc-frontend-scripts.php#L392-L394
		 * @link https://github.com/woocommerce/woocommerce/blob/8.3.0/plugins/woocommerce/client/legacy/js/frontend/checkout.js
		 */
		if ( \wp_script_is( 'wc-checkout' ) ) {
			\wp_enqueue_style( 'pronamic-pay-mollie-card-field' );
			\wp_enqueue_script( 'pronamic-pay-mollie-wc-legacy-checkout' );
		}
	}
}
