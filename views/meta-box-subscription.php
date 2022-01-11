<?php
/**
 * Subscription meta box.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 *
 * @since 1.1.6
 * @link  https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

$subscription = \get_pronamic_subscription( $post->ID );

if ( null === $subscription ) :
	return;
endif;

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
				\esc_html( (string) $mollie_customer_id )
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

<?php

$mandate_id = $subscription->get_meta( 'mollie_mandate_id' );

if ( ! empty( $mandate_id ) ) :

	?>

	<p>
		<?php

		echo esc_html(
			sprintf(
				/* translators: %s: Mollie mandate ID */
				\__( 'Mandate: %s', 'pronamic_ideal' ),
				$mandate_id
			)
		);

		?>
	</p>

<?php endif; ?>
