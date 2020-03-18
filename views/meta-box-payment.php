<?php
/**
 * Payment meta box.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 *
 * @since 1.1.6
 * @link  https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

$payment = \get_pronamic_payment( $post->ID );

$mollie_payment_id  = $payment->get_transaction_id();
$mollie_customer_id = $payment->get_meta( 'mollie_customer_id' );

?>
<p>
	<?php

	$payment_url = \add_query_arg(
		array(
			'page' => 'pronamic_pay_mollie_payments',
			'id'   => $mollie_payment_id,
		),
		\admin_url( 'admin.php' )
	);

	echo \wp_kses(
		\sprintf(
			/* translators: %s: Mollie payment ID anchor. */
			\__( 'Payment: %s', 'pronamic_ideal' ),
			\sprintf(
				'<a href="%s">%s</a>',
				\esc_url( $payment_url ),
				\esc_html( $mollie_payment_id )
			)
		),
		array(
			'a' => array(
				'href' => true,
			),
		)
	);

	if ( $mollie_customer_id ) {
		$customer_url = \add_query_arg(
			array(
				'page' => 'pronamic_pay_mollie_customers',
				'id'   => $mollie_customer_id,
			),
			\admin_url( 'admin.php' )
		);

		echo '<br />';

		echo \wp_kses(
			\sprintf(
				/* translators: %s: Mollie customer ID anchor. */
				\__( 'Customer: %s', 'pronamic_ideal' ),
				\sprintf(
					'<a href="%s">%s</a>',
					\esc_url( $customer_url ),
					\esc_html( $mollie_customer_id )
				)
			),
			array(
				'a' => array(
					'href' => true,
				),
			)
		);
	}

	?>
</p>
