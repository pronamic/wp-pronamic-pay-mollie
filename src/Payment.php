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
use DateTimeImmutable;

/**
 * Payment class
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
	 * Amount charged back.
	 *
	 * @var Amount|null
	 */
	private $amount_charged_back;

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
	 * @param mixed             $metadata      Metadata.
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
	 * Get mode.
	 *
	 * @return string
	 */
	public function get_mode() {
		return $this->mode;
	}

	/**
	 * Get created at.
	 *
	 * @return DateTimeInterface
	 */
	public function get_created_at() {
		return $this->created_at;
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
	 * Get amount.
	 *
	 * @return Amount
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get redirect URL.
	 *
	 * @return string|null
	 */
	public function get_redirect_url() {
		return $this->redirect_url;
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
	 * Get amount charged back.
	 *
	 * @return Amount|null
	 */
	public function get_amount_charged_back() {
		return $this->amount_charged_back;
	}

	/**
	 * Set amount charged back.
	 *
	 * @param Amount|null $amount_charged_back Amount charged back.
	 * @return void
	 */
	public function set_amount_charged_back( Amount $amount_charged_back = null ) {
		$this->amount_charged_back = $amount_charged_back;
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
	 * Get metadata.
	 *
	 * @return mixed
	 */
	public function get_metadata() {
		return $this->metadata;
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
			(object) [
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/payment.json' ),
			],
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		$object_access = new ObjectAccess( $json );

		$payment = new Payment(
			$object_access->get_property( 'id' ),
			$object_access->get_property( 'mode' ),
			new DateTimeImmutable( $object_access->get_property( 'createdAt' ) ),
			$object_access->get_property( 'status' ),
			Amount::from_json( $object_access->get_property( 'amount' ) ),
			$object_access->get_property( 'description' ),
			$object_access->get_property( 'redirectUrl' ),
			$object_access->get_property( 'method' ),
			$object_access->get_property( 'metadata' ),
			$object_access->get_property( 'profileId' ),
			$object_access->get_property( 'sequenceType' ),
			$object_access->get_property( '_links' ),
		);

		if ( $object_access->has_property( 'expiresAt' ) ) {
			$payment->set_expires_at( new DateTimeImmutable( $object_access->get_property( 'expiresAt' ) ) );
		}

		$payment->set_customer_id( $object_access->get_optional( 'customerId' ) );
		$payment->set_mandate_id( $object_access->get_optional( 'mandateId' ) );

		if ( $object_access->has_property( 'details' ) ) {
			$payment->set_details( PaymentDetails::from_json( (string) $payment->get_method(), $object_access->get_property( 'details' ) ) );
		}

		if ( $object_access->has_property( 'amountRefunded' ) ) {
			$payment->set_amount_refunded( Amount::from_json( $object_access->get_property( 'amountRefunded' ) ) );
		}

		if ( $object_access->has_property( 'amountChargedBack' ) ) {
			$payment->set_amount_charged_back( Amount::from_json( $object_access->get_property( 'amountChargedBack' ) ) );
		}

		return $payment;
	}
}
