<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Mollie listener
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Listener {
	public static function listen() {
		if ( ! filter_has_var( INPUT_GET, 'mollie_webhook' ) || ! filter_has_var( INPUT_POST, 'id' ) ) {
			return;
		}

		$transaction_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );

		$payment = get_pronamic_payment_by_transaction_id( $transaction_id );

		Plugin::update_payment( $payment, false );
	}
}
