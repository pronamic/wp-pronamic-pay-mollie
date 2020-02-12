<?php
/**
 * User profile.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 *
 * @since 1.1.6
 * @link  https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

global $wpdb;

$query = $wpdb->prepare( "
	SELECT
		mollie_customer.mollie_id,
		mollie_customer.test_mode
	FROM
		$wpdb->pronamic_pay_mollie_customer_users AS mollie_customer_user
			INNER JOIN
		$wpdb->pronamic_pay_mollie_customers AS mollie_customer
				ON mollie_customer_user.customer_id = mollie_customer.id
	WHERE
		mollie_customer_user.user_id = %d
	;
	",
	$user->ID
);

$customers = $wpdb->get_results( $query );

if ( empty( $customers ) ) {
	return;
}

?>
<h2><?php esc_html_e( 'Mollie', 'pronamic_ideal' ); ?></h2>

<table class="widefat striped">
	<thead>
		<tr>
			<th scope="col"><?php esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Test', 'pronamic_ideal' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Link', 'pronamic_ideal' ); ?></th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ( $customers as $customer ) : ?>

			<tr>
				<td>
					<code><?php echo esc_html( $customer->mollie_id ); ?>
				</td>
				<td>
					<?php $customer->test_mode ? esc_html_e( 'Yes', 'pronamic_ideal' ) : esc_html_e( 'No', 'pronamic_ideal' ); ?>
				</td>
				<td>
					<?php

					$mollie_link = sprintf(
						'https://www.mollie.com/dashboard/customers/%s',
						$customer->mollie_id
					);

					printf(
						'<a href="%s">%s</a>',
						esc_url( $mollie_link ),
						esc_html( $mollie_link )
					);

					?>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>
