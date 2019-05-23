<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie sequence
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   1.1.9
 */
class Sequence {
	/**
	 * Constant for one-off payment.
	 *
	 * @var string
	 */
	const ONE_OFF = 'oneoff';

	/**
	 * Constant for the first payment.
	 *
	 * @var string
	 */
	const FIRST = 'first';

	/**
	 * Constant for recurring payments.
	 *
	 * @var string
	 */
	const RECURRING = 'recurring';

	/**
	 * Constant for subscription payments.
	 *
	 * @var string
	 */
	const SUBSCRIPTION = 'subscription';
}
