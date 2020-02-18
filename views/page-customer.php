<?php
/**
 * Page customer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

$mollie_customer_id = \filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );

global $wpdb;

$query = $wpdb->prepare(
	"
	SELECT
		mollie_customer.*,
		IF ( mollie_customer.test_mode, mollie_profile.api_key_test, mollie_profile.api_key_live ) AS api_key
	FROM
		$wpdb->pronamic_pay_mollie_customers AS mollie_customer
			INNER JOIN
		$wpdb->pronamic_pay_mollie_profiles AS mollie_profile
				ON mollie_customer.profile_id = mollie_profile.id
	WHERE
		mollie_customer.mollie_id = %s
	LIMIT
		1
	;
	",
	$mollie_customer_id
);

$data = $wpdb->get_row( $query );

$client = new Client( $data->api_key );

/**
 * Customer.
 *
 * @link https://docs.mollie.com/reference/v2/customers-api/get-customer
 */
$customer = $client->get_customer( $mollie_customer_id );

/**
 * Mandates.
 *
 * @link https://docs.mollie.com/reference/v2/mandates-api/list-mandates
 */
$response = $client->get_mandates( $mollie_customer_id );

$mandates = $response->_embedded->mandates;

/**
 * WordPress user.
 */
$query = $wpdb->prepare(
	"
	SELECT
		user.*
	FROM
		$wpdb->pronamic_pay_mollie_customer_users AS mollie_customer_user
			INNER JOIN
		$wpdb->users AS user
				ON mollie_customer_user.user_id = user.ID
	WHERE
		mollie_customer_user.customer_id = %d
	;
	",
	$data->id
);

$users = $wpdb->get_results( $query );

?>
<div class="wrap">
	<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<h2><?php \printf( \__( 'Customer %s', 'pronamic_ideal' ), \sprintf( '<code>%s</code>', $mollie_customer_id ) ); ?></h2>
	
	<h3><?php \esc_html_e( 'General', 'pronamic_ideal' ); ?></h3>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php \esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
				<td>
					<code><?php echo \esc_html( $customer->id ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Mode', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					switch ( $customer->mode ) {
						case 'test':
							\esc_html_e( 'Test', 'pronamic_ideal' );

							break;
						case 'live':
							\esc_html_e( 'Live', 'pronamic_ideal' );

							break;
						default:
							echo \esc_html( $customer->mode );

							break;
					}

					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Name', 'pronamic_ideal' ); ?></th>
				<td>
					<?php echo \esc_html( $customer->name ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Email', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					if ( null !== $customer->email ) {
						printf(
							'<a href="%s">%s</a>',
							esc_attr( 'mailto:' . $customer->email ),
							esc_html( $customer->email )
						);
					}

					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Locale', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					if ( null !== $customer->locale ) {
						printf(
							'<code>%s</code>',
							esc_html( $customer->locale )
						);
					}

					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Link', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					$mollie_link = \sprintf(
						'https://www.mollie.com/dashboard/customers/%s',
						$customer->id
					);

					\printf(
						'<a href="%s">%s</a>',
						\esc_url( $mollie_link ),
						\esc_html( $mollie_link )
					);

					?>
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php \esc_html_e( 'Mandates', 'pronamic_ideal' ); ?></h3>

	<table class="widefat">
		<thead>
			<tr>
				<th><?php \esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Mode', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Status', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Method', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Details', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Mandate Reference', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Signature Date', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Created On', 'pronamic_ideal' ); ?></th>
			</tr>
		</thead>

		<tbody>
			
			<?php if ( empty( $mandates ) ) : ?>

				<tr>
					<td colspan="4"><?php esc_html_e( 'No mandates found.', 'pronamic_ideal' ); ?></td>
				</tr>

			<?php else : ?>

				<?php foreach ( $mandates as $mandate ) : ?>

					<tr>
						<td>
							<code><?php echo \esc_html( $mandate->id ); ?></code>
						</td>
						<td>
							<?php

							switch ( $mandate->mode ) {
								case 'test':
									\esc_html_e( 'Test', 'pronamic_ideal' );

									break;
								case 'live':
									\esc_html_e( 'Live', 'pronamic_ideal' );

									break;
								default:
									echo \esc_html( $mandate->mode );

									break;
							}

							?>
						</td>
						<td>
							<?php

							switch ( $mandate->status ) {
								case 'valid':
									\esc_html_e( 'Valid', 'pronamic_ideal' );

									break;
								default:
									echo \esc_html( $mandate->status );

									break;
							}

							?>
						</td>
						<td>
							<?php

							switch ( $mandate->method ) {
								case 'directdebit':
									\esc_html_e( 'Direct Debit', 'pronamic_ideal' );

									break;
								default:
									echo \esc_html( $mandate->method );

									break;
							}

							?>
						</td>
						<td>
							<?php

							switch ( $mandate->method ) {
								case 'directdebit':
									?>
									<dl style="margin: 0;">
										<dt><?php \esc_html_e( 'Consumer Name', 'pronamic_ideal' ); ?></dt>
										<dd>
											<?php echo \esc_html( $mandate->details->consumerName ); ?>
										</dd>

										<dt><?php \esc_html_e( 'Consumer Account', 'pronamic_ideal' ); ?></dt>
										<dd>
											<?php echo \esc_html( $mandate->details->consumerAccount ); ?>
										</dd>

										<dt><?php \esc_html_e( 'Consumer BIC', 'pronamic_ideal' ); ?></dt>
										<dd>
											<?php echo \esc_html( $mandate->details->consumerBic ); ?>
										</dd>
									</dl>
									<?php

									break;
								default:
									?>
									<pre><?php var_dump( $mandate->details ); ?></pre>
									<?php

									break;
							}

							?>							
						</td>
						<td>
							<?php echo \esc_html( $mandate->mandateReference ); ?>
						</td>
						<td>
							<?php

							$signature_date = new \DateTime( $mandate->signatureDate );

							echo \esc_html( $signature_date->format( 'd-m-Y' ) );

							?>
						</td>
						<td>
							<?php

							$created_on = new \DateTime( $mandate->createdAt );

							echo \esc_html( $created_on->format( 'd-m-Y H:i:s' ) );

							?>
						</td>
					</tr>

				<?php endforeach; ?>

			<?php endif; ?>

		</tbody>
	</table>

	<h3><?php \esc_html_e( 'WordPress Users', 'pronamic_ideal' ); ?></h3>

	<table class="widefat">
		<thead>
			<tr>
				<th><?php \esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Email', 'pronamic_ideal' ); ?></th>
				<th><?php \esc_html_e( 'Display Name', 'pronamic_ideal' ); ?></th>
			</tr>
		</thead>

		<tbody>	

			<?php if ( empty( $users ) ) : ?>

				<tr>
					<td colspan="3"><?php esc_html_e( 'No users found.', 'pronamic_ideal' ); ?></td>
				</tr>

			<?php else : ?>

				<?php foreach ( $users as $user ) : ?>

					<tr>
						<td>
							<code><?php echo \esc_html( $user->ID ); ?></code>
						</td>
						<td>
							<?php

							printf(
								'<a href="%s">%s</a>',
								esc_attr( 'mailto:' . $user->user_email ),
								esc_html( $user->user_email )
							);

							?>
						</td>
						<td>
							<?php echo \esc_html( $user->display_name ); ?>
						</td>
					</tr>

				<?php endforeach; ?>

			<?php endif; ?>

		</tbody>
	</table>
</div>
