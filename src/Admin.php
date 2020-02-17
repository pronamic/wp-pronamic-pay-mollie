<?php
/**
 * Mollie admin.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie admin
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class Admin {
	/**
	 * Construct and intialize Mollie admin.
	 */
	public function __construct() {
		$function = array( __CLASS__, 'user_profile' );

		if ( ! has_action( 'show_user_profile', $function ) ) {
			add_action( 'show_user_profile', $function );
		}

		if ( ! has_action( 'edit_user_profile', $function ) ) {
			add_action( 'edit_user_profile', $function );
		}

		/**
		 * Menu.
		 *
		 * @link https://metabox.io/create-hidden-admin-page/
		 */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Get menu icon URL.
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_menu_page/
	 * @return string
	 * @throws \Exception Throws exception when retrieving menu icon fails.
	 */
	private function get_menu_icon_url() {
		/**
		 * Icon URL.
		 *
		 * Pass a base64-encoded SVG using a data URI, which will be colored to match the color scheme.
		 * This should begin with 'data:image/svg+xml;base64,'.
		 *
		 * We use a SVG image with default fill color #A0A5AA from the default admin color scheme:
		 * https://github.com/WordPress/WordPress/blob/5.2/wp-includes/general-template.php#L4135-L4145
		 *
		 * The advantage of this is that users with the default admin color scheme do not see the repaint:
		 * https://github.com/WordPress/WordPress/blob/5.2/wp-admin/js/svg-painter.js
		 *
		 * @link https://developer.wordpress.org/reference/functions/add_menu_page/
		 */
		$file = __DIR__ . '/../images/dist/mollie-wp-admin-fresh-base.svgo-min.svg';

		if ( ! \is_readable( $file ) ) {
			throw new \Exception(
				\sprintf(
					'Could not read WordPress admin menu icon from file: %s.',
					$file
				)
			);
		}

		$svg = \file_get_contents( $file, true );

		if ( false === $svg ) {
			throw new \Exception(
				\sprintf(
					'Could not read WordPress admin menu icon from file: %s.',
					$file
				)
			);
		}

		$icon_url = \sprintf(
			'data:image/svg+xml;base64,%s',
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			\base64_encode( $svg )
		);

		return $icon_url;
	}

	/**
	 * Admin menu.
	 */
	public function admin_menu() {
		try {
			$menu_icon_url = $this->get_menu_icon_url();
		} catch ( \Exception $e ) {
			// @todo Log.

			/**
			 * If retrieving the menu icon URL fails we will
			 * fallback to the WordPress money dashicon.
			 *
			 * @link https://developer.wordpress.org/resource/dashicons/#money
			 */
			$menu_icon_url = 'dashicons-money';
		}

		add_menu_page(
			__( 'Mollie', 'pronamic_ideal' ),
			__( 'Mollie', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_mollie',
			array( $this, 'page_mollie' ),
			$menu_icon_url
		);

		add_submenu_page(
			'pronamic_pay_mollie',
			__( 'Mollie Customers', 'pronamic_ideal' ),
			__( 'Customers', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_mollie_customers',
			array( $this, 'page_mollie_customers' )
		);
	}

	/**
	 * Page Mollie.
	 */
	public function page_mollie() {

	}

	/**
	 * Page Mollie customers.
	 */
	public function page_mollie_customers() {
		if ( filter_has_var( INPUT_GET, 'id' ) ) {
			include __DIR__ . '/../views/page-customer.php';

			return;
		}

		include __DIR__ . '/../views/page-customers.php';
	}

	/**
	 * User profile.
	 *
	 * @since 1.1.6
	 * @link https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
	 * @param WP_User $user WordPress user.
	 * @return void
	 */
	public static function user_profile( $user ) {
		include __DIR__ . '/../views/html-admin-user-profile.php';
	}
}
