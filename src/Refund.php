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
 * Refund
 *
 * @author  Reüel van der Steege
 * @version 2.3.0
 * @since   2.3.0
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
	 * The optional metadata you provided upon refund creation. Metadata can for
	 * example be used to link an bookkeeping ID to a refund.
	 *
	 * @var mixed
	 */
	private $metadata;

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
	 * Get metadata.
	 *
	 * @return mixed
	 */
	public function get_metadata() {
		return $this->metadata;
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
			(object) array(
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/refund.json' ),
			),
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		$refund = new Refund(
			$json->id,
			Amount::from_json( $json->amount ),
			$json->description,
			$json->status,
			$json->paymentId,
			new \DateTimeImmutable( $json->createdAt )
		);

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $refund;
	}
}
