<?php
/**
 * Page payment.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Admin\AdminPaymentPostType;

$mollie_payment_id = \filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );

$payment = \get_pronamic_payment_by_transaction_id( $mollie_payment_id );

$api_key = \get_post_meta( $payment->config_id, '_pronamic_gateway_mollie_api_key', true );

$client = new Client( $api_key );

/**
 * Customer.
 *
 * @link https://docs.mollie.com/reference/v2/payments-api/get-payment
 */
$mollie_payment = $client->get_payment(
	$mollie_payment_id,
	array(
		'embed' => 'chargebacks,refunds',
	)
);

?>
<div class="wrap">
	<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<h2>
	<?php

	echo \wp_kses(
		\sprintf(
			/* translators: %s: payment number */
			\__( 'Payment %s', 'pronamic_ideal' ),
			\sprintf(
				'<code>%s</code>',
				$mollie_payment_id
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
					<code><?php echo \esc_html( $mollie_payment_id ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Link', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					$mollie_link = \sprintf(
						'https://www.mollie.com/dashboard/payments/%s',
						$mollie_payment_id
					);

					\printf(
						'<a href="%s">%s</a>',
						\esc_url( $mollie_link ),
						\esc_html( $mollie_link )
					);

					?>
				</td>
			</tr>

			<?php if ( null !== $payment ) : ?>

				<?php

				$url = $payment->get_meta( 'mollie_change_payment_state_url' );

				if ( ! empty( $url ) ) :

					?>

					<tr>
						<th scope="row"><?php \esc_html_e( 'Change Payment State', 'pronamic_ideal' ); ?></th>
						<td>
							<?php

							\printf(
								'<a href="%1$s" title="%2$s">%3$s</a>',
								\esc_url( $url ),
								\esc_attr( \__( 'Change Payment State', 'pronamic_ideal' ) ),
								\esc_html( $url )
							);

							?>
						</td>
					</tr>

				<?php endif; ?>

				<tr>
					<th scope="row"><?php \esc_html_e( 'Pronamic Pay Payment', 'pronamic_ideal' ); ?></th>
					<td>
						<?php

						\do_action(
							'manage_' . AdminPaymentPostType::POST_TYPE . '_posts_custom_column',
							'pronamic_payment_title',
							$payment->get_id()
						);

						?>
					</td>
				</tr>

			<?php endif; ?>
		</tbody>
	</table>
</div>
