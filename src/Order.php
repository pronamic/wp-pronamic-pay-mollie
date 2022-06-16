<?php
/**
 * Order
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateTimeInterface;

/**
 * Order
 *
 * @author  Reüel van der Steege
 * @version 4.3.0
 * @since   4.3.0
 */
class Order extends BaseResource {
	/**
	 * The identifier referring to the profile this payment was created on.
	 *
	 * @var string
	 */
	private string $profile_id;

	/**
	 * Lines.
	 *
	 * @var Lines
	 */
	private Lines $lines;

	/**
	 * The payment method used for this payment, either forced on creation by specifying the method parameter, or chosen by the customer on our payment method selection screen.
	 *
	 * @var string
	 */
	private string $method;

	/**
	 * The mode used to create this payment. Mode determines whether a payment is real (live mode) or a test payment.
	 *
	 * @var string
	 */
	private string $mode;

	/**
	 * The amount of the payment, e.g. {"currency":"EUR", "value":"100.00"} for a €100.00 payment.
	 *
	 * @var Amount
	 */
	private Amount $amount;

	/**
	 * The payment’s status.
	 *
	 * @var string
	 */
	private string $status;

	/**
	 * Billing address.
	 *
	 * @var Address
	 */
	private Address $billing_address;

	/**
	 * Shopper county must match billing country.
	 *
	 * @var bool
	 */
	private bool $shopper_country_must_match_billing_country;

	/**
	 * Order number.
	 *
	 * @var string
	 */
	private string $order_number;

	/**
	 * Shipping address.
	 *
	 * @var Address
	 */
	private Address $shipping_address;

	/**
	 * The customer’s locale, either forced on creation by specifying the `locale` parameter, or detected by us during checkout. Will be a full locale, for example `nl_NL`.
	 *
	 * @var string
	 */
	private string $locale;

	/**
	 * The optional metadata you provided upon payment creation. Metadata can for example be used to link an order to a payment.
	 *
	 * @var string|array|null
	 */
	private $metadata;

	/**
	 * The URL your customer will be redirected to after completing or canceling the payment process.
	 *
	 * @var string|null
	 */
	private ?string $redirect_url;

	/**
	 * The order’s date and time of creation, in ISO 8601 format.
	 *
	 * @var DateTimeInterface
	 */
	private DateTimeInterface $created_at;

	/**
	 * For bank transfer payments, the `_links` object will contain some additional URL objects relevant to the payment.
	 *
	 * @var object
	 */
	private $links;

	/**
	 * Payments.
	 *
	 * @var Payment[]|null
	 */
	private array $payments;

	/**
	 * Construct order.
	 *
	 * @param string            $id                                         Identifier.
	 * @param string            $profile_id                                 Profile ID.
	 * @param Lines             $lines                                      Lines.
	 * @param string|null       $method                                     Method.
	 * @param string            $mode                                       Mode.
	 * @param Amount            $amount                                     Amount.
	 * @param string            $status                                     Status.
	 * @param Address           $billing_address                            Billing address.
	 * @param bool              $shopper_country_must_match_billing_country Shopper country must match billing country.
	 * @param string            $order_number                               Order number.
	 * @param Address           $shipping_address                           Shipping address.
	 * @param string            $locale                                     Locale.
	 * @param string            $metadata                                   Metadata.
	 * @param string|null       $redirect_url                               Redirect URL.
	 * @param DateTimeInterface $created_at                                 Created at.
	 * @param object            $links                                      Links.
	 */
	public function __construct( $id, $profile_id, $lines, $method, $mode, $amount, $status, $billing_address, $shopper_country_must_match_billing_country, $order_number, $shipping_address, $locale, $metadata, $redirect_url, $created_at, $links ) {
		parent::__construct( $id );

		$this->profile_id                                 = $profile_id;
		$this->lines                                      = $lines;
		$this->method                                     = $method;
		$this->mode                                       = $mode;
		$this->amount                                     = $amount;
		$this->status                                     = $status;
		$this->billing_address                            = $billing_address;
		$this->shopper_country_must_match_billing_country = $shopper_country_must_match_billing_country;
		$this->order_number                               = $order_number;
		$this->shipping_address                           = $shipping_address;
		$this->locale                                     = $locale;
		$this->metadata                                   = $metadata;
		$this->redirect_url                               = $redirect_url;
		$this->created_at                                 = $created_at;
		$this->links                                      = $links;
	}

	/**
	 * Get embedded payments.
	 *
	 * @return Payment[]|null
	 */
	public function get_payments() : ?array {
		return $this->payments;
	}

	/**
	 * Set embedded payments.
	 *
	 * @param array|null $payments Payments.
	 */
	public function set_payments( ?array $payments ) : void {
		$this->payments = $payments;
	}

	/**
	 * Create order from JSON.
	 *
	 * @link https://docs.mollie.com/reference/v2/orders-api/get-order
	 * @param object $json JSON object.
	 * @return Payment
	 * @throws \JsonSchema\Exception\ValidationException Throws JSON schema validation exception when JSON is invalid.
	 */
	public static function from_json( $json ) {
		$validator = new \JsonSchema\Validator();

		$validator->validate(
			$json,
			(object) [
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/order.json' ),
			],
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.
		$order = new Order(
			$json->id,
			$json->profileId,
			Lines::from_json( $json->lines ),
			$json->method,
			$json->mode,
			Amount::from_json( $json->amount ),
			$json->status,
			Address::from_json( $json->billingAddress ),
			$json->shopperCountryMustMatchBillingCountry,
			$json->orderNumber,
			Address::from_json( $json->shippingAddress ),
			$json->locale,
			$json->metadata,
			$json->redirectUrl,
			new \DateTimeImmutable( $json->createdAt ),
			$json->_links
		);

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		if ( property_exists( $json, '_embedded' ) ) {
			if ( property_exists( $json->_embedded, 'payments' ) ) {
				$payments = array_map(
					/**
					 * Get JSON for payments.
					 *
					 * @param object $payment Payment.
					 * @return Payment
					 */
					function( object $payment ) {
						return Payment::from_json( $payment );
					},
					$json->_embedded->payments
				);

				$order->set_payments( $payments );
			}
		}

		return $order;
	}
}
