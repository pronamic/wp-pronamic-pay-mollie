<?php
/**
 * Mollie listener.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: Mollie listener
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Listener {
	/**
	 * Listen.
	 *
	 * @return bool|null
	 */
	public static function listen() {
		if ( ! filter_has_var( INPUT_GET, 'mollie_webhook' ) || ! filter_has_var( INPUT_POST, 'id' ) ) {
			return null;
		}

		$transaction_id = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );

		$payment = get_pronamic_payment_by_transaction_id( $transaction_id );

		if ( null === $payment ) {
			return false;
		}

		// Add note.
		$note = sprintf(
			/* translators: %s: Mollie */
			__( 'Webhook requested by %s.', 'pronamic_ideal' ),
			__( 'Mollie', 'pronamic_ideal' )
		);

		$payment->add_note( $note );

		// Log webhook request.
		do_action( 'pronamic_pay_webhook_log_payment', $payment );

		// Update payment.
		Plugin::update_payment( $payment, false );
	}
}
