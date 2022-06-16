<?php
/**
 * Line
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use InvalidArgumentException;

/**
 * Line.
 *
 * @author  Reüel van der Steege
 * @version 4.3.0
 * @since   4.3.0
 */
class Line {
	/**
	 * The order line’s unique identifier.
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * The type of product bought, for example, a physical or a digital product.
	 *
	 * @see LineType
	 * @var string|null
	 */
	private $type;

	/**
	 * The category of product bought.
	 *
	 * Optional, but required in at least one of the lines to accept `voucher` payments.
	 *
	 * @var string|null
	 */
	private $category;

	/**
	 * Name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Quantity.
	 *
	 * @var int
	 */
	private $quantity;

	/**
	 * The price of a single item including VAT in the order line.
	 *
	 * @var Amount
	 */
	private $unit_price;

	/**
	 * Any discounts applied to the order line. For example, if you have a two-for-one sale,
	 * you should pass the amount discounted as a positive amount.
	 *
	 * @var Amount|null
	 */
	private $discount_amount;

	/**
	 * The total amount of the line, including VAT and discounts. Adding all `totalAmount`
	 * values together should result in the same amount as the amount top level property.
	 *
	 * The total amount should match the following formula: (unitPrice × quantity) - discountAmount
	 *
	 * @var Amount
	 */
	private $total_amount;

	/**
	 * The VAT rate applied to the order line, for example "21.00" for 21%. The `vatRate` should
	 * be passed as a string and not as a float to ensure the correct number of decimals are passed.
	 *
	 * @var string
	 */
	private $vat_rate;

	/**
	 * The amount of value-added tax on the line. The `totalAmount` field includes VAT, so
	 * the `vatAmount` can be calculated with the formula `totalAmount × (vatRate / (100 + vatRate))`.
	 *
	 * @var Amount
	 */
	private $vat_amount;

	/**
	 * SKU.
	 *
	 * @var string|null
	 */
	private $sku;

	/**
	 * Image url.
	 *
	 * @var string|null
	 */
	private $image_url;

	/**
	 * Product URL.
	 *
	 * @var string|null
	 */
	private $product_url;

	/**
	 * Metadata
	 *
	 * @var string|object|null
	 */
	private $metadata;

	/**
	 * Line constructor.
	 *
	 * @param string $name         Description of the order line.
	 * @param int    $quantity     Quantity.
	 * @param Amount $unit_price   Unit price.
	 * @param Amount $total_amount Total amount, including VAT and  discounts.
	 * @param string $vat_rate     VAT rate.
	 * @param Amount $vat_amount   Value-added tax amount.
	 */
	public function __construct( string $name, int $quantity, Amount $unit_price, Amount $total_amount, string $vat_rate, Amount $vat_amount ) {
		$this->name         = $name;
		$this->quantity     = $quantity;
		$this->unit_price   = $unit_price;
		$this->total_amount = $total_amount;
		$this->vat_rate     = $vat_rate;
		$this->vat_amount   = $vat_amount;
	}

	/**
	 * Get the Mollie id / identifier of this payment line.
	 */
	public function get_id() : ?string {
		return $this->id;
	}

	/**
	 * Set the Mollie id / identifier of this payment line.
	 *
	 * @param string|null $id Number.
	 */
	public function set_id( ?string $id ) : void {
		$this->id = $id;
	}

	/**
	 * Get type.
	 */
	public function get_type(): ?string {
		return $this->type;
	}

	/**
	 * Set type.
	 *
	 * @param string|null $type Type.
	 */
	public function set_type( ?string $type ) : void {
		$this->type = $type;
	}

	/**
	 * Get category.
	 */
	public function get_category() : ?string {
		return $this->category;
	}

	/**
	 * Set category.
	 *
	 * @param null|string $category Product category.
	 */
	public function set_category( ?string $category ) : void {
		$this->category = $category;
	}

	/**
	 * Get name.
	 */
	public function get_name() : string {
		return $this->name;
	}

	/**
	 * Get quantity.
	 */
	public function get_quantity() : int {
		return $this->quantity;
	}

	/**
	 * Get unit price.
	 */
	public function get_unit_price() : Amount {
		return $this->unit_price;
	}

	/**
	 * Get discount amount, should not contain any tax.
	 */
	public function get_discount_amount() : Amount {
		return $this->discount_amount;
	}

	/**
	 * Set discount amount, should not contain any tax.
	 *
	 * @param Amount|null $discount_amount Discount amount.
	 */
	public function set_discount_amount( ?Amount $discount_amount = null ) : void {
		$this->discount_amount = $discount_amount;
	}

	/**
	 * Get total amount.
	 *
	 * @return Amount
	 */
	public function get_total_amount() : Amount {
		return $this->total_amount;
	}

	/**
	 * Get VAT rate.
	 *
	 * @return string
	 */
	public function get_vat_rate() : string {
		return $this->vat_rate;
	}

	/**
	 * Get value-added tax amount.
	 *
	 * @return Amount
	 */
	public function get_vat_amount() : Amount {
		return $this->vat_amount;
	}

	/**
	 * Get the SKU of this payment line.
	 */
	public function get_sku() : ?string {
		return $this->sku;
	}

	/**
	 * Set the SKU of this payment line.
	 *
	 * @param string|null $sku SKU.
	 */
	public function set_sku( ?string $sku ) : void {
		$this->sku = $sku;
	}

	/**
	 * Get image URL.
	 */
	public function get_image_url() : ?string {
		return $this->image_url;
	}

	/**
	 * Set image URL.
	 *
	 * @param string|null $image_url Image url.
	 */
	public function set_image_url( ?string $image_url ) : void {
		$this->image_url = $image_url;
	}

	/**
	 * Get product URL.
	 */
	public function get_product_url() : ?string {
		return $this->product_url;
	}

	/**
	 * Set product URL.
	 *
	 * @param string|null $product_url Product URL.
	 */
	public function set_product_url( ?string $product_url = null ) : void {
		$this->product_url = $product_url;
	}

	/**
	 * Get metadata.
	 */
	public function get_metadata() {
		return $this->metadata;
	}

	/**
	 * Set metadata.
	 *
	 * @param string|object|null $metadata Metadata.
	 */
	public function set_metadata( $metadata = null ) : void {
		$this->metadata = $metadata;
	}

	/**
	 * Create payment line from object.
	 *
	 * @param mixed $json JSON.
	 * @return Line
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) : Line {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		$object_access = new ObjectAccess( $json );

		$line = new self(
			$object_access->get_property( 'name' ),
			$object_access->get_property( 'quantity' ),
			Amount::from_json( $object_access->get_property( 'unitPrice' ) ),
			Amount::from_json( $object_access->get_property( 'totalAmount' ) ),
			$object_access->get_property( 'vatRate' ),
			Amount::from_json( $object_access->get_property( 'vatAmount' ) ),
		);

		if ( $object_access->has_property( 'id' ) ) {
			$line->set_id( $object_access->get_property( 'id' ) );
		}

		if ( $object_access->has_property( 'type' ) ) {
			$line->set_type( $object_access->get_property( 'type' ) );
		}

		if ( $object_access->has_property( 'category' ) ) {
			$line->set_category( $object_access->get_property( 'category' ) );
		}

		if ( $object_access->has_property( 'discountAmount' ) ) {
			$line->set_discount_amount( Amount::from_json( $object_access->get_property( 'discountAmount' ) ) );
		}

		if ( $object_access->has_property( 'sku' ) ) {
			$line->set_sku( $object_access->get_property( 'sku' ) );
		}

		if ( $object_access->has_property( 'imageUrl' ) ) {
			$line->set_image_url( $object_access->get_property( 'imageUrl' ) );
		}

		if ( $object_access->has_property( 'productUrl' ) ) {
			$line->set_product_url( $object_access->get_property( 'productUrl' ) );
		}

		if ( $object_access->has_property( 'metadata' ) ) {
			$line->set_metadata( $object_access->get_property( 'metadata' ) );
		}

		return $line;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() : object {
		$properties = [
			'id'             => $this->id,
			'type'           => $this->type,
			'category'       => $this->category,
			'name'           => $this->name,
			'quantity'       => $this->quantity,
			'unitPrice'      => $this->unit_price->jsonSerialize(),
			'discountAmount' => null === $this->discount_amount ? null : $this->discount_amount->jsonSerialize(),
			'totalAmount'    => $this->total_amount->jsonSerialize(),
			'vatRate'        => $this->vat_rate,
			'vatAmount'      => $this->vat_amount->jsonSerialize(),
			'sku'            => $this->sku,
			'imageUrl'       => $this->image_url,
			'productUrl'     => $this->product_url,
			'metadata'       => $this->metadata,
		];

		$properties = array_filter( $properties );

		return (object) $properties;
	}

	/**
	 * Create string representation of the payment line.
	 *
	 * @return string
	 */
	public function __toString() {
		$parts = [
			$this->id,
			$this->quantity,
		];

		$parts = array_map( 'strval', $parts );

		$parts = array_map( 'trim', $parts );

		$parts = array_filter( $parts );

		$string = implode( ' - ', $parts );

		return $string;
	}
}
