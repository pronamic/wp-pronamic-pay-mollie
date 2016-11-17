# WordPress Pay Gateway: Mollie

**Mollie driver for the WordPress payment processing library.**

[![Build Status](https://travis-ci.org/wp-pay-gateways/mollie.svg?branch=develop)](https://travis-ci.org/wp-pay-gateways/mollie)
[![Coverage Status](https://coveralls.io/repos/wp-pay-gateways/mollie/badge.svg?branch=master&service=github)](https://coveralls.io/github/wp-pay-gateways/mollie?branch=master)
[![Latest Stable Version](https://poser.pugx.org/wp-pay-gateways/mollie/v/stable.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![Total Downloads](https://poser.pugx.org/wp-pay-gateways/mollie/downloads.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![Latest Unstable Version](https://poser.pugx.org/wp-pay-gateways/mollie/v/unstable.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![License](https://poser.pugx.org/wp-pay-gateways/mollie/license.svg)](https://packagist.org/packages/wp-pay-gateways/mollie)
[![Built with Grunt](https://cdn.gruntjs.com/builtwith.svg)](http://gruntjs.com/)


## Webhook URL

Please note that an webhook URL with the host `localhost` or with the TLD `.dev` are not allowed,
this library will check on WordPress URL's on `localhost` or on the `.dev` TLD and will not pass 
the `webhookUrl` paramater to Mollie. If you want to test the Mollie webhook URL on an local 
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
