<?php
/**
 * Mollie card field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Html\Element;
use Pronamic\WordPress\Pay\Fields\Field;

/**
 * Mollie card field class
 */
class CardField extends Field {
	/**
	 * Gatweay.
	 * 
	 * @var Gateway
	 */
	private $gateway;

	/**
	 * Construct card field.
	 * 
	 * @param string  $id      ID.
	 * @param Gateway $gateway Gateway.
	 */
	public function __construct( $id, Gateway $gateway ) {
		parent::__construct( $id );

		$this->gateway = $gateway;
	}

	/**
	 * Setup field.
	 */
	public function setup(): void {
		\wp_register_script(
			'mollie.js',
			'https://js.mollie.com/v1/mollie.js',
			[],
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is part of URL.
			null,
			false
		);

		$file = '../css/components.css';

		\wp_register_style(
			'pronamic-pay-mollie-components',
			\plugins_url( $file, __FILE__ ),
			[],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
		);

		$file = '../js/src/mollie.js';

		\wp_register_script(
			'pronamic-pay-mollie',
			\plugins_url( $file, __FILE__ ),
			[ 'mollie.js' ],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
			true
		);
	}

	/**
	 * Get element.
	 * 
	 * @return Element|null
	 */
	protected function get_element() {
		\wp_enqueue_script( 'pronamic-pay-mollie' );

		\wp_enqueue_style( 'pronamic-pay-mollie' );

		$element = new Element( 'div' );

		$element->children[] = new Element(
			'div',
			[
				'id' => 'card',
			]
		); 

		$element->children[] = new Element(
			'input',
			[
				'id'   => $this->get_id(),
				'name' => $this->get_id(),
				'type' => 'hidden',
			]
		);

		return $element;
	}

	/**
	 * Ouput.
	 *
	 * @return void
	 */
	public function output() {
		parent::output();

		try {
			$profile_id = $this->gateway->get_profile_id();
		} catch ( \Exception $e ) {
			return;
		}

		$locale_transformer = new LocaleTransformer();

		$data = [
			'elementId' => $this->get_id(),
			'profileId' => $profile_id,
			'options'   => [
				'locale'   => $locale_transformer->transform_wp_to_mollie( \get_locale() ),
				'testmode' => 'test' === $this->gateway->get_mode(),
			],
			'mount'     => '#card',
		];

		?>
		<script>
			window.pronamicPayMollieFields = window.pronamicPayMollieFields || [];

			window.pronamicPayMollieFields.push( <?php echo \wp_json_encode( $data ); ?> );
		</script>
		<?php
	}

	/**
	 * Serialize to JSON.
	 */
	public function jsonSerialize(): array {
		$data = parent::jsonSerialize();

		$data['type'] = 'html';

		return $data;
	}
}
