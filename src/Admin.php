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
	 * Admin menu.
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Mollie', 'pronamic_ideal' ),
			__( 'Mollie', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_mollie',
			array( $this, 'page_mollie' )
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
