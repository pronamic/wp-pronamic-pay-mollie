<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie Recurring
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.1.9
 * @since 1.1.9
 */
class Recurring {
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
