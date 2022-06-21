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

use JsonSerializable;

/**
 * Line class
 */
class Line implements JsonSerializable {
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
	 * Set type.
	 *
	 * @param string|null $type Type.
	 */
	public function set_type( ?string $type ) : void {
		$this->type = $type;
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
	 * Set discount amount, should not contain any tax.
	 *
	 * @param Amount|null $discount_amount Discount amount.
	 */
	public function set_discount_amount( ?Amount $discount_amount = null ) : void {
		$this->discount_amount = $discount_amount;
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
	 * Set image URL.
	 *
	 * @param string|null $image_url Image url.
	 */
	public function set_image_url( ?string $image_url ) : void {
		$this->image_url = $image_url;
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
	 * JSON serialize.
	 *
	 * @return mixed
	 */
	public function jsonSerialize() {
		$json_builder = new JsonBuilder();

		$json_builder->set_optional( 'type', $this->type );
		$json_builder->set_optional( 'category', $this->category );
		$json_builder->set_value( 'name', $this->name );
		$json_builder->set_value( 'quantity', $this->quantity );
		$json_builder->set_value( 'unitPrice', $this->unit_price->jsonSerialize() );
		$json_builder->set_optional( 'discountAmount', null === $this->discount_amount ? null : $this->discount_amount->jsonSerialize() );
		$json_builder->set_optional( 'totalAmount', $this->total_amount->jsonSerialize() );
		$json_builder->set_value( 'vatRate', $this->vat_rate );
		$json_builder->set_value( 'vatAmount',  $this->vat_amount->jsonSerialize() );
		$json_builder->set_optional( 'sku', $this->sku );
		$json_builder->set_optional( 'imageUrl', $this->image_url );
		$json_builder->set_optional( 'productUrl', $this->product_url );

		return $json_builder->jsonSerialize();
	}
}
