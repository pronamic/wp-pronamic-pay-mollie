# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

## [4.7.4] - 2023-02-17

### Commits

- Fixed running integration installation. ([ba35110](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/ba3511097e85f5292edcf5d8d3542110ceb21374))

Full set of changes: [`4.7.3...4.7.4`][4.7.4]

[4.7.4]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.3...v4.7.4

## [4.7.3] - 2023-02-07
### Changed

- Removed `db_version_option_name` integration argument.

Full set of changes: [`4.7.2...4.7.3`][4.7.3]

[4.7.3]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.2...v4.7.3

## [4.7.2] - 2023-01-31
### Commits

- Fixed "Mollie requires locale for order" with language codes of only 2 characters in `Accept-Language` header (fixes #20). ([c9ca730](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/c9ca7308cc956c888a594d73724bcf02d8d4195c))

### Composer

- Changed `php` from `>=8.0` to `>=7.4`.
Full set of changes: [`4.7.1...4.7.2`][4.7.2]

[4.7.2]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.1...v4.7.2

## [4.7.1] - 2023-01-18

### Commits

- Maybe create shipment on payment fulfilled action. ([881d6d5](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/881d6d5ec49275a0ab86e5aa8969b04d9cb0996e))
- Mark WooCommerce with support for Mollie orders. ([5440b76](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/5440b76ee1ffa3c2e34dc7a1a8c5328e9b8fc447))
- Ignore `documentation` folder in archive files. ([1cb8f56](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/1cb8f56690c8e7b76fecb6606f6b02b28c7890cc))
- The Mollie order line category is not the same as a product category. ([10e1297](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/10e1297c52bdda8651aeff9979655239cf8b94e3))
- Mollie order shipping address is optional. ([88d7458](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/88d745827e6aec33e6b08b49fdfe90698c2e8eae))
- Happy 2023. ([cc44d7a](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/cc44d7ad4f0195fc0e2cfcaa4895f96cb3a69909))

Full set of changes: [`4.7.0...4.7.1`][4.7.1]

[4.7.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.0...v4.7.1

## [4.7.0] - 2022-12-22

### Commits

- Added "Requires Plugins" header. ([fbd32df](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/fbd32dff63e7086690eccfa30bf0bcf97267143f))
- Only add anchor in meta box if destination page can be accessed. ([aecda56](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/aecda568d1bfbd85c1650fd1be62605b153ee282))
- Removed `FILTER_SANITIZE_STRING` usage. ([1011701](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/10117012f586c11836cb8c3a7be1c9163f1b9738))

### Composer

- Changed `php` from `>=7.4` to `>=8.0`.
- Changed `pronamic/wp-http` from `^1.1` to `v1.2.0`.
	Release notes: https://github.com/pronamic/wp-http/releases/tag/v4.6.0
- Changed `pronamic/wp-mollie` from `^1.0` to `v1.1.0`.
	Release notes: https://github.com/pronamic/wp-mollie/releases/tag/v4.6.0
- Changed `wp-pay/core` from `^4.5` to `v4.6.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.6.0
Full set of changes: [`4.6.0...4.7.0`][4.7.0]

[4.7.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.6.0...v4.7.0

## [4.6.0] - 2022-11-29
- Use new `pronamic/wp-mollie` library.
- Use new `str_*_with` functions, requires WordPress `5.9` or higher.

## [4.5.0] - 2022-11-07
- Added user agent to HTTP requests to Mollie. [#13](https://github.com/pronamic/wp-pronamic-pay-mollie/issues/13)

## [4.4.1] - 2022-10-11
- Fixed recurring payments using latest mandate of Mollie customer instead of subscription mandate (pronamic/wp-pronamic-pay-mollie#11).

## [4.4.0] - 2022-09-26
- Fixed empty billing email address causing `Unprocessable Entity - The email address '' is invalid` error.
- Updated payment methods registration.

## [4.3.1] - 2022-08-15
- Updated payment provider URL from `www.mollie.com` to `my.mollie.com` ([pronamic/wp-pronamic-pay-mollie#8](https://github.com/pronamic/wp-pronamic-pay-mollie/issues/8)).

## [4.3.0] - 2022-07-01
### Added
- Added support for Mollie orders API. [pronamic/wp-pronamic-pay/#190](https://github.com/pronamic/wp-pronamic-pay/issues/190)

### Changed
- Improved JSON serialization for communication towards Mollie API. 

## [4.2.0] - 2022-05-30
### Added
- Added payment charged back amount ([pronamic/wp-pronamic-pay#165](https://github.com/pronamic/wp-pronamic-pay/issues/165), [pronamic/wp-pronamic-pay#170](https://github.com/pronamic/wp-pronamic-pay/issues/170)).

## [4.1.0] - 2022-04-11
- No longer store gateway mode in meta.
- No longer catch exception, should be handled downstream.

## [4.0.1] - 2022-02-16
- Fixed updating subscription mandate with subscription actions.

## [4.0.0] - 2022-01-11
### Changed
- Updated to https://github.com/pronamic/wp-pay-core/releases/tag/4.0.0.
- Added payment ID to the webhook URL.

## [3.1.0] - 2021-09-03
- Added `pronamic_pay_mollie_payment_description` filter (with example).
- Removed check for empty amount, `0` amount is allowed for credit card authorizations.

## [3.0.0] - 2021-08-05
- Updated to `pronamic/wp-pay-core`  version `3.0.0`.
- Updated to `pronamic/wp-money`  version `2.0.0`.
- Switched to `pronamic/wp-coding-standards`.

## [2.2.4] - 2021-06-18
- Refunds maintenance.

## [2.2.3] - 2021-04-26
- Added initial support for refunds.
- Added support for creating mandate with free trial periods.
- Started using `pronamic/wp-http`.

## [2.2.2] - 2021-02-08
- Fixed "Error validating `/locale`: The property `locale` is required" on some status update (https://github.com/mollie/api-documentation/pull/731).

## [2.2.1] - 2021-01-18
- Added support for first payment with regular iDEAL/Bancontact/Sofort payment methods.
- Added support for recurring payments with Apple Pay.
- Added 'Change Payment State' URL to Mollie payment admin page.
- Chargebacks now update subscriptions status to 'On hold' (needs manual review).

## [2.2.0] - 2020-11-09
- Added Przelewy24 payment method.
- Added REST route permission callback.
- Improved determining customer if previously used customer has been removed at Mollie.
- Fixed filtering next payment delivery date.
- Fixed incorrect check for failed payment bank reason detail.

## [2.1.4] - 2020-07-08
- Added filter for Mollie payment metadata.
- Added support for updating subscription mandate.

## [2.1.3] - 2020-06-02
- Add support for Mollie payment billing email and filter `pronamic_pay_mollie_payment_billing_email`.

## [2.1.2] - 2020-04-03
- Fixed install issues on some specific WordPress installations.
- Add initial Apple Pay support.

## [2.1.1] - 2020-03-19
- Force a specific collate to fix "Illegal mix of collations" error.

## [2.1.0] - 2020-03-18
- Added custom tables for Mollie profiles, customers and WordPress users.
- Added experimental CLI integration.
- Moved webhook logic to REST API.
- Improved WordPress user profile Mollie section.
- Added WordPress admin dashboard page for Mollie customers.
- Added support for one-off SEPA Direct Debit payment method.
- Added support for payment failure reason.

## [2.0.10] - 2020-02-03
- Fixed notice "Not Found - No customer exists with token cst_XXXXXXXXXX" in some cases.

## [2.0.9] - 2019-12-22
- Added advanced setting for bank transfer due date days.
- Added bank transfer recipient details to payment.
- Added URL to manual in gateway settings.
- Improved error handling with exceptions.
- Removed Bitcoin payment method (not supported by Mollie anymore).

## [2.0.8] - 2019-10-04
- Added response data to error for unexpected response code.
- Moved next payment delivery date filter from gateway to integration class.
- Throw exception when Mollie response is not what we expect.

## [2.0.7] - 2019-08-28
- Updated packages.
- Updated to Mollie API v2, with multicurrency support.
- Added EPS payment method.
- Added filter for subscription 'Next Payment Delivery Date'.

## [2.0.6] - 2019-01-18
- Name is not required anymore when creating a new Mollie customer.

## [2.0.5] - 2018-10-12
- Set gateway mode based on API key.

## [2.0.4] - 2018-08-15
- Improved the way we create and handle Mollie customers.

## [2.0.3] - 2018-07-06
- Do not allow .local TLD in webhook URL.
- Added missing `failed` status.

## [2.0.2] - 2018-06-01
- Fixed setting issuer for iDEAL payment method.

## [2.0.1] - 2018-05-16
- Fixed getting customer ID from subscription meta for guest users.

## [2.0.0] - 2018-05-14
- Switched to PHP namespaces.

## [1.1.15] - 2017-12-12
- Added support for payment method `Direct Debit (mandate via Bancontact)`.
- No longer create new Mollie customer during recurring (not first) payments.
- Update payment consumer BIC from Mollie payment details.
- Update payment consumer name with Mollie payment card holder name.
- Cancel subscriptions if first payment fails, to prevent future reactivation when a valid customer ID becomes available.
- Update subscription status on payment start only if it's not a recurring payment for a cancelled subscription.

## [1.1.14] - 2017-05-01
- Set payment status to `Failed` too if `mollie_error` occurs.

## [1.1.13] - 2017-03-15
- Return null if the payment method variable is not a scalar type to fix “Warning: Illegal offset type in isset or empty” error.
- No longer check if $payment_method is a empty string, the compare on the mandate method is enough.
- Set default payment method to null in `has_valid_mandate` function.
- Improved getting the first valid mandate date time.
- Ignore valid mandates for first payments.

## [1.1.12] - 2017-01-25
- Fixed Composer requirement.

## [1.1.11] - 2017-01-25
- Enabled support for more Mollie payment methods.
- Auto renew invalid customer IDs.
- Only update subscription status for subscriptions.
- Added filter for payment provider URL.
- Removed deprecated MISTER_CASH from the `get_supported_payment_methods` function.

## [1.1.10] - 2016-11-16
- Improved Client class, DRY improvements.
- Added constants for some extra methods.

## [1.1.9] - 2016-10-20
- Fixed wrong char in switch statement.
- Added support for new Bancontact constant.
- Use separate customer IDs for test and live mode.

## [1.1.8] - 2016-07-06
- Excluded non-essential files in .gitattributes.

## [1.1.7] - 2016-07-06
- Added PayPal to gateway methods transformations.
- Fixed undefined variable `$user_id`.

## [1.1.6] - 2016-06-08
- Added support for Mollie Checkout.
- Reduced the use of else expressions.
- Added WordPress payment method to Mollie method transform function.
- Added readonly Mollie user profile fields.
- Simplified the gateway payment start function.

## [1.1.5] - 2016-03-22
- Added product URL, updated dashboard URL.
- Updated gateway settings.
- Added support for bank transfer and direct debit payment methods.

## [1.1.4] - 2016-03-02
- Improved support for custom payment methods through Gravity Forms.
- Moved get_gateway_class() function to the configuration class.
- Removed get_config_class(), no longer required.
- Also added an check on localhost webhook URLs.

## [1.1.3] - 2016-02-01
- Added an gateway settings class.
- Don't redirect if webhook was called (allows for e-commerce tracking)

## [1.1.2] - 2015-10-14
- Add support for direct iDEAL payment method.

## [1.1.1] - 2015-03-03
- Changed WordPress pay core library requirement from `~1.0.1` to `>=1.0.1`.

## [1.1.0] - 2015-02-16
- Improved support for unsupported Mollie locales.
- Added support for the SOFORT Banking payment method.

## 1.0.0 - 2015-01-19
- First release.

[unreleased]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.6.0...HEAD
[4.6.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.5.0...4.6.0
[4.5.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.4.1...4.5.0
[4.4.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.4.0...4.4.1
[4.4.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.3.1...4.4.0
[4.3.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.3.0...4.3.1
[4.3.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.2.0...4.3.0
[4.2.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.1.0...4.2.0
[4.1.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.0.1...4.1.0
[4.0.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/3.1.0...4.0.0
[3.0.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/3.0.0...3.1.0
[2.2.4]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.2.4...3.0.0
[2.2.4]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.2.3...2.2.4
[2.2.3]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.2.2...2.2.3
[2.2.2]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.1.4...2.2.0
[2.1.4]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.1.3...2.1.4
[2.1.3]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.10...2.1.0
[2.0.10]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.9...2.0.10
[2.0.9]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.8...2.0.9
[2.0.8]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.7...2.0.8
[2.0.7]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.6...2.0.7
[2.0.6]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.5...2.0.6
[2.0.5]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.15...2.0.0
[1.1.15]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.14...1.1.15
[1.1.14]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.13...1.1.14
[1.1.13]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.12...1.1.13
[1.1.12]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.11...1.1.12
[1.1.11]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.10...1.1.11
[1.1.10]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.9...1.1.10
[1.1.9]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.8...1.1.9
[1.1.8]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.7...1.1.8
[1.1.7]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.6...1.1.7
[1.1.6]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/1.0.0...1.1.0
