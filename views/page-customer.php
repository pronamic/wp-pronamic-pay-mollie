<?php
/**
 * Page customer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

$mollie_customer_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2><?php printf( __( 'Customer %s', 'pronamic_ideal' ), $mollie_customer_id ); ?></h2>
	
	<h3><?php esc_html_e( 'General', 'pronamic_ideal' ); ?></h3>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
				<td>
					<code><?php echo esc_html( $mollie_customer_id ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Link', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					$mollie_link = \sprintf(
						'https://www.mollie.com/dashboard/customers/%s',
						$mollie_customer_id
					);

					\printf(
						'<a href="%s">%s</a>',
						\esc_url( $mollie_link ),
						\esc_html( $mollie_link )
					);

					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Name', 'pronamic_ideal' ); ?></th>
				<td>
					
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Email', 'pronamic_ideal' ); ?></th>
				<td>
					
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php esc_html_e( 'Mandates', 'pronamic_ideal' ); ?></h3>

	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
				<th><?php esc_html_e( 'Mode', 'pronamic_ideal' ); ?></th>
				<th><?php esc_html_e( 'Status', 'pronamic_ideal' ); ?></th>
				<th><?php esc_html_e( 'Method', 'pronamic_ideal' ); ?></th>
			</tr>
		</thead>

		<tbody>
			
		</tbody>
	</table>

	<h3><?php esc_html_e( 'WordPress Users', 'pronamic_ideal' ); ?></h3>

	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
				<th><?php esc_html_e( 'Email', 'pronamic_ideal' ); ?></th>
				<th><?php esc_html_e( 'Display Name', 'pronamic_ideal' ); ?></th>
			</tr>
		</thead>

		<tbody>
			
		</tbody>
	</table>
</div>
