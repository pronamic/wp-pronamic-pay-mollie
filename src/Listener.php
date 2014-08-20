<?php

/**
 * Title: Mollie listener
 * Description:
 * Copyright: Copyright (c) 2005 - 2014
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Listener implements Pronamic_Pay_Gateways_ListenerInterface {
	public static function listen() {
		if ( 
			filter_has_var( INPUT_GET, 'mollie_webhook' )
				&&
			filter_has_var( INPUT_GET, 'type' )
				&&
			filter_has_var( INPUT_GET, 'id' )
		) {
			$type           = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
			$transaction_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );

			if ( 'payment' == $type ) {
				$payment = get_pronamic_payment_by_transaction_id( $transaction_id );

				Pronamic_WP_Pay_Plugin::update_payment( $payment );
			}
		}
	}
}
