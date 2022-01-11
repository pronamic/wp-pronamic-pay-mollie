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
 * Chargeback
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
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
			(object) array(
				'$ref' => 'file://' . realpath( __DIR__ . '/../json-schemas/chargeback.json' ),
			),
			\JsonSchema\Constraints\Constraint::CHECK_MODE_EXCEPTIONS
		);

		$chargeback = new Chargeback(
			$json->id,
			Amount::from_json( $json->amount ),
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.
			new \DateTimeImmutable( $json->createdAt )
		);

		return $chargeback;
	}
}
