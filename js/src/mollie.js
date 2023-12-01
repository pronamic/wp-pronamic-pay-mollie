/* global Mollie */
( function () {
	function init( data ) {
		const mollie = Mollie( data.profileId, data.options );

		const tokenElement = document.getElementById( data.elementId );

		if ( ! tokenElement ) {
			throw new Error( 'No data token element.' );
		}

		if ( ! tokenElement.form ) {
			throw new Error( 'Data token element not in form.' );
		}

		const form = tokenElement.form;

		async function createToken( e ) {
			e.preventDefault();

			const { token } = await mollie.createToken();

			if ( token ) {
				tokenElement.value = token;
			}

			form.removeEventListener( 'submit', createToken );

			form.requestSubmit( e.submitter );

			form.addEventListener( 'submit', createToken );
		}

		form.addEventListener( 'submit', createToken );

		const cardComponent = mollie.createComponent( 'card' );

		cardComponent.mount( data.mount );
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
