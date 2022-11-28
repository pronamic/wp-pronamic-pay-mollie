/* global Mollie */
'use strict';

/**
 * Mollie Components.
 *
 * @link https://docs.mollie.com/components/overview
 */
($ => {
  const components = [{
    id: 'card-holder',
    label: 'Card Holder',
    component: 'cardHolder'
  }, {
    id: 'card-number',
    label: 'Card Number',
    component: 'cardNumber'
  }, {
    id: 'expiry-date',
    label: 'Expiry',
    component: 'expiryDate'
  }, {
    id: 'verification-code',
    label: 'CVC',
    component: 'verificationCode'
  }];
  function initMollieComponents(forms) {
    let $cardTokenElements;
    if (typeof forms === 'string') {
      $cardTokenElements = document.querySelectorAll(forms + ' .pronamic_pay_mollie_card_token');
    } else {
      $cardTokenElements = forms.querySelectorAll('.pronamic_pay_mollie_card_token');
    }
    $cardTokenElements.forEach($cardTokenElement => {
      // Create components.
      const data = $cardTokenElement.dataset;

      // Check required Mollie profile ID.
      if (!("mollie-profile-id" in data)) {
        throw new Error('No Mollie profile ID in element dataset. Unable to load Mollie Components.');
        return;
      }

      // Initialize Mollie object.
      const mollie = Mollie(data['mollie-profile-id'], {
        locale: data['mollie-locale'] ?? null,
        testmode: "mollie-testmode" in data
      });
      let componentsContainer = document.createElement('div');
      componentsContainer.classList.add('mollie-components');
      $cardTokenElement.append(componentsContainer);
      components.forEach(component => {
        // Label.
        let label = document.createElement('label');
        label.setAttribute('for', component.id);
        label.innerText = component.label;

        // Component container.
        let field = document.createElement('div');
        field.setAttribute('id', component.id);
        label.append(field);

        // Error.
        let error = document.createElement('div');
        error.setAttribute('id', component.id + '-error');
        error.setAttribute('role', 'alert');
        error.classList.add('field-error');
        componentsContainer.append(label, error);

        // Create and mount component.
        let mollieComponent = mollie.createComponent(component.component);
        mollieComponent.mount('#' + component.id);

        // Handle errors.
        mollieComponent.addEventListener('change', event => {
          // Add error.
          if (event.error && event.touched) {
            error.textContent = event.error;
            label.classList.add('is-invalid');
            return;
          }

          // Remove error.
          error.textContent = '';
          label.classList.remove('is-invalid');
        });
      });

      // Create Mollie token on checkout submit.
      const form = $cardTokenElement.closest('form');
      form.addEventListener('submit', async e => {
        // Check existing card token input.
        let cardTokenInput = form.querySelector('input[name="pronamic_pay_mollie_card_token"]');
        if (cardTokenInput) {
          return;
        }
        e.preventDefault();

        // Create token.
        const {
          token,
          error
        } = await mollie.createToken();
        if (error) {
          throw new Error(error.message || '');
        }

        // Add token to form.
        const tokenInput = document.createElement('input');
        tokenInput.setAttribute('type', 'hidden');
        tokenInput.setAttribute('name', 'pronamic_pay_mollie_card_token');
        tokenInput.setAttribute('value', token);
        form.append(tokenInput);
        if (false !== form.dispatchEvent(new Event('pronamic_pay_mollie_components_card_token_added', {
          cancelable: true
        }))) {
          // Submit form, now containing the hidden card token field.
          // form.submit(); — not working with test meta box
          form.querySelector('[name="' + e.submitter.name + '"]').click();
        }
      });
    });
  }
  function setupWooCommerce() {
    const checkoutForm = document.querySelector('form.woocommerce-checkout');
    if (!checkoutForm) {
      return;
    }

    // Init components on updated checkout.
    $(document.body).on('updated_checkout', function (e) {
      initMollieComponents(checkoutForm);
    });
    const $form = $(checkoutForm);

    // Prevent placing order, need to create token first.
    $form.on('checkout_place_order_pronamic_pay_credit_card', returnFalse);

    // Re-enable placing order if card token has been added.
    checkoutForm.addEventListener('pronamic_pay_mollie_components_card_token_added', function (e) {
      e.preventDefault();
      $form.off('checkout_place_order_pronamic_pay_credit_card', returnFalse);
      $form.submit();
    });
  }
  function returnFalse() {
    return false;
  }

  // Init Mollie Components.
  document.addEventListener('DOMContentLoaded', function (e) {
    initMollieComponents('form:not(.woocommerce-checkout)');
  });
  setupWooCommerce();
})(jQuery);
//# sourceMappingURL=components.js.map