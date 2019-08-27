<?php
/**
 * User profile.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 *
 * @since 1.1.6
 * @link  https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

$data = array(
	'_pronamic_pay_mollie_customer_id'      => __( 'Customer live ID', 'pronamic_ideal' ),
	'_pronamic_pay_mollie_customer_id_test' => __( 'Customer test ID', 'pronamic_ideal' ),
);

$customers = array();

foreach ( $data as $meta_key => $label ) {
	$customer_id = get_user_meta( $user->ID, $meta_key, true );

	if ( empty( $customer_id ) ) {
		continue;
	}

	$customers[] = (object) array(
		'id'    => $customer_id,
		'label' => $label,
	);
}

if ( empty( $customers ) ) {
	return;
}

?>
<h2><?php esc_html_e( 'Mollie', 'pronamic_ideal' ); ?></h2>

<table class="form-table">

	<?php foreach ( $customers as $customer ) : ?>

		<tr>
			<th>
				<?php echo esc_html( $customer->label ); ?>
			</th>
			<td>
				<?php

				$mollie_link = sprintf(
					'https://www.mollie.com/dashboard/customers/%s',
					$customer->id
				);

				printf(
					'<a href="%s">%s</a>',
					esc_url( $mollie_link ),
					esc_html( $customer->id )
				);

				?>
			</td>
		</tr>

	<?php endforeach; ?>

</table>
