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

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

$customer_query = new CustomerQuery(
	array(
		'user_id' => $user->ID,
	)
);

$customers = $customer_query->get_customers();

if ( empty( $customers ) ) {
	return;
}

?>
<h2><?php esc_html_e( 'Mollie', 'pronamic_ideal' ); ?></h2>

<style type="text/css">
	.form-table .pronamic-pay-mollie-customers-table th,
	.form-table .pronamic-pay-mollie-customers-table td {
		padding: 8px 10px;
	}
</style>

<table class="form-table" id="fieldset-billing">
	<tbody>
		<tr>
			<th>
				<?php echo esc_html( _x( 'Customers', 'mollie', 'pronamic_ideal' ) ); ?>
			</th>
			<td>
				<table class="widefat striped pronamic-pay-mollie-customers-table">
					<thead>
						<tr>
							<th scope="col"><?php \esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
							<th scope="col"><?php \esc_html_e( 'Test', 'pronamic_ideal' ); ?></th>
							<th scope="col"><?php \esc_html_e( 'Name', 'pronamic_ideal' ); ?></th>
							<th scope="col"><?php \esc_html_e( 'Email', 'pronamic_ideal' ); ?></th>
						</tr>
					</thead>

					<tbody>

						<?php foreach ( $customers as $customer ) : ?>

							<tr>
								<td>
									<?php

									$url = \add_query_arg(
										array(
											'page' => 'pronamic_pay_mollie_customers',
											'id'   => $customer->mollie_id,
										),
										\admin_url( 'admin.php' )
									);

									\printf(
										'<a href="%s"><code>%s</code></a>',
										\esc_url( $url ),
										\esc_html( $customer->mollie_id )
									);

									?>
								</td>
								<td>
									<?php $customer->test_mode ? \esc_html_e( 'Yes', 'pronamic_ideal' ) : \esc_html_e( 'No', 'pronamic_ideal' ); ?>
								</td>
								<td>
									<?php echo empty( $customer->name ) ? '—' : \esc_html( $customer->name ); ?>
								</td>
								<td>
									<?php

									empty( $customer->email ) ? print( '—' ): \printf(
										'<a href="%s">%s</a>',
										esc_attr( 'mailto:' . $customer->email ),
										esc_html( $customer->email )
									);

									?>
								</td>
							</tr>

						<?php endforeach; ?>

					</tbody>
				</table>

				<p class="description">
					Mollie biedt de mogelijkheid om betalers als klant ('customer') te registreren binnen het Mollie-betaalplatform. Deze functionaliteit onthoudt de betaalvoorkeuren van een klant om een volgende betaling sneller te laten verlopen. De Mollie-klanten kunnen gekoppeld zijn aan WordPress-gebruikers. Hieronder is een lijst van Mollie-klanten te zien die gekoppeld zijn aan deze WordPress-gebruiker. Voor abonnementen kunnen de machtigingen bij een Mollie-klant gebruikt worden voor terugkerende betalingen.
				</p>
			</td>
		</tr>
	</tbody>
</table>
