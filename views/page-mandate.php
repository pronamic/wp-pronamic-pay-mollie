<?php
/**
 * Page customer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Client;
use Pronamic\WordPress\Mollie\ObjectAccess;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valid global.
 *
 * @see Pronamic\WordPress\Pay\Gateways\Mollie\Admin::page_mollie_customers()
 * @psalm-suppress InvalidGlobal
 */
global $wpdb;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not necessary because this parameter does not trigger an action
$config_id = array_key_exists( 'config_id', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['config_id'] ) ) : null;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not necessary because this parameter does not trigger an action
$mollie_customer_id = array_key_exists( 'customer_id', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['customer_id'] ) ) : null;

if ( null === $mollie_customer_id ) {
	return;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is not necessary because this parameter does not trigger an action
$mollie_mandate_id = array_key_exists( 'mandate_id', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['mandate_id'] ) ) : null;

if ( null === $mollie_mandate_id ) {
	return;
}

$api_key = \get_post_meta( $config_id, '_pronamic_gateway_mollie_api_key', true );

$mollie_mandate = null;

if ( $api_key ) {
	$client = new Client( $api_key );

	try {
		/**
		 * Mandate.
		 *
		 * @link https://docs.mollie.com/reference/v2/mandates-api/get-mandate
		 */
		$response = $client->get_mandate( $mollie_mandate_id, $mollie_customer_id );

		$mollie_mandate = $response;
	} catch ( \Exception ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		// No problem, in case of an error we will not show the remote information.
	}
}

?>
<div class="wrap">
	<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<h2>
		<?php

		echo \wp_kses(
			\sprintf(
				/* translators: %s: Mollie customer ID. */
				\__( 'Mandate %s', 'pronamic_ideal' ),
				\sprintf(
					'<code>%s</code>',
					$mollie_mandate_id
				)
			),
			[
				'code' => [],
			]
		);

		?>
	</h2>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Customer ID', 'pronamic_ideal' ); ?></th>
				<td>
					<code><?php echo \esc_html( $mollie_customer_id ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Mandate ID', 'pronamic_ideal' ); ?></th>
				<td>
					<code><?php echo \esc_html( $mollie_mandate_id ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php \esc_html_e( 'Link', 'pronamic_ideal' ); ?></th>
				<td>
					<?php

					$mollie_link = \sprintf(
						'https://my.mollie.com/dashboard/customers/%s',
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

			<?php if ( $mollie_mandate ) : ?>

				<tr>
					<th scope="row"><?php \esc_html_e( 'Mode', 'pronamic_ideal' ); ?></th>
					<td>
						<?php

						switch ( $mollie_mandate->mode ) {
							case 'test':
								\esc_html_e( 'Test', 'pronamic_ideal' );
								break;
							case 'live':
								\esc_html_e( 'Live', 'pronamic_ideal' );
								break;
							default:
								echo \esc_html( $mollie_mandate->mode );
								break;
						}

						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'Status', 'pronamic_ideal' ); ?></th>
					<td>
						<?php

						switch ( $mollie_mandate->status ) {
							case 'valid':
								\esc_html_e( 'Valid', 'pronamic_ideal' );
								break;
							case 'pending':
								\esc_html_e( 'Pending', 'pronamic_ideal' );
								break;
							case 'invalid':
								\esc_html_e( 'Invalid', 'pronamic_ideal' );
								break;
							default:
								echo \esc_html( $mollie_mandate->status );
								break;
						}

						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'Method', 'pronamic_ideal' ); ?></th>
					<td>
						<?php

						switch ( $mollie_mandate->method ) {
							case 'directdebit':
								\esc_html_e( 'Direct debit', 'pronamic_ideal' );
								break;
							case 'creditcard':
								\esc_html_e( 'Credit card', 'pronamic_ideal' );
								break;
							case 'paypal':
								\esc_html_e( 'PayPal', 'pronamic_ideal' );
								break;
							default:
								echo \esc_html( $mollie_mandate->method );
								break;
						}

						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'Details', 'pronamic_ideal' ); ?></th>
					<td>
						<pre><?php echo \esc_html( \wp_json_encode( $mollie_mandate->details, \JSON_PRETTY_PRINT ) ); ?></pre>
					</td>
				</tr>

				<?php

				$object_access = new ObjectAccess( $mollie_mandate );

				?>

				<tr>
					<th scope="row"><?php \esc_html_e( 'Reference', 'pronamic_ideal' ); ?></th>
					<td>
						<?php echo \esc_html( $object_access->get_optional( 'mandateReference' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'Signature date', 'pronamic_ideal' ); ?></th>
					<td>
						<?php echo \esc_html( $object_access->get_optional( 'signatureDate' ) ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php \esc_html_e( 'Created at', 'pronamic_ideal' ); ?></th>
					<td>
						<?php echo \esc_html( $object_access->get_optional( 'createdAt' ) ); ?>
					</td>
				</tr>

			<?php endif; ?>

		</tbody>
	</table>
</div>
