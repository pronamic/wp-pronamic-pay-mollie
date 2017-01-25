<?php

/**
 * Title: Mollie listener
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Listener implements Pronamic_Pay_Gateways_ListenerInterface {
	public static function listen() {
		if (
			filter_has_var( INPUT_GET, 'mollie_webhook' )
				&&
			filter_has_var( INPUT_POST, 'id' )
		) {
			$transaction_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );

			$payment = get_pronamic_payment_by_transaction_id( $transaction_id );

			Pronamic_WP_Pay_Plugin::update_payment( $payment, false );
		}
	}
}
