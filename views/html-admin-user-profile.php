<?php

/**
 * User profile.
 *
 * @since 1.1.6
 * @see https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

$customer_id_live = get_user_meta( $user->ID, '_pronamic_pay_mollie_customer_id', true );
$customer_id_test = get_user_meta( $user->ID, '_pronamic_pay_mollie_customer_id_test', true );

?>
<h2><?php esc_html_e( 'Mollie', 'pronamic_ideal' ); ?></h2>

<table class="form-table">
	<tr>
		<th>
			<label for="pronamic_pay_mollie_customer_id_live">
				<?php esc_html_e( 'Customer live ID', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<input id="pronamic_pay_mollie_customer_id_live" name="pronamic_pay_mollie_customer_id_live" type="text" value="<?php echo esc_attr( $customer_id_live ); ?>" class="regular-text" readonly="readonly" />
		</td>
	</tr>
	<tr>
		<th>
			<label for="pronamic_pay_mollie_customer_id_test">
				<?php esc_html_e( 'Customer test ID', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<input id="pronamic_pay_mollie_customer_id_test" name="pronamic_pay_mollie_customer_id_test" type="text" value="<?php echo esc_attr( $customer_id_test ); ?>" class="regular-text" readonly="readonly" />
		</td>
	</tr>
</table>
