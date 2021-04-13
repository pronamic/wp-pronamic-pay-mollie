# Hooks

- [Actions](#actions)
- [Filters](#filters)

## Actions

### pronamic_pay_webhook_log_payment

Argument | Type | Description
-------- | ---- | -----------
`$payment` |  | 

Source: [src/WebhookController.php](../src/WebhookController.php), [line 115](../src/WebhookController.php#L115-L115)

## Filters

### pronamic_pay_mollie_payment_metadata

*Filters the Mollie metadata.*

Argument | Type | Description
-------- | ---- | -----------
`$metadata` | `mixed` | Metadata.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Gateway.php](../src/Gateway.php), [line 405](../src/Gateway.php#L405-L413)

### pronamic_pay_mollie_payment_billing_email

*Filters the Mollie payment billing email used for bank transfer payment instructions.*

Argument | Type | Description
-------- | ---- | -----------
`$billing_email` | `string|null` | Billing email.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Gateway.php](../src/Gateway.php), [line 425](../src/Gateway.php#L425-L433)


