<?php
/**
 * Page customer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Valid global.
 *
 * @see Pronamic\WordPress\Pay\Gateways\Mollie\Admin::page_mollie_customers()
 * @psalm-suppress InvalidGlobal
 */
global $wpdb;

$mollie_customer_id = \filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );

$mollie_customer_data = $wpdb->get_row(
	$wpdb->prepare(
		"
		SELECT
			mollie_customer.*,
			IF ( mollie_customer.test_mode, mollie_profile.api_key_test, mollie_profile.api_key_live ) AS api_key
		FROM
			$wpdb->pronamic_pay_mollie_customers AS mollie_customer
				LEFT JOIN
			$wpdb->pronamic_pay_mollie_profiles AS mollie_profile
					ON mollie_customer.profile_id = mollie_profile.id
		WHERE
			mollie_customer.mollie_id = %s
		LIMIT
			1
		;
		",
		$mollie_customer_id
	)
);

$mollie_customer = null;

$mollie_customer_mandates = null;

if ( $mollie_customer_data->api_key ) {
	$client = new Client( $mollie_customer_data->api_key );

	/**
	 * Customer.
	 *
	 * @link https://docs.mollie.com/reference/v2/customers-api/get-customer
	 */
	$mollie_customer = $client->get_customer( $mollie_customer_id );

	/**
	 * Mandates.
	 *
	 * @link https://docs.mollie.com/reference/v2/mandates-api/list-mandates
	 */
	$response = $client->get_mandates( $mollie_customer_id );

	$mollie_customer_mandates = $response->_embedded->mandates;
}

/**
 * WordPress user.
 */
$users = $wpdb->get_results(
	$wpdb->prepare(
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
		$mollie_customer_data->id
	)
);

?>
<div class="wrap">
	<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<h2>
	<?php

	echo \wp_kses(
		\sprintf(
			/* translators: %s: Mollie customer ID. */
			\__( 'Customer %s', 'pronamic_ideal' ),
			\sprintf(
				'<code>%s</code>',
				$mollie_customer_id
			)
		),
		array(
			'code' => array(),
		)
	);

	?>
	</h2>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php \esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
				<td>
					<code><?php echo \esc_html( $mollie_customer_data->id ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Mode', 'pronamic_ideal' ); ?></th>
				<td>
					<?php $mollie_customer_data->test_mode ? \esc_html_e( 'Test', 'pronamic_ideal' ) : \esc_html_e( 'Live', 'pronamic_ideal' ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Name', 'pronamic_ideal' ); ?></th>
				<td>
					<?php echo \esc_html( $mollie_customer_data->name ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Email', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					if ( null !== $mollie_customer_data->email ) {
						printf(
							'<a href="%s">%s</a>',
							esc_attr( 'mailto:' . $mollie_customer_data->email ),
							esc_html( $mollie_customer_data->email )
						);
					}

					?>
				</td>
			</tr>

			<?php if ( null !== $mollie_customer ) : ?>

				<tr>
					<th scope="row"><?php \esc_html_e( 'Locale', 'pronamic_ideal' ); ?></th>
					<td>
						<?php

						if ( null !== $mollie_customer->locale ) {
							printf(
								'<code>%s</code>',
								esc_html( $mollie_customer->locale )
							);
						}

						?>
					</td>
				</tr>

			<?php endif; ?>

			<tr>
				<th scope="row"><?php \esc_html_e( 'Link', 'pronamic_ideal' ); ?></th>
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
		</tbody>
	</table>

	<?php if ( null !== $mollie_customer_mandates ) : ?>

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

				<?php if ( empty( $mollie_customer_mandates ) ) : ?>

					<tr>
						<td colspan="4"><?php esc_html_e( 'No mandates found.', 'pronamic_ideal' ); ?></td>
					</tr>

				<?php else : ?>

					<?php foreach ( $mollie_customer_mandates as $mandate ) : ?>

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
									case 'pending':
										\esc_html_e( 'Pending', 'pronamic_ideal' );

										break;
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
									case 'creditcard':
										\esc_html_e( 'Credit Card', 'pronamic_ideal' );

										break;
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
									case 'creditcard':
										?>
										<dl style="margin: 0;">

											<?php if ( ! empty( $mandate->details->cardHolder ) ) : ?>

												<dt><?php \esc_html_e( 'Card Holder', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->cardHolder ); ?>
												</dd>

											<?php endif; ?>

											<?php if ( ! empty( $mandate->details->cardNumber ) ) : ?>

												<dt><?php \esc_html_e( 'Card Number', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->cardNumber ); ?>
												</dd>

											<?php endif; ?>

											<?php if ( ! empty( $mandate->details->cardLabel ) ) : ?>

												<dt><?php \esc_html_e( 'Card Label', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->cardLabel ); ?>
												</dd>

											<?php endif; ?>

											<?php if ( ! empty( $mandate->details->cardFingerprint ) ) : ?>

												<dt><?php \esc_html_e( 'Card Fingerprint', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->cardFingerprint ); ?>
												</dd>

											<?php endif; ?>

											<?php if ( ! empty( $mandate->details->cardExpiryDate ) ) : ?>

												<dt><?php \esc_html_e( 'Card Expiry Date', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->cardExpiryDate ); ?>
												</dd>

											<?php endif; ?>
										</dl>
										<?php

										break;
									case 'directdebit':
										?>
										<dl style="margin: 0;">

											<?php if ( ! empty( $mandate->details->consumerName ) ) : ?>

												<dt><?php \esc_html_e( 'Consumer Name', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->consumerName ); ?>
												</dd>

											<?php endif; ?>

											<?php if ( ! empty( $mandate->details->consumerAccount ) ) : ?>

												<dt><?php \esc_html_e( 'Consumer Account', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->consumerAccount ); ?>
												</dd>

											<?php endif; ?>

											<?php if ( ! empty( $mandate->details->consumerBic ) ) : ?>

												<dt><?php \esc_html_e( 'Consumer BIC', 'pronamic_ideal' ); ?></dt>
												<dd>
													<?php echo \esc_html( $mandate->details->consumerBic ); ?>
												</dd>

											<?php endif; ?>
										</dl>
										<?php

										break;
									default:
										?>
										<pre><?php echo \esc_html( (string) \wp_json_encode( $mandate->details, \JSON_PRETTY_PRINT ) ); ?></pre>
										<?php

										break;
								}

								?>
							</td>
							<td>
								<?php

								// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie.
								echo \esc_html( $mandate->mandateReference );

								?>
							</td>
							<td>
								<?php

								// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie.
								$signature_date = new \DateTime( $mandate->signatureDate );

								echo \esc_html( $signature_date->format( 'd-m-Y' ) );

								?>
							</td>
							<td>
								<?php

								// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie.
								$created_on = new \DateTime( $mandate->createdAt );

								echo \esc_html( $created_on->format( 'd-m-Y H:i:s' ) );

								?>
							</td>
						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>
		</table>

	<?php endif; ?>

	<?php if ( ! empty( $users ) ) : ?>

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

			</tbody>
		</table>

	<?php endif; ?>

</div>
