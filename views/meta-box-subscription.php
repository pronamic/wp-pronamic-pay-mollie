<?php
/**
 * Subscription meta box.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 *
 * @since 1.1.6
 * @link  https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

$subscription = \get_pronamic_subscription( $post->ID );

$mollie_customer_id = $subscription->get_meta( 'mollie_customer_id' );

?>
<p>
	<?php

	$customer_url = \add_query_arg(
		array(
			'page' => 'pronamic_pay_mollie_customers',
			'id'   => $mollie_customer_id,
		),
		\admin_url( 'admin.php' )
	);

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

	?>
</p>
