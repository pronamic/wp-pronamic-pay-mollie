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

$mollie_payment_id = \filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );

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
		</tbody>
	</table>
</div>
