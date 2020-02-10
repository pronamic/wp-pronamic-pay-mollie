<?php
/**
 * Upgrade 3.0.0
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Upgrades\Upgrade;

/**
 * Upgrade 3.0.0
 *
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
 */
class Upgrade300 extends Upgrade {
	/**
	 * Construct 3.0.0 upgrade.
	 */
	public function __construct() {
		parent::__construct( '3.0.0' );
	}

	/**
	 * Execute.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-includes/wp-db.php#L992-L1072
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/schema.php#L25-L344
	 * @link https://developer.wordpress.org/reference/functions/dbdelta/
	 * @link https://github.com/wp-premium/gravityforms/blob/2.4.16/includes/class-gf-upgrade.php#L518-L531
	 */
	public function execute() {
		global $wpdb;

		/**
		 * Requirements.
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * Other.
		 */
		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Queries.
		 */
		$queries = "
			CREATE TABLE $wpdb->pronamic_pay_mollie_organizations (
				id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				mollie_id VARCHAR( 16 ) NOT NULL,

				PRIMARY KEY  ( id ),
				UNIQUE KEY mollie_id ( mollie_id )
			) $charset_collate;

			CREATE TABLE $wpdb->pronamic_pay_mollie_customers (
				id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				mollie_id VARCHAR( 16 ) NOT NULL,
				organisation_id BIGINT( 20 ) NOT NULL,
				test_mode BOOL NOT NULL,
				email VARCHAR( 100 ) DEFAULT NULL,

				PRIMARY KEY  ( id ),
				UNIQUE KEY mollie_id ( mollie_id ),
				KEY organisation_id ( organisation_id ),
				KEY test_mode ( test_mode ),
				KEY email ( email )
			) $charset_collate;

			CREATE TABLE $wpdb->pronamic_pay_mollie_customer_users (
				id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				customer_id BIGINT( 20 ) UNSIGNED NOT NULL,
				user_id BIGINT( 20 ) UNSIGNED NOT NULL,

				PRIMARY KEY  ( id ),
				UNIQUE KEY customer_user ( customer_id, user_id )
			) $charset_collate;
		";

		/**
		 * Execute.
		 */
		\dbDelta( $queries );

		/**
		 * Foreign keys.
		 *
		 * @link https://core.trac.wordpress.org/ticket/19207
		 * @link https://dev.mysql.com/doc/refman/5.6/en/create-table-foreign-keys.html
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->pronamic_pay_mollie_customers 
			ADD FOREIGN KEY ( organisation_id )
			REFERENCES $wpdb->pronamic_pay_mollie_organizations ( id )
			ON DELETE RESTRICT
			ON UPDATE RESTRICT
			;
		" );

		$wpdb->query( "
			ALTER TABLE $wpdb->pronamic_pay_mollie_customer_users 
			ADD FOREIGN KEY ( customer_id )
			REFERENCES $wpdb->pronamic_pay_mollie_customers ( id )
			ON DELETE RESTRICT
			ON UPDATE RESTRICT
			;
		" );

		$wpdb->query( "
			ALTER TABLE $wpdb->pronamic_pay_mollie_customer_users 
			ADD FOREIGN KEY ( user_id )
			REFERENCES $wpdb->users ( id )
			ON DELETE CASCADE
			ON UPDATE CASCADE
			;
		" );
	}
}
