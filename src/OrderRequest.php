<?php
/**
 * Mollie order request.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateTimeInterface;
use JsonSerializable;

/**
 * Order request class
 */
class OrderRequest implements JsonSerializable {
	/**
	 * The total amount of the order, including VAT and discounts. This is the amount that
	 * will be charged to your customer. It has to match the sum of the lines totalAmount amounts.
	 * For example: {"currency":"EUR", "value":"100.00"} if the total order amount is €100.00.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var Amount
	 */
	private Amount $amount;

	/**
	 * The order number. For example, `16738`. We recommend that each order
	 * should have a unique order number.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var string
	 */
	private string $order_number;

	/**
	 * The lines in the order. Each line contains details such as a description of the item ordered,
	 * its price et cetera. All order lines must have the same currency as the order. You cannot
	 * mix currencies within a single order.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var Lines
	 */
	private Lines $lines;

	/**
	 * The billing person and address for the order. This field is not required if you
	 * make use of the PayPal Express Checkout button.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var Address|null
	 */
	public ?Address $billing_address = null;

	/**
	 * The shipping address for the order. This field is optional, but if it is provided,
	 * then the full name and address have to be in a valid format.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var Address|null
	 */
	public ?Address $shipping_address = null;

	/**
	 * The date of birth of your customer. Some payment methods need this value and if you have it,
	 * you should send it so that your customer does not have to enter it again later in the checkout process.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var DateTimeInterface|null
	 */
	public ?DateTimeInterface $consumer_date_of_birth = null;

	/**
	 * The URL the consumer will be redirected to after the payment process. It could make sense
	 * for the redirectURL to contain a unique identifier – like your order ID – so you can show
	 * the right page referencing the order when the consumer returns.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var string|null
	 */
	public ?string $redirect_url = null;

	/**
	 * Set the webhook URL, where we will send order status changes to.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var string|null
	 */
	public ?string $webhook_url = null;

	/**
	 * Allows you to preset the language to be used in the hosted payment pages shown to
	 * the consumer. You can provide any `xx_XX` format ISO 15897 locale, but our hosted
	 * payment pages does not support all languages.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var string
	 */
	private string $locale;

	/**
	 * Normally, a payment method screen is shown. However, when using this parameter, you
	 * can choose a specific payment method and your customer will skip the selection screen
	 * and is sent directly to the chosen payment method. The parameter enables you to fully
	 * integrate the payment method selection into your website.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var string|array<string>|null
	 */
	public $method;

	/**
	 * Any payment specific properties (for example, the `dueDate` for bank transfer payments)
	 * can be passed here.
	 *
	 * The payment property should be an object where the keys are the payment method specific
	 * parameters you want to pass.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order#payment-parameters
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @var array<string, string>|null
	 */
	public ?array $payment = null;

	/**
	 * Provide any data you like in JSON notation, and we will save the data alongside the payment.
	 * Whenever you fetch the payment with our API, we'll also include the metadata. You can use up
	 * to 1kB of JSON.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
	 * @link https://en.wikipedia.org/wiki/Metadata
	 * @var object|string|null
	 */
	private $metadata;

	/**
	 * Create Mollie payment request object.
	 *
	 * @param Amount $amount       The amount that you want to charge.
	 * @param string $order_number The order number.
	 * @param Lines  $lines        The lines in the order.
	 * @param string $locale       Locale.
	 */
	public function __construct( Amount $amount, string $order_number, Lines $lines, string $locale ) {
		$this->amount       = $amount;
		$this->order_number = $order_number;
		$this->lines        = $lines;
		$this->locale       = $locale;
	}

	/**
	 * Set billing address.
	 *
	 * @param Address|null $billing_address Billing address.
	 */
	public function set_billing_address( ?Address $billing_address ) : void {
		$this->billing_address = $billing_address;
	}

	/**
	 * Set shipping address.
	 *
	 * @param Address|null $shipping_address Shipping address.
	 */
	public function set_shipping_address( ?Address $shipping_address ) : void {
		$this->shipping_address = $shipping_address;
	}

	/**
	 * Set consumer date of birth.
	 *
	 * @param DateTimeInterface|null $consumer_date_of_birth Consumer date of birth.
	 */
	public function set_consumer_date_of_birth( ?DateTimeInterface $consumer_date_of_birth ) : void {
		$this->consumer_date_of_birth = $consumer_date_of_birth;
	}

	/**
	 * Set redirect URL.
	 *
	 * @param string|null $redirect_url Redirect URL.
	 */
	public function set_redirect_url( ?string $redirect_url ) : void {
		$this->redirect_url = $redirect_url;
	}

	/**
	 * Set webhook URL.
	 *
	 * @param string|null $webhook_url Webhook URL.
	 */
	public function set_webhook_url( ?string $webhook_url ) : void {
		$this->webhook_url = $webhook_url;
	}

	/**
	 * Set method.
	 *
	 * @param array<string>|string|null $method Method.
	 */
	public function set_method( $method ) : void {
		$this->method = $method;
	}

	/**
	 * Set payment.
	 *
	 * @param array<string, string>|null $payment Payment specific parameters.
	 * @link https://docs.mollie.com/reference/v2/orders-api/create-order#payment-specific-parameters
	 */
	public function set_payment( ?array $payment ) : void {
		$this->payment = $payment;
	}

	/**
	 * Set metadata.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @link https://en.wikipedia.org/wiki/Metadata
	 * @param mixed $metadata Metadata.
	 * @return void
	 */
	public function set_metadata( $metadata = null ) : void {
		$this->metadata = $metadata;
	}

	/**
	 * JSON serialize.
	 *
	 * @return mixed
	 */
	public function jsonSerialize() {
		$object_builder = new ObjectBuilder();

		$object_builder->set_required( 'amount', $this->amount->jsonSerialize() );
		$object_builder->set_required( 'orderNumber', $this->order_number );
		$object_builder->set_required( 'lines', $this->lines->jsonSerialize() );
		$object_builder->set_optional( 'billingAddress', null === $this->billing_address ? null : $this->billing_address->jsonSerialize() );
		$object_builder->set_optional( 'shippingAddress', null === $this->shipping_address ? null : $this->shipping_address->jsonSerialize() );
		$object_builder->set_optional( 'consumerDateOfBirth', null === $this->consumer_date_of_birth ? null : $this->consumer_date_of_birth->format( 'Y-m-d' ) );
		$object_builder->set_optional( 'redirectUrl', $this->redirect_url );
		$object_builder->set_optional( 'webhookUrl', $this->webhook_url );
		$object_builder->set_required( 'locale', $this->locale );
		$object_builder->set_optional( 'method', $this->method );
		$object_builder->set_optional( 'payment', $this->payment );
		$object_builder->set_optional( 'metadata', $this->metadata );

		return $object_builder->jsonSerialize();
	}
}
