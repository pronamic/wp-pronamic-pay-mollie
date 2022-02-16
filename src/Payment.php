<?php
/**
 * Payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateTimeInterface;

/**
 * Payment
 *
 * @author  Remco Tolsma
 * @version 2.2.2
 * @since   2.1.0
 */
class Payment extends BaseResource {
	/**
	 * The payment’s status.
	 *
	 * @var string
	 */
	private $status;

	/**
	 * The payment method used for this payment, either forced on creation by specifying the method parameter, or chosen by the customer on our payment method selection screen.
	 *
	 * @var string|null
	 */
	private $method;

	/**
	 * The identifier referring to the profile this payment was created on.
	 *
	 * @var string
	 */
	private $profile_id;

	/**
	 * If a customer was specified upon payment creation, the customer’s token will be available here as well.
	 *
	 * @var string|null
	 */
	private $customer_id;

	/**
	 * If the payment is a first or recurring payment, this field will hold the ID of the mandate.
	 *
	 * @var string|null
	 */
	private $mandate_id;

	/**
	 * Payment method specific details.
	 *
	 * @var PaymentDetails|null
	 */
	private $details;

	/**
	 * The mode used to create this payment. Mode determines whether a payment is real (live mode) or a test payment.
	 *
	 * @var string
	 */
	private $mode;

	/**
	 * The payment’s date and time of creation, in ISO 8601 format.
	 *
	 * @var DateTimeInterface
	 */
	private $created_at;

	/**
	 * The date and time the payment will expire, in ISO 8601 format. This parameter is omitted if the payment can no longer expire.
	 *
	 * @var DateTimeInterface
	 */
	private $expires_at;

	/**
	 * The amount of the payment, e.g. {"currency":"EUR", "value":"100.00"} for a €100.00 payment.
	 *
	 * @var Amount
	 */
	private $amount;

	/**
	 * A short description of the payment. The description is visible in the Dashboard and will be shown on the customer’s bank or card statement when possible.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * The URL your customer will be redirected to after completing or canceling the payment process.
	 *
	 * @var string|null
	 */
	private $redirect_url;

	/**
	 * The optional metadata you provided upon payment creation. Metadata can for example be used to link an order to a payment.
	 *
	 * @var string
	 */
	private $metadata;

	/**
	 * The customer’s locale, either forced on creation by specifying the `locale` parameter, or detected by us during checkout. Will be a full locale, for example `nl_NL`.
	 *
	 * @var string
	 */
	private $locale;

	/**
	 * Indicates which type of payment this is in a recurring sequence.
	 * Set to `first` for first payments that allow the customer to agree to automatic recurring charges taking place on their account in the future.
	 * Set to `recurring` for payments where the customer’s card is charged automatically.
	 * Set to `oneoff` by default, which indicates the payment is a regular non-recurring payment.
	 *
	 * @var string
	 */
	private $sequence_type;

	/**
	 * For bank transfer payments, the `_links` object will contain some additional URL objects relevant to the payment.
	 *
	 * @var object
	 */
	private $links;

	/**
	 * Amount refunded.
	 *
	 * @var Amount|null
	 */
	private $amount_refunded;

	/**
	 * Construct payment.
	 *
	 * @param string            $id            Identifier.
	 * @param string            $mode          Mode.
	 * @param DateTimeInterface $created_at    Created at.
	 * @param string            $status        Status.
	 * @param Amount            $amount        Amount.
	 * @param string            $description   Description.
	 * @param string|null       $redirect_url  Redirect URL.
	 * @param string|null       $method        Method.
	 * @param string            $metadata      Metadata.
	 * @param string            $profile_id    Profile ID.
	 * @param string            $sequence_type Sequence type.
	 * @param object            $links         Links.
	 */
	public function __construct( $id, $mode, DateTimeInterface $created_at, $status, Amount $amount, $description, $redirect_url, $method, $metadata, $profile_id, $sequence_type, $links ) {
		parent::__construct( $id );

		$this->mode          = $mode;
		$this->created_at    = $created_at;
		$this->status        = $status;
		$this->amount        = $amount;
		$this->description   = $description;
		$this->redirect_url  = $redirect_url;
		$this->method        = $method;
		$this->metadata      = $metadata;
		$this->profile_id    = $profile_id;
		$this->sequence_type = $sequence_type;
		$this->links         = $links;
	}

	/**
	 * Get status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Get method.
	 *
	 * @return string|null
	 */
	public function get_method() {
		return $this->method;
	}

	/**
	 * Get sequence type.
	 *
	 * @return string
	 */
	public function get_sequence_type() {
		return $this->sequence_type;
	}

	/**
	 * Get profile ID.
	 *
	 * @return string
	 */
	public function get_profile_id() {
		return $this->profile_id;
	}

	/**
	 * Get locale.
	 *
	 * @return string
	 */
	public function get_locale() {
		return $this->locale;
	}

	/**
	 * Set locale.
	 *
	 * @param string $locale Locale.
	 * @return void
	 */
	public function set_locale( $locale ) {
		$this->locale = $locale;
	}

	/**
	 * Get customer ID.
	 *
	 * @return string|null
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * Set customer ID.
	 *
	 * @param string|null $customer_id Customer ID.
	 * @return void
	 */
	public function set_customer_id( $customer_id ) {
		$this->customer_id = $customer_id;
	}

	/**
	 * Get mandate ID.
	 *
	 * @return string|null
	 */
	public function get_mandate_id() {
		return $this->mandate_id;
	}

	/**
	 * Set mandate ID.
	 *
	 * @param string|null $mandate_id Mandate ID.
	 * @return void
	 */
	public function set_mandate_id( $mandate_id ) {
		$this->mandate_id = $mandate_id;
	}

	/**
	 * Has chargebacks.
	 *
	 * @link https://github.com/mollie/mollie-api-php/blob/v2.24.0/src/Resources/Payment.php#L358-L366
	 * @return bool True if payment has chargebacks, false otherwise.
	 */
	public function has_chargebacks() {
		return ! empty( $this->links->chargebacks );
	}

	/**
	 * Get payment method specific details.
	 *
	 * @return PaymentDetails|null
	 */
	public function get_details() {
		return $this->details;
	}

	/**
	 * Set payment method specific details.
	 *
	 * @param PaymentDetails|null $details Details.
	 * @return void
	 */
	public function set_details( PaymentDetails $details = null ) {
		$this->details = $details;
	}

	/**
	 * Get amount refunded.
	 *
	 * @return Amount|null
	 */
	public function get_amount_refunded() {
		return $this->amount_refunded;
	}

	/**
	 * Set amount refunded.
	 *
	 * @param Amount|null $amount_refunded Amount refunded.
	 * @return void
	 */
	public function set_amount_refunded( Amount $amount_refunded = null ) {
		$this->amount_refunded = $amount_refunded;
	}

	/**
	 * Get expires at.
	 *
	 * @return DateTimeInterface
	 */
	public function get_expires_at() {
		return $this->expires_at;
	}

	/**
	 * Set expires at.
	 *
	 * @param DateTimeInterface $expires_at Expiry date.
	 * @return void
	 */
	public function set_expires_at( DateTimeInterface $expires_at ) {
		$this->expires_at = $expires_at;
	}

	/**
	 * Get links.
	 *
	 * @return object
	 */
	public function get_links() {
		return $this->links;
	}

	/**
	 * Set links.
	 *
	 * @param object $links Links.
	 * @return void
	 */
	public function set_links( $links ) {
		$this->links = $links;
	}

	/**
	 * Create payment from JSON.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/get-payment
	 * @param object $json JSON object.
	 * @return Payment
	 * @throws \JsonSchema\Exception\ValidationException Throws JSON schema validation exception when JSON is invalid.
	 */
	public static function from_json( $json ) {
		$validator = new \JsonSchema\Validator();

		$validator->validate(
			$json,
			(object) array(
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/payment.json' ),
			),
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.
		$payment = new Payment(
			$json->id,
			$json->mode,
			new \DateTimeImmutable( $json->createdAt ),
			$json->status,
			Amount::from_json( $json->amount ),
			$json->description,
			$json->redirectUrl,
			$json->method,
			$json->metadata,
			$json->profileId,
			$json->sequenceType,
			$json->_links
		);

		if ( \property_exists( $json, 'expiresAt' ) ) {
			$payment->set_expires_at( new \DateTimeImmutable( $json->expiresAt ) );
		}

		if ( \property_exists( $json, 'locale' ) ) {
			$payment->set_locale( $json->locale );
		}

		if ( \property_exists( $json, 'customerId' ) ) {
			$payment->set_customer_id( $json->customerId );
		}

		if ( \property_exists( $json, 'mandateId' ) ) {
			$payment->set_mandate_id( $json->mandateId );
		}

		if ( \property_exists( $json, 'details' ) ) {
			$payment->set_details( PaymentDetails::from_json( (string) $payment->get_method(), $json->details ) );
		}

		if ( \property_exists( $json, 'amountRefunded' ) ) {
			$refunded_amount = Amount::from_json( $json->amountRefunded );

			$payment->set_amount_refunded( $refunded_amount );
		}

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		return $payment;
	}
}
