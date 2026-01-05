<?php
/**
 * Payment meta box.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 *
 * @since 1.1.6
 * @link  https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$payment = \get_pronamic_payment( $post->ID );

// Check valid payment.
if ( null === $payment ) {
	return;
}

$mollie_payment_id  = $payment->get_transaction_id();
$mollie_customer_id = $payment->get_meta( 'mollie_customer_id' );
$mollie_mandate_id  = $payment->get_meta( 'mollie_mandate_id' );
$mollie_order_id    = $payment->get_meta( 'mollie_order_id' );

?>
<p>
	<?php

	$payment_url = \add_query_arg(
		[
			'page' => 'pronamic_pay_mollie_payments',
			'id'   => $mollie_payment_id,
		],
		\admin_url( 'admin.php' )
	);

	echo \wp_kses(
		\sprintf(
			/* translators: %s: Mollie payment ID anchor. */
			\__( 'Payment: %s', 'pronamic_ideal' ),
			\sprintf(
				current_user_can( 'manage_options' ) ? '<a href="%s">%s</a>' : '%2$s',
				\esc_url( $payment_url ),
				\esc_html( (string) $mollie_payment_id )
			)
		),
		[
			'a' => [
				'href' => true,
			],
		]
	);

	?>
</p>

<?php if ( ! empty( $mollie_customer_id ) ) : ?>

	<p>
		<?php

		$customer_url = \add_query_arg(
			[
				'page' => 'pronamic_pay_mollie_customers',
				'id'   => $mollie_customer_id,
			],
			\admin_url( 'admin.php' )
		);

		echo \wp_kses(
			\sprintf(
				/* translators: %s: Mollie customer ID anchor. */
				\__( 'Customer: %s', 'pronamic_ideal' ),
				\sprintf(
					current_user_can( 'manage_options' ) ? '<a href="%s">%s</a>' : '%2$s',
					\esc_url( $customer_url ),
					\esc_html( $mollie_customer_id )
				)
			),
			[
				'a' => [
					'href' => true,
				],
			]
		);

		?>
	</p>

<?php endif; ?>

<?php if ( ! empty( $mollie_mandate_id ) ) : ?>

	<p>
		<?php

		$mandate_url = \add_query_arg(
			[
				'page'        => 'pronamic_pay_mollie_mandates',
				'config_id'   => $payment->config_id,
				'customer_id' => $mollie_customer_id,
				'mandate_id'  => $mollie_mandate_id,
			],
			\admin_url( 'admin.php' )
		);

		echo \wp_kses(
			\sprintf(
				/* translators: %s: Mollie mandate ID anchor. */
				\__( 'Mandate: %s', 'pronamic_ideal' ),
				\sprintf(
					current_user_can( 'manage_options' ) ? '<a href="%s">%s</a>' : '%2$s',
					\esc_url( $mandate_url ),
					\esc_html( (string) $mollie_mandate_id )
				)
			),
			[
				'a' => [
					'href' => true,
				],
			]
		);

		?>
	</p>

<?php endif; ?>

<?php if ( ! empty( $mollie_order_id ) ) : ?>

	<p>
		<?php

		echo \esc_html(
			\sprintf(
				/* translators: %s: Mollie order ID */
				\__( 'Order: %s', 'pronamic_ideal' ),
				$mollie_order_id
			)
		);

		?>
	</p>

<?php endif; ?>
