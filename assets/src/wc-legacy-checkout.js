/* global Mollie */
/* eslint-env jquery */

/**
 * Pronamic Pay Mollie WooCommerce legacy checkout form controller class
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Classes
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Using_classes
 */
class PronamicPayMollieWooCommerceLegacyCheckoutFormController {
	/**
	 * Construct Pronamic Pay Mollie WooCommerce legacy checkout form controller.
	 *
	 * @param {jQuery}      jQuery The jQuery library.
	 * @param {HTMLElement} body   Body element.
	 * @param {HTMLElement} form   WooCommerce legacy checkout form element.
	 */
	constructor( jQuery, body, form ) {
		this.jQuery = jQuery;
		this.body = body;
		this.form = form;
	}

	/**
	 * Setup.
	 */
	setup() {
		this.jQuery( this.body ).on( 'init_checkout', () =>
			this.initCheckout()
		);
	}

	/**
	 * Init checkout.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/8.3.0/plugins/woocommerce/client/legacy/js/frontend/checkout.js#L56-L59
	 */
	initCheckout() {
		const cardElement = this.form.querySelector(
			'.pronamic-pay-mollie-card-field'
		);

		if ( null === cardElement ) {
			return;
		}

		const mollieProfileId = cardElement.dataset.mollieProfileId;
		const mollieOptions = JSON.parse( cardElement.dataset.mollieOptions );

		this.mollie = Mollie( mollieProfileId, mollieOptions );

		this.checkoutPlaceOrderListener = ( event, wcCheckoutForm ) =>
			this.checkoutPlaceOrder( event, wcCheckoutForm );

		this.jQuery( this.form ).on(
			'checkout_place_order',
			this.checkoutPlaceOrderListener
		);

		this.jQuery( this.body ).on( 'updated_checkout', () =>
			this.updatedCheckout()
		);

		this.mollieCardComponent = this.mollie.createComponent( 'card' );
	}

	/**
	 * Updated checkout.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/8.3.0/plugins/woocommerce/client/legacy/js/frontend/checkout.js#L428-L429
	 */
	updatedCheckout() {
		if ( this.cardElement ) {
			this.mollieCardComponent.unmount();
		}

		this.cardElement = this.form.querySelector(
			'.pronamic-pay-mollie-card-field'
		);

		if ( null === this.cardElement ) {
			return;
		}

		this.mollieCardComponent.mount( this.cardElement );
	}

	/**
	 * Checkout place order.
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/8.3.0/plugins/woocommerce/client/legacy/js/frontend/checkout.js#L478-L480
	 * @param {jQuery.Event} event          A `jQuery.Event` object.
	 * @param {Object}       wcCheckoutForm WooCommerce checkout form object.
	 */
	checkoutPlaceOrder( event, wcCheckoutForm ) {
		if (
			'pronamic_pay_credit_card' !== wcCheckoutForm.get_payment_method()
		) {
			return true;
		}

		this.mollie
			.createToken()
			.then( ( result ) => this.processTokenResponse( result ) );

		return false;
	}

	/**
	 * Process token response.
	 *
	 * @param {Object} result Mollie create token repsonse object.
	 */
	processTokenResponse( result ) {
		if ( result.error ) {
			return;
		}

		const tokenElement = document.getElementById(
			'pronamic_pay_mollie_card_token'
		);

		if ( tokenElement ) {
			tokenElement.value = result.token;
		}

		this.jQuery( this.form ).off(
			'checkout_place_order',
			this.checkoutPlaceOrderListener
		);

		this.jQuery( this.form ).submit();

		this.jQuery( this.form ).on(
			'checkout_place_order',
			this.checkoutPlaceOrderListener
		);
	}
}

/**
 * Initialization.
 */
( function () {
	if ( ! jQuery ) {
		return;
	}

	if ( ! document.forms.checkout ) {
		return;
	}

	const controller =
		new PronamicPayMollieWooCommerceLegacyCheckoutFormController(
			jQuery,
			document.body,
			document.forms.checkout
		);

	controller.setup();
} )();
