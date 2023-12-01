/* global Mollie */
( function () {
	function init( data ) {
		const element = document.getElementById( data.elementId );

		if ( ! element ) {
			throw new Error( 'No data token element.' );
		}

		if ( ! element.form ) {
			throw new Error( 'Data token element not in form.' );
		}

		const form = element.form;

		if ( ! form.mollie ) {
			form.mollie = Mollie( data.profileId, data.options );

			function createToken( e ) {
				const tokenElement = document.getElementById( data.elementId );

				if ( ! tokenElement ) {
					return;
				}

				e.preventDefault();
				e.stopImmediatePropagation();

				form.mollie.createToken().then( function( result ) {
					if ( result.error ) {
						console.log( result.error );
					}

					if ( result.token ) {
						tokenElement.value = result.token;
					}

					form.requestSubmit( e.submitter );

					form.addEventListener( 'submit', createToken, {
						once: true,
					} );
				} );
			}

			form.addEventListener( 'submit', createToken, {
				once: true,
			} );
		}

		if ( form.mollieCardComponent ) {
			form.mollieCardComponent.unmount();
		}

		if ( ! form.mollieCardComponent ) {
			form.mollieCardComponent = form.mollie.createComponent( 'card' );
		}

		form.mollieCardComponent.mount( data.mount );
	}

	window.pronamicPayMollieFields = window.pronamicPayMollieFields || [];

	window.pronamicPayMollieFields.push = function () {
		const args = Array.from( arguments );

		args.forEach( function ( data ) {
			init( data );
		} );
	};

	window.pronamicPayMollieFields.filter( function ( item ) {
		init( item );

		return false;
	} );
} )();
