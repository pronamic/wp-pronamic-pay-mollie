<?php
/**
 * Payment
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateTimeInterface;

/**
 * Payment
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class Payment {
	/**
	 * The identifier uniquely referring to this payment.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * The payment’s status.
	 *
	 * @var string
	 */
	private $status;

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
	 * Construct payment.
	 *
	 * @param string            $id            Identifier.
	 * @param string            $mode          Mode.
	 * @param DateTimeInterface $created_at    Created at.
	 * @param string            $status        Status.
	 * @param Amount            $amount        Amount.
	 * @param string            $description   Description.
	 * @param string|null       $redirect_url  Redirect URL.
	 * @param string            $method        Method.
	 * @param string            $metadata      Metadata.
	 * @param string            $locale        Locale.
	 * @param string            $profile_id    Profile ID.
	 * @param string            $sequence_type Sequence type.
	 * @param object            $links         Links.
	 */
	public function __construct( $id, $mode, DateTimeInterface $created_at, $status, Amount $amount, $description, $redirect_url, $method, $metadata, $locale, $profile_id, $sequence_type, $links ) {
		$this->id            = $id;
		$this->mode          = $mode;
		$this->created_at    = $created_at;
		$this->status        = $status;
		$this->amount        = $amount;
		$this->description   = $description;
		$this->redirect_url  = $redirect_url;
		$this->method        = $method;
		$this->metadata      = $metadata;
		$this->locale        = $locale;
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
	 * Create payment from JSON.
	 *
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
			$json->locale,
			$json->profileId,
			$json->sequenceType,
			$json->_links
		);

		if ( \property_exists( $json, 'customerId' ) ) {
			$payment->set_customer_id( $json->customerId );
		}

		if ( \property_exists( $json, 'mandateId' ) ) {
			$payment->set_mandate_id( $json->mandateId );
		}

		return $payment;
	}
}
