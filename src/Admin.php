<?php
/**
 * Mollie admin.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie admin
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class Admin {
	/**
	 * Construct and initialize Mollie admin.
	 */
	public function __construct() {
		/**
		 * Initialize.
		 */
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		/**
		 * Menu.
		 *
		 * @link https://metabox.io/create-hidden-admin-page/
		 */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		/**
		 * Meta boxes.
		 */
		add_action( 'add_meta_boxes', array( $this, 'add_payment_meta_box' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_subscription_meta_box' ), 10, 2 );
	}

	/**
	 * Admin init.
	 *
	 * @return void
	 */
	public function admin_init() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		$function = array( __CLASS__, 'user_profile' );

		if ( ! has_action( 'show_user_profile', $function ) ) {
			add_action( 'show_user_profile', $function );
		}

		if ( ! has_action( 'edit_user_profile', $function ) ) {
			add_action( 'edit_user_profile', $function );
		}
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
	 *
	 * @return void
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
			__( 'Mollie Profiles', 'pronamic_ideal' ),
			__( 'Profiles', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_mollie_profiles',
			array( $this, 'page_mollie_profiles' )
		);

		add_submenu_page(
			'pronamic_pay_mollie',
			__( 'Mollie Customers', 'pronamic_ideal' ),
			__( 'Customers', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_mollie_customers',
			array( $this, 'page_mollie_customers' )
		);

		add_submenu_page(
			'pronamic_pay_mollie',
			__( 'Mollie Payments', 'pronamic_ideal' ),
			__( 'Payments', 'pronamic_ideal' ),
			'manage_options',
			'pronamic_pay_mollie_payments',
			array( $this, 'page_mollie_payments' )
		);

		/**
		 * Remove menu page.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/plugin.php#L1708-L1729
		 * @link https://wordpress.stackexchange.com/questions/135692/creating-a-wordpress-admin-page-without-a-menu-for-a-plugin
		 * @link https://stackoverflow.com/questions/3902760/how-do-you-add-a-wordpress-admin-page-without-adding-it-to-the-menu
		 */
		remove_menu_page( 'pronamic_pay_mollie' );
	}

	/**
	 * Page Mollie.
	 *
	 * @return void
	 */
	public function page_mollie() {
		include __DIR__ . '/../views/page-mollie.php';
	}

	/**
	 * Page Mollie profiles.
	 *
	 * @return void
	 */
	public function page_mollie_profiles() {
		if ( filter_has_var( INPUT_GET, 'id' ) ) {
			include __DIR__ . '/../views/page-profile.php';

			return;
		}

		include __DIR__ . '/../views/page-profiles.php';
	}

	/**
	 * Page Mollie customers.
	 *
	 * @return void
	 */
	public function page_mollie_customers() {
		if ( filter_has_var( INPUT_GET, 'id' ) ) {
			include __DIR__ . '/../views/page-customer.php';

			return;
		}

		include __DIR__ . '/../views/page-customers.php';
	}

	/**
	 * Page Mollie payments.
	 *
	 * @return void
	 */
	public function page_mollie_payments() {
		if ( filter_has_var( INPUT_GET, 'id' ) ) {
			include __DIR__ . '/../views/page-payment.php';

			return;
		}

		include __DIR__ . '/../views/page-payments.php';
	}

	/**
	 * User profile.
	 *
	 * @since 1.1.6
	 * @link https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
	 * @param \WP_User $user WordPress user.
	 * @return void
	 */
	public static function user_profile( $user ) {
		include __DIR__ . '/../views/user-profile.php';
	}

	/**
	 * Add payment meta box.
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/meta-boxes.php#L1541-L1549
	 * @param string   $post_type Post type.
	 * @param \WP_Post $post      Post object.
	 * @return void
	 */
	public function add_payment_meta_box( $post_type, $post ) {
		if ( 'pronamic_payment' !== $post_type ) {
			return;
		}

		$transaction_id = \get_post_meta( $post->ID, '_pronamic_payment_transaction_id', true );

		if ( 'tr_' !== \substr( $transaction_id, 0, 3 ) ) {
			return;
		}

		\add_meta_box(
			'pronamic_pay_mollie_payment',
			\__( 'Mollie', 'pronamic_ideal' ),
			function( $post ) {
				include __DIR__ . '/../views/meta-box-payment.php';
			},
			$post_type,
			'side'
		);
	}

	/**
	 * Add subscription meta box.
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/meta-boxes.php#L1541-L1549
	 * @param string   $post_type Post type.
	 * @param \WP_Post $post      Post object.
	 * @return void
	 */
	public function add_subscription_meta_box( $post_type, $post ) {
		if ( 'pronamic_pay_subscr' !== $post_type ) {
			return;
		}

		// Get subscription.
		$subscription = \get_pronamic_subscription( $post->ID );

		if ( null === $subscription ) {
			return;
		}

		// Get Mollie customer ID.
		$mollie_customer_id = $subscription->get_meta( 'mollie_customer_id' );

		if ( empty( $mollie_customer_id ) ) {
			return;
		}

		// Add meta box.
		\add_meta_box(
			'pronamic_pay_mollie_subscription',
			\__( 'Mollie', 'pronamic_ideal' ),
			function( $post ) {
				include __DIR__ . '/../views/meta-box-subscription.php';
			},
			$post_type,
			'side'
		);
	}
}
