<?php
/**
 * Mollie resource.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie resource
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 4.3.0
 * @since   4.3.0
 */
class ResourceType {
	/**
	 * Constant for payments.
	 *
	 * @var string
	 */
	const PAYMENTS = 'payments';

	/**
	 * Constant for orders.
	 *
	 * @var string
	 */
	const ORDERS = 'orders';
}
