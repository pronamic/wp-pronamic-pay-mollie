<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Mollie listener
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Listener {
	public static function listen() {
		if ( ! filter_has_var( INPUT_GET, 'mollie_webhook' ) || ! filter_has_var( INPUT_POST, 'id' ) ) {
			return;
		}

		$transaction_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );

		$payment = get_pronamic_payment_by_transaction_id( $transaction_id );

		if ( null === $payment ) {
			return;
		}

		// Add note.
		$note = sprintf(
			/* translators: %s: Mollie */
			__( 'Webhook requested by %s.', 'pronamic_ideal' ),
			__( 'Mollie', 'pronamic_ideal' )
		);

		$payment->add_note( $note );

		// Update payment.
		Plugin::update_payment( $payment, false );
	}
}
