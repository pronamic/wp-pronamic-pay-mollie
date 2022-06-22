<?php
/**
 * Chargeback
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateTimeInterface;

/**
 * Chargeback class
 */
class Chargeback extends BaseResource {
	/**
	 * The amount charged back by the consumer.
	 *
	 * @var Amount
	 */
	private $amount;

	/**
	 * The date and time the chargeback was issued.
	 *
	 * @var DateTimeInterface
	 */
	private $created_at;

	/**
	 * Construct chargeback.
	 *
	 * @param string            $id            Identifier.
	 * @param Amount            $amount        Amount.
	 * @param DateTimeInterface $created_at    Created at.
	 */
	public function __construct( $id, Amount $amount, DateTimeInterface $created_at ) {
		parent::__construct( $id );

		$this->amount     = $amount;
		$this->created_at = $created_at;
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
	 * Get chargeback amount.
	 *
	 * @return Amount
	 */
	public function get_amount() {
		return $this->amount;
	}

	/**
	 * Create chargeback from JSON.
	 *
	 * @link https://docs.mollie.com/reference/v2/chargebacks-api/get-chargeback
	 * @param object $json JSON object.
	 * @return Chargeback
	 * @throws \JsonSchema\Exception\ValidationException Throws JSON schema validation exception when JSON is invalid.
	 */
	public static function from_json( $json ) {
		$validator = new \JsonSchema\Validator();

		$validator->validate(
			$json,
			(object) [
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/chargeback.json' ),
			],
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		$object_access = new ObjectAccess( $json );

		$chargeback = new Chargeback(
			$object_access->get_property( 'id' ),
			Amount::from_json( $object_access->get_property( 'amount' ) ),
			new \DateTimeImmutable( $object_access->get_property( 'createdAt' ) )
		);

		return $chargeback;
	}
}
