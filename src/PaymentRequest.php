<?php

/**
 * Title: Mollie payment request
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.9
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_PaymentRequest {
	/**
	 * The amount in EURO that you want to charge, e.g. `100.00` if you would want to charge
	 * € 100,00.
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var float
	 */
	public $amount;

	/**
	 * The description of the payment you're creating. This will be shown to the consumer on their
	 * card or bank statement when possible.
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string
	 */
	public $description;

	/**
	 * The URL the consumer will be redirected to after the payment process. It could make sense
	 * for the redirectURL to contain a unique identifier – like your order ID – so you can show
	 * the right page referencing the order when the consumer returns.
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string
	 */
	public $redirect_url;

	/**
	 * Use this parameter to set a wehook URL for this payment only. Mollie will ignore any webhook
	 * set in your website profile for this payment.
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string
	 */
	public $webhook_url;

	/**
	 * Normally, a payment method selection screen is shown. However, when using this parameter,
	 * your customer will skip the selection screen and will be sent directly to the chosen payment
	 * method. The parameter enables you to fully integrate the payment method selection into your
	 * website, however note Mollie's country based conversion optimization is lost.
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string
	 */
	public $method;

	/**
	 * Provide any data you like in JSON notation, and we will save the data alongside the payment.
	 * Whenever you fetch the payment with our API, we'll also include the metadata. You can use up
	 * to 1kB of JSON.
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var mixed
	 */
	public $meta_data;

	/**
	 * Allow you to preset the language to be used in the payment screens shown to the consumer.
	 * When this parameter is not provided, the browser language will be used instead (which is
	 * usually more accurate).
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string
	 */
	public $locale;

	/**
	 * Payment method specific parameters
	 */

	/**
	 * An iDEAL issuer ID, for example ideal_INGNL2A. The returned payment URL will deep-link into
	 * the specific banking website (ING Bank, in this example). For a list of issuers, refer to the
	 * Issuers API.
	 *
	 * @see https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string
	 */
	public $issuer;

	/**
	 * Customer ID for Mollie checkout.
	 *
	 * @see https://www.mollie.com/nl/docs/checkout
	 * @var string
	 */
	public $customer_id;

	/**
	 * Recurring type for Mollie Recurring.
	 *
	 * @see https://www.mollie.com/nl/docs/recurring
	 * @since 1.1.9
	 * @var string
	 */
	public $recurring_type;

	/**
	 * Get array of this Mollie payment request object.
	 *
	 * @return array
	 */
	public function get_array() {
		$array = array(
			'amount'        => number_format( $this->amount, 2, '.', '' ),
			'description'   => $this->description,
			'method'        => $this->method,
			'redirectUrl'   => $this->redirect_url,
			'metadata'      => $this->meta_data,
			'locale'        => $this->locale,
			'webhookUrl'    => $this->webhook_url,
			'issuer'        => $this->issuer,
			'recurringType' => $this->recurring_type,
			'customerId'    => $this->customer_id,
		);

		/*
		 * Array filter will remove values NULL, FALSE and empty strings ('')
		 */
		$array = array_filter( $array );

		return $array;
	}
}
