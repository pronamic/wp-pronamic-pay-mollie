<?php
/**
 * Mollie refund request.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie refund request
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.3.0
 * @since   2.3.0
 */
class RefundRequest {
	/**
	 * The amount to refund. For some payments, it can be up to €25.00 more
	 * than the original transaction amount.
	 *
	 * @link https://docs.mollie.com/reference/v2/refunds-api/create-refund
	 * @var Amount
	 */
	public $amount;

	/**
	 * The description of the refund you are creating. This will be shown to the consumer
	 * on their card or bank statement when possible. Max. 140 characters.
	 *
	 * @link https://docs.mollie.com/reference/v2/refunds-api/create-refund
	 * @var string|null
	 */
	public $description;

	/**
	 * Provide any data you like in JSON notation, and we will save the data alongside the payment.
	 * Whenever you fetch the refund with our API, we'll also include the metadata. You can use up
	 * to 1kB of JSON.
	 *
	 * @link https://www.mollie.com/nl/docs/reference/payments/create
	 * @link https://en.wikipedia.org/wiki/Metadata
	 * @var mixed|null
	 */
	private $metadata;

	/**
	 * Construct Mollie refund request object.
	 *
	 * @param Amount $amount The amount that you want to refund.
	 * @retrun void
	 */
	public function __construct( $amount ) {
		$this->amount = $amount;
	}

	/**
	 * Get description.
	 *
	 * @return string|null
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Set description.
	 *
	 * @param string|null $description Description.
	 * @return void
	 */
	public function set_description( $description ) {
		$this->description = $description;
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
	 * Get array of this Mollie refund request object.
	 *
	 * @return array<string,null|string|object>
	 */
	public function get_array() {
		$array = array(
			'amount'      => $this->amount->get_json(),
			'description' => $this->description,
			'metadata'    => $this->metadata,
		);

		/*
		 * Array filter will remove values NULL, FALSE and empty strings ('')
		 */
		$array = array_filter( $array );

		return $array;
	}
}
