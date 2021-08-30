<p align="center">
	<a href="https://www.wp-pay.org/">
		<img src="https://www.wp-pay.org/assets/pronamic-pay.svgo-min.svg" alt="WordPress Pay » Gateway » Mollie" width="72" height="72">
	</a>
</p>

<h1 align="center">WordPress Pay » Gateway » Mollie</h3>

<p align="center">
	Mollie driver for the WordPress payment processing library.
</p>

## Table of contents

- [Status](#status)
- [Webhook URL](#webhook-url)
- [Simulate Requests](#simulate-requests)
- [REST API](#rest-api)
- [WP-CLI](#wp-cli)
- [WordPress Filters](#wordpress-filters)
- [Links](#links)
- [Documentation](#documentation)

## Status

[![Build Status](https://travis-ci.org/wp-pay-gateways/mollie.svg?branch=develop)](https://travis-ci.org/wp-pay-gateways/mollie)
[![Coverage Status](https://coveralls.io/repos/wp-pay-gateways/mollie/badge.svg?branch=master&service=github)](https://coveralls.io/github/wp-pay-gateways/mollie?branch=master)
[![Latest Stable Version](https://poser.pugx.org/wp-pay-gateways/mollie/v/stable.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![Total Downloads](https://poser.pugx.org/wp-pay-gateways/mollie/downloads.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![Latest Unstable Version](https://poser.pugx.org/wp-pay-gateways/mollie/v/unstable.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![License](https://poser.pugx.org/wp-pay-gateways/mollie/license.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![Built with Grunt](http://cdn.gruntjs.com/builtwith.svg)](http://gruntjs.com/)


## Webhook URL

Please note that an webhook URL with the host `localhost` or with the TLD `.dev` are not allowed,
this library will check on WordPress URL's on `localhost` or on the `.dev` TLD and will not pass 
the `webhookUrl` parameter to Mollie. If you want to test the Mollie webhook URL on an local 
development environment you could use a service like [ngrok](https://ngrok.com/).

> Beste Remco,
> 
> Ja dit is inderdaad het probleem. .dev URL's worden niet ondersteunt. Deze zal ook niet bereikbaar zijn.
> 
> Als je report URL niet publiekelijk bereikbaar is zou je een service als https://ngrok.com kunnen gebruiken. Dit is een programma die je lokaal draait en als proxy werkt. Misschien heb je er iets aan.
> 
> Met vriendelijke groet,
> 
> Lennard van Gunst
> Mollie

## Simulate Requests

### Webhook

```
curl --request POST "https://www.example.com/wp-json/pronamic-pay/mollie/v1/webhook" \
        --data "id=test" \
        --user-agent "Mollie HTTP"
```

## REST API

The Pronamic Pay Mollie gateway can handle Mollie webhook requests via the WordPress REST API.

**Route:** `/wp-json/pronamic-pay/mollie/v1/webhook`

The WordPress REST API Mollie webhook endpoint can be tested with for example cURL:

```
curl --request POST --data "id=tr_d0b0E3EA3v" http://pay.test/wp-json/pronamic-pay/mollie/v1/webhook
```

Legacy webhook URL:

```
curl --request POST --data "id=tr_d0b0E3EA3v" "http://pay.test/?mollie_webhook"
```

## WP-CLI

### What is WP-CLI?

For those who have never heard before WP-CLI, here's a brief description extracted from the [official website](https://wp-cli.org/).

> **WP-CLI** is a set of command-line tools for managing WordPress installations. You can update plugins, set up multisite installs and much more, without using a web browser.

### Commands

```bash
$ wp pronamic-pay mollie
usage: wp pronamic-pay mollie customers <command>
   or: wp pronamic-pay mollie organizations <command>

See 'wp help pronamic-pay mollie <command>' for more information on a specific command.
```

### Command `pronamic-pay mollie customers synchronize`

Synchronize Mollie customers to WordPress.

```bash
$ wp pronamic-pay mollie customers synchronize
```

### Command `pronamic-pay mollie customers connect-wp-users`

Connect Mollie customers to WordPress users by email.

```bash
$ wp pronamic-pay mollie customers connect-wp-users
```

## WordPress Filters

### `pronamic_pay_mollie_payment_description`

#### Description

Filters the Mollie payment description.

#### Usage

```php
\add_filter( 'pronamic_pay_mollie_payment_description', 'your_function_name', 10, 2 );
```

#### Parameters

**`$description`** | string

Mollie payment description.

**`$payment`** | [Payment Object](https://github.com/wp-pay/core/blob/2.3.0/src/Payments/Payment.php)

The WordPress payment object.

#### Examples

```php
\add_filter( 'pronamic_pay_mollie_payment_description', function( $description, $payment ) {
	$periods = $payment->get_periods();

	if ( null === $periods ) {
		return $description;
	}

	foreach ( $periods as $period ) {
		$phase = $period->get_phase();

		$subscription = $phase->get_subscription();

		$description = \sprintf(
			'%s - %s - %s',
			$subscription->get_description(),
			$period->get_start_date()->format_i18n( 'd-m-Y' ),
			$period->get_end_date()->format_i18n( 'd-m-Y' )
		);
	}

	return $description;
}, 10, 2 );
```

### `pronamic_pay_mollie_payment_metadata`

#### Description

Filters the Mollie payment metadata.

#### Usage

```php
\add_filter( 'pronamic_pay_mollie_payment_metadata', 'your_function_name', 10, 2 );
```

#### Parameters

**`$metadata`** | mixed

Mollie payment metadata.

**`$payment`** | [Payment Object](https://github.com/wp-pay/core/blob/2.3.0/src/Payments/Payment.php)

The WordPress payment object.

#### Examples

```php
\add_filter( 'pronamic_pay_mollie_payment_metadata', function( $metadata, $payment ) {
	$data = array();

	$customer = $payment->get_customer();

	if ( null !== $customer ) {
		$vat_number = $customer->get_vat_number();

		if ( null !== $vat_number ) {
			$data['vat_number'] = $vat_number->normalized();
		}
	}

	switch ( $payment->get_source() ) {
		case 'easydigitaldownloads':
			$data['edd_order_id'] = $payment->get_source_id();

			break;
		case 'gravityformsideal':
			$data['gf_entry_id'] = $payment->get_source_id();

			break;
	}

	return (object) $data;
}, 10, 2 );
```

### `pronamic_pay_mollie_payment_billing_email`

#### Description

Filters the Mollie payment billing email used for bank transfer payment instructions.

#### Usage

```php
\add_filter( 'pronamic_pay_mollie_payment_billing_email', 'your_function_name', 10, 2 );
```

#### Parameters

**`$billing_email`** | string|null

The Mollie payment billing email.

**`$payment`** | [Payment Object](https://github.com/wp-pay/core/blob/2.3.0/src/Payments/Payment.php)

The WordPress payment object.

#### Examples

```php
\add_filter( 'pronamic_pay_mollie_payment_billing_email', function( $billing_email, $payment ) {
	$billing_email = 'mollie-billing-email@example.com';

	return $billing_email;
}, 10, 2 );
```

## Links

*	http://www.mollie.nl/


## Errors

### The customer id is invalid

```sql
DELETE
	meta
FROM
	wp_usermeta AS meta
		INNER JOIN
	wp_users AS user
			ON user.ID = user_id
WHERE
	(
		meta_key = '_pronamic_pay_mollie_customer_id'
			OR
		meta_key = '_pronamic_pay_mollie_customer_id_test'
	)
		AND
	user.user_login = 'username'
;
```

## Documentation

*	[Mollie API](https://www.mollie.nl/files/documentatie/payments-api.html)
*	[GitHub repository Mollie API client for PHP](https://github.com/mollie/mollie-api-php)
