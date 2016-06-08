<?php

/**
 * User profile.
 *
 * @since 1.1.6
 * @see https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
 */

$customer_id = get_user_meta( $user->ID, '_pronamic_pay_mollie_customer_id', true );

?>
<h2><?php esc_html_e( 'Mollie', 'pronamic_ideal' ); ?></h2>

<table class="form-table">
	<tr>
		<th>
			<label for="pronamic_pay_mollie_customer_id">
				<?php esc_html_e( 'Customer ID', 'pronamic_ideal' ); ?>
			</label>
		</th>
		<td>
			<input id="pronamic_pay_mollie_customer_id" name="pronamic_pay_mollie_customer_id" type="text" value="<?php echo esc_attr( $customer_id ); ?>" class="regular-text" readonly="readonly" />
		</td>
	</tr>
</table>
