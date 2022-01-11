<?php
/**
 * Mollie payment request.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie payment request
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.4
 * @since   1.0.0
 */
class PaymentRequest {
	/**
	 * The amount in EURO that you want to charge, e.g. `{"currency":"EUR", "value":"100.00"}`
	 * if you would want to charge € 100,00.
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @var Amount
	 */
	public $amount;

	/**
	 * The description of the payment you're creating. This will be shown to the consumer on their
	 * card or bank statement when possible.
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string
	 */
	public $description;

	/**
	 * The URL the consumer will be redirected to after the payment process. It could make sense
	 * for the redirectURL to contain a unique identifier – like your order ID – so you can show
	 * the right page referencing the order when the consumer returns.
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string|null
	 */
	public $redirect_url;

	/**
	 * Use this parameter to set a webhook URL for this payment only. Mollie will ignore any webhook
	 * set in your website profile for this payment.
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string|null
	 */
	public $webhook_url;

	/**
	 * Normally, a payment method selection screen is shown. However, when using this parameter,
	 * your customer will skip the selection screen and will be sent directly to the chosen payment
	 * method. The parameter enables you to fully integrate the payment method selection into your
	 * website, however note Mollie's country based conversion optimization is lost.
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string|null
	 */
	public $method;

	/**
	 * Provide any data you like in JSON notation, and we will save the data alongside the payment.
	 * Whenever you fetch the payment with our API, we'll also include the metadata. You can use up
	 * to 1kB of JSON.
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @link https://en.wikipedia.org/wiki/Metadata
	 * @var mixed|null
	 */
	private $metadata;

	/**
	 * Allow you to preset the language to be used in the payment screens shown to the consumer.
	 * When this parameter is not provided, the browser language will be used instead (which is
	 * usually more accurate).
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string|null
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
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @var string|null
	 */
	public $issuer;

	/**
	 * Billing email.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @var string|null
	 */
	private $billing_email;

	/**
	 * The date the payment should expire, in YYYY-MM-DD format. Please note: the minimum date
	 * is tomorrow and the maximum date is 100 days after tomorrow.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @var \DateTimeInterface|null
	 */
	private $due_date;

	/**
	 * Customer ID for Mollie checkout.
	 *
	 * @link https://www.mollie.com/nl/docs/checkout
	 * @var string|null
	 */
	public $customer_id;

	/**
	 * Sequence type for Mollie Recurring.
	 *
	 * @link https://docs.mollie.com/payments/recurring#:~:text=sequenceType
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment#:~:text=sequenceType
	 * @since 1.1.9
	 * @var string|null
	 */
	public $sequence_type;

	/**
	 * Mandate ID.
	 *
	 * When creating recurring payments, the ID of a specific Mandate may be
	 * supplied to indicate which of the consumer’s accounts should be
	 * credited.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment#:~:text=mandateId
	 * @link https://docs.mollie.com/reference/v2/payments-api/get-payment#:~:text=mandateId
	 * @since unreleased
	 * @var string|null
	 */
	public $mandate_id;

	/**
	 * Consumer name for SEPA Direct Debit.
	 *
	 * Beneficiary name of the account holder. Only available if one-off payments are enabled
	 * on your account. Will pre-fill the beneficiary name in the checkout screen if present.
	 *
	 * @var string|null
	 */
	public $consumer_name;

	/**
	 * Consumer account for SEPA Direct Debit.
	 *
	 * IBAN of the account holder. Only available if one-off payments are enabled on your account.
	 * Will pre-fill the IBAN in the checkout screen if present.
	 *
	 * @var string|null
	 */
	public $consumer_account;

	/**
	 * Create Mollie payment request object.
	 *
	 * @param Amount $amount      The amount that you want to charge.
	 * @param string $description The description of the payment you’re creating.
	 */
	public function __construct( $amount, $description ) {
		$this->amount      = $amount;
		$this->description = $description;
	}

	/**
	 * Get method.
	 *
	 * @return null|string
	 */
	public function get_method() {
		return $this->method;
	}

	/**
	 * Set method.
	 *
	 * @param null|string $method Method.
	 * @return void
	 */
	public function set_method( $method ) {
		$this->method = $method;
	}

	/**
	 * Get due date.
	 *
	 * @return null|\DateTimeInterface
	 */
	public function get_due_date() {
		return $this->due_date;
	}

	/**
	 * Set due date.
	 *
	 * @param null|\DateTimeInterface $due_date Due date.
	 * @return void
	 */
	public function set_due_date( $due_date ) {
		$this->due_date = $due_date;
	}

	/**
	 * Get billing email.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @return string|null
	 */
	public function get_billing_email() {
		return $this->billing_email;
	}

	/**
	 * Set billing email.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @param string|null $email Billing email.
	 * @return void
	 */
	public function set_billing_email( $email = null ) {
		$this->billing_email = $email;
	}

	/**
	 * Get sequence type.
	 *
	 * @return string|null
	 */
	public function get_sequence_type() {
		return $this->sequence_type;
	}

	/**
	 * Set sequence type.
	 *
	 * @param string|null $sequence_type Sequence type.
	 * @return void
	 */
	public function set_sequence_type( $sequence_type = null ) {
		$this->sequence_type = $sequence_type;
	}

	/**
	 * Get mandate ID.
	 *
	 * When creating recurring payments, the ID of a specific Mandate may be
	 * supplied to indicate which of the consumer’s accounts should be
	 * credited.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment#:~:text=mandateId
	 * @link https://docs.mollie.com/reference/v2/payments-api/get-payment#:~:text=mandateId
	 * @return string|null
	 */
	public function get_mandate_id() {
		return $this->mandate_id;
	}

	/**
	 * Set mandate ID.
	 *
	 * When creating recurring payments, the ID of a specific Mandate may be
	 * supplied to indicate which of the consumer’s accounts should be
	 * credited.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment#:~:text=mandateId
	 * @link https://docs.mollie.com/reference/v2/payments-api/get-payment#:~:text=mandateId
	 * @param string|null $mandate_id Mandate ID.
	 * @return void
	 */
	public function set_mandate_id( $mandate_id = null ) {
		$this->mandate_id = $mandate_id;
	}

	/**
	 * Get metadata.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @link https://en.wikipedia.org/wiki/Metadata
	 * @return mixed
	 */
	public function get_metadata() {
		return $this->metadata;
	}

	/**
	 * Set metadata.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @link https://en.wikipedia.org/wiki/Metadata
	 * @param mixed $metadata Metadata.
	 * @return void
	 */
	public function set_metadata( $metadata = null ) {
		$this->metadata = $metadata;
	}

	/**
	 * Get array of this Mollie payment request object.
	 *
	 * @return array<string,null|string|object>
	 */
	public function get_array() {
		// Due date.
		$due_date = $this->get_due_date();

		if ( null !== $due_date ) {
			$due_date = $due_date->format( 'Y-m-d' );
		}

		$array = array(
			'amount'          => $this->amount->get_json(),
			'description'     => $this->description,
			'method'          => $this->method,
			'redirectUrl'     => $this->redirect_url,
			'metadata'        => $this->metadata,
			'locale'          => $this->locale,
			'webhookUrl'      => $this->webhook_url,
			'consumerName'    => $this->consumer_name,
			'consumerAccount' => $this->consumer_account,
			'issuer'          => $this->issuer,
			'billingEmail'    => $this->billing_email,
			'dueDate'         => $due_date,
			'sequenceType'    => $this->sequence_type,
			'customerId'      => $this->customer_id,
			'mandateId'       => $this->mandate_id,
		);

		/*
		 * Array filter will remove values NULL, FALSE and empty strings ('')
		 */
		$array = array_filter( $array );

		return $array;
	}
}
