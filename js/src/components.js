/* global Mollie */

'use strict';

/**
 * Mollie Components.
 *
 * @link https://docs.mollie.com/components/overview
 */
( () => {
	function initMollieComponents() {
		const $elements = document.querySelectorAll( '.pronamic_pay_mollie_components' );

		$elements.forEach( ( $element ) => {
			const data = $element.dataset;

			if ( ! ( "mollie-profile-id" in data ) ) {
				throw new Error( 'No Mollie profile ID in element dataset. Unable to load Mollie Components.' );

				return;
			}

			// Initialize Mollie object.
			const mollie = Mollie(
				data['mollie-profile-id'],
				{
					locale: data['mollie-locale'] ?? null,
					testmode: ( "mollie-testmode" in data ),
				}
			);

			// Create components.
			const components = [
				{
					id: 'card-number',
					label: 'Card Number',
					component: 'cardNumber'
				},
				{
					id: 'card-holder',
					label: 'Card Holder',
					component: 'cardHolder'
				},
				{
					id: 'expiry-date',
					label: 'Expiry Date',
					component: 'expiryDate'
				},
				{
					id: 'verification-code',
					label: 'Verification Code',
					component: 'verificationCode'
				}
			];

			components.forEach( ( element ) => {
				let fieldElement = document.createElement( 'div' );
				fieldElement.setAttribute( 'id', element.id );

				let errorElement = document.createElement( 'div' );
				errorElement.setAttribute( 'id', element.id + '-error' );

				$element.append( element.label );
				$element.append( fieldElement );
				$element.append( errorElement );

				// Mount component.
				if ( document.querySelectorAll('#' + element.id ).length > 0 ) {
					let component = mollie.createComponent( element.component );

					component.mount( '#' + element.id );
				}

				// Handling errors.
				/*
				var cardNumberError = document.querySelector( '#card-number-error' );

				cardNumber.addEventListener( 'change', event => {
					cardNumberError.textContent = event.error && event.touched ? event.error : '';
				} );
			 	*/
			} );
		} );
	}

	jQuery( document.body ).on( 'updated_checkout', initMollieComponents );
} )();
