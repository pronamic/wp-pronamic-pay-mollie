<?php
/**
 * Refund
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateTimeInterface;

/**
 * Refund class
 */
class Refund extends BaseResource {
	/**
	 * The amount refunded to your customer with this refund.
	 *
	 * @var Amount
	 */
	private $amount;

	/**
	 * The description of the refund that may be shown to your customer,
	 * depending on the payment method used.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Since refunds may not be instant for certain payment methods,
	 * the refund carries a status field.
	 *
	 * @var string
	 */
	private $status;

	/**
	 * The unique identifier of the payment this refund was created for.
	 * For example: tr_7UhSN1zuXS.
	 *
	 * @var string
	 */
	private $payment_id;

	/**
	 * The date and time the refund was issued.
	 *
	 * @var DateTimeInterface
	 */
	private $created_at;

	/**
	 * Construct chargeback.
	 *
	 * @param string            $id          Identifier.
	 * @param Amount            $amount      Amount.
	 * @param string            $description Description.
	 * @param string            $status      Status.
	 * @param string            $payment_id  Mollie payment ID.
	 * @param DateTimeInterface $created_at  Created at.
	 */
	public function __construct( $id, Amount $amount, $description, $status, $payment_id, DateTimeInterface $created_at ) {
		parent::__construct( $id );

		$this->amount      = $amount;
		$this->description = $description;
		$this->status      = $status;
		$this->payment_id  = $payment_id;
		$this->created_at  = $created_at;
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
	 * Get status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Get Mollie payment ID.
	 *
	 * @return string
	 */
	public function get_payment_id() {
		return $this->payment_id;
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
	 * Create chargeback from JSON.
	 *
	 * @link https://docs.mollie.com/reference/v2/refunds-api/get-refund
	 * @param object $json JSON object.
	 * @return Refund
	 * @throws \JsonSchema\Exception\ValidationException Throws JSON schema validation exception when JSON is invalid.
	 */
	public static function from_json( $json ) {
		$validator = new \JsonSchema\Validator();

		$validator->validate(
			$json,
			(object) [
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/refund.json' ),
			],
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		$object_access = new ObjectAccess( $json );

		$refund = new Refund(
			$object_access->get_property( 'id' ),
			Amount::from_json( $object_access->get_property( 'amount' ) ),
			$object_access->get_property( 'description' ),
			$object_access->get_property( 'status' ),
			$object_access->get_property( 'paymentId' ),
			new \DateTimeImmutable( $object_access->get_property( 'createdAt' ) )
		);

		return $refund;
	}
}
