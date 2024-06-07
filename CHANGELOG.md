# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

## [4.12.0] - 2024-06-07

### Fixed

- Improve the handling of Mollie mandate requests on the mandate detail page. ([9f23324](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/9f23324daa61972c400c9b2604e6d4c1e6e08e69))

Full set of changes: [`4.11.0...4.12.0`][4.12.0]

[4.12.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.11.0...v4.12.0

## [4.11.0] - 2024-05-27

### Commits

- Updated composer.json ([cb4f3a8](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/cb4f3a85809153c6154514333e504efa253e448b))
- Updated composer.json ([408efb0](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/408efb034b80758909f64131f972378da13dfed4))
- npm run build ([6056a1c](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/6056a1c329df46e324515f6ac8e76c9511206514))
- ncu -u ([437f62c](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/437f62ca0e6cfa7ce624096a6ff4a992833eca48))
- Added MyBank. ([9c49ce6](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/9c49ce6cbc667e90fdc1c85f39045e5bb0a18758))
- Added BLIK to payment method transformer. ([b0efe96](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/b0efe96d99a067557a59304508c33c427ef7252d))

### Composer

- Changed `pronamic/wp-mollie` from `^1.5` to `v1.6.0`.
	Release notes: https://github.com/pronamic/wp-mollie/releases/tag/v1.6.0
- Changed `woocommerce/action-scheduler` from `^3.7` to `3.8.0`.
	Release notes: https://github.com/woocommerce/action-scheduler/releases/tag/3.8.0
- Changed `wp-pay/core` from `^4.16` to `v4.18.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.18.0

Full set of changes: [`4.10.3...4.11.0`][4.11.0]

[4.11.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.10.3...v4.11.0

## [4.10.3] - 2024-05-06

### Commits

- Fixed mix customer ID and Mollie ID, see 5c739d2fcdc448d27e8f9246d713951a147a638c. ([e2e47fa](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/e2e47fadf1594f095e34418e87165ae665c8223f))
- Improve exception message with insert data. ([aa1006a](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/aa1006a5de804dad8a1003fac7e24b438b080366))

Full set of changes: [`4.10.2...4.10.3`][4.10.3]

[4.10.3]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.10.2...v4.10.3

## [4.10.2] - 2024-04-22

### Commits

- No longer use `INSERT IGNORE INTO`, is not supported in Playground. [5c739d2](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/5c739d2fcdc448d27e8f9246d713951a147a638c) [67a2a6e](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/67a2a6e45c481c2b555bd5533ad822312554519b)

### Composer

- Added `automattic/jetpack-autoloader` `^3.0`.

Full set of changes: [`4.10.1...4.10.2`][4.10.2]

[4.10.2]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.10.1...v4.10.2

## [4.10.1] - 2024-03-27

### Commits

- No longer use `INSERT IGNORE` and `BINARY` operator. ([aec7ea5](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/aec7ea543459d807ff2e2c8e8062996ce5c88e34))

Full set of changes: [`4.10.0...4.10.1`][4.10.1]

[4.10.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.10.0...v4.10.1

## [4.10.0] - 2024-03-26

### Changed

- Added support for the more general card payment method. ([9fe0f64](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/9fe0f64d1e725977c5ce83c9d19bf25479737b95))

### Composer

- Changed `wp-pay/core` from `^4.15` to `v4.16.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.16.0

Full set of changes: [`4.9.2...4.10.0`][4.10.0]

[4.10.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.9.2...v4.10.0

## [4.9.2] - 2024-02-13

### Commits

- Added `if ( ! defined( 'ABSPATH' ) )`. ([5c4840c](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/5c4840cfc85939569674283cc1f7acb1f1fb9b14))

Full set of changes: [`4.9.1...4.9.2`][4.9.2]

[4.9.2]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.9.1...v4.9.2

## [4.9.1] - 2024-02-13

### Commits

- Added support for TWINT. ([e4b482b](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/e4b482b304c1157e7acd886e0815734e86733c39))

Full set of changes: [`4.9.0...4.9.1`][4.9.1]

[4.9.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.9.0...v4.9.1

## [4.9.0] - 2024-02-07

### Changed

- The code further complies with (WordPress) coding standards.
- The HTTP timeout option is increased when connecting to Mollie via WP-Cron, WP-CLI or the Action Scheduler library. [pronamic/wp-pay-core#170](https://github.com/pronamic/wp-pay-core/issues/170)

### Fixed

- Fixed `wp_register_script` and `wp_register_style` are called incorrectly https://github.com/pronamic/wp-pronamic-pay-mollie/issues/42. ([41bfb35](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/41bfb35d058cb50012d2141d111c084f24ec1e3c))

### Added

- Added support for Mollie card field/component in WooCommerce legacy checkout. [#40](https://github.com/pronamic/wp-pronamic-pay-mollie/pull/40)

### Composer

- Changed `pronamic/wp-mollie` from `^1.4` to `v1.5.0`.
	Release notes: https://github.com/pronamic/wp-mollie/releases/tag/v1.5.0
- Changed `woocommerce/action-scheduler` from `^3.6` to `3.7.1`.
	Release notes: https://github.com/woocommerce/action-scheduler/releases/tag/3.7.1
- Changed `wp-pay/core` from `^4.13` to `v4.15.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.15.0

Full set of changes: [`4.8.1...4.9.0`][4.9.0]

[4.9.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.8.1...v4.9.0

## [4.8.1] - 2023-10-16

### Commits

- Allow bypassing `first` sequence type with empty string (pronamic/wp-pronamic-pay-woocommerce#58). ([2a08130](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/2a08130afd975f1396d6ac213b0a7cb8e18496e6))

Full set of changes: [`4.8.0...4.8.1`][4.8.1]

[4.8.1]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.8.0...v4.8.1

## [4.8.0] - 2023-10-13

### Commits

- Merge pull request #39 from pronamic/wp-mollie-4-dynamic-properties ([db6e935](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/db6e935910d7077c158f5817ced143c506716cc5))
- Removed failure reason comment. ([83f2747](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/83f2747bbfbdb49b4d7d4917ddabeffe22a4201f))
- Updated for payment details through `ObjectAccess` (pronamic/wp-mollie#4). ([450aca6](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/450aca61ca4a3e5efaffb195c249b3554a45c4f1))
- Fixed the "The method parameter $args is never used" warnings. ([918d67d](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/918d67d6cac1fd7e72fd122e2378718ec4d477ff))
- Fixed the "The method parameter $args is never used" warnings. ([1e3b3c7](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/1e3b3c73eabdc50aff2d0d6c7a8d5c79008f9fc6))
- Cast vars to strings to make PHPStan happy. ([9fdee84](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/9fdee84e7f3cd209d273ed0afff1e05ab92e0178))
- Compare meta value and Mollie ID as binary strings to avoid collate issues. ([fd373d1](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/fd373d18ab2ee08567b75042d2c72da4121d0ed6))
- Require PHP >=8.0, updated pronamic/wp-coding-standards. ([d0daf64](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/d0daf64769c1abc2dce62f3c381df52190a612b4))

### Composer

- Changed `php` from `>=7.4` to `>=8.0`.
- Changed `pronamic/wp-mollie` from `^1.2` to `v1.4.0`.
	Release notes: https://github.com/pronamic/wp-mollie/releases/tag/v1.4.0
- Changed `woocommerce/action-scheduler` from `^3.4` to `3.6.4`.
	Release notes: https://github.com/woocommerce/action-scheduler/releases/tag/3.6.4
- Changed `wp-pay/core` from `^4.9` to `v4.13.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.13.0

Full set of changes: [`4.7.11...4.8.0`][4.8.0]

[4.8.0]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.11...v4.8.0

## [4.7.11] - 2023-09-11

### Commits

- Make consumer name and IBAN fields required (https://github.com/pronamic/wp-pronamic-pay/issues/361). ([5f6d13c](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/5f6d13c32745fba007fd1765ec964f8014c1b474))
- Added `wp-slug` to Composer config. ([a9f0539](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/a9f05394943165dde9496e7f371394ddbed45886))

Full set of changes: [`4.7.10...4.7.11`][4.7.11]

[4.7.11]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.10...v4.7.11

## [4.7.10] - 2023-08-30

### Fixed

- Fixed setting Billie payment method status.

### Commits

- All payment methods are inactive by default. ([d15c1cc](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/d15c1cc82b5cf977c7a409d7cb161981aad03d8e))

Full set of changes: [`4.7.9...4.7.10`][4.7.10]

[4.7.10]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.9...v4.7.10

## [4.7.9] - 2023-08-23

### Commits

- Fixed WPCS 3 issues. ([12b62fb](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/12b62fb3f733eb4a57c3a05f903f7273691f6fb7))
- Dont allow direct file access. ([48c927b](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/48c927b97f30e9ff8b87d3b346589ac849001aa4))
- Removed collate clause. ([bccdb0e](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/bccdb0eb6f5bc9ece8bd1146068758cb62a02270))

Full set of changes: [`4.7.8...4.7.9`][4.7.9]

[4.7.9]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.8...v4.7.9

## [4.7.8] - 2023-07-12

### Commits

- Mark payment methods recurring support. ([a0c1246](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/a0c1246a55f5393c68c6d3a5e0f182a2d70eac84))
- Added link to payment mandate ID. ([9d37959](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/9d37959c0c0baa92682136a0de9eb97be8b554a1))
- Change incomplete `<dl>` elements to `<p>`. ([7f18457](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/7f18457ea5cbca1d3ef224dd0c0ac123392d9380))
- Added support for Billie. ([59ea0a8](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/59ea0a8911cbe5521c4102343c53e58963ced6ab))
- Added mandate page and link. ([277adc0](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/277adc0193c8faa6f56564d95716915b4fada334))

Full set of changes: [`4.7.7...4.7.8`][4.7.8]

[4.7.8]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.7...v4.7.8

## [4.7.7] - 2023-06-01

### Commits

- Switch from `pronamic/wp-deployer` to `pronamic/pronamic-cli`. ([1659c0a](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/1659c0ac9a0713fef7de66eeca72ff9d9a2afde8))
- Prevent error when trying to retrieve iDEAL issuers when SEPA Direct Debit and iDEAL are both inactive. ([1f3e4a0](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/1f3e4a0ea58c60d0ae268cb70774861962acfb16))
- Fixed method documentation. ([949cf76](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/949cf76e561f176e5264f94f16f620ee89df03a7))
- Updated .gitattributes ([45c4a5c](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/45c4a5c8a629c7e6ca6d50bce0332fbb99bd683e))

Full set of changes: [`4.7.6...4.7.7`][4.7.7]

[4.7.7]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.6...v4.7.7

## [4.7.6] - 2023-03-29
### Changed

- Extended support for refunds.

### Commits

- Added support for in3. ([f12c9b5](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/f12c9b50a7969a0e081a1163861fbc79a7748ee8))

### Composer

- Changed `pronamic/wp-mollie` from `^1.1` to `v1.2.0`.
	Release notes: https://github.com/pronamic/wp-mollie/releases/tag/v1.2.0
- Changed `wp-pay/core` from `^4.6` to `v4.9.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.9.0
Full set of changes: [`4.7.5...4.7.6`][4.7.6]

[4.7.6]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.5...v4.7.6

## [4.7.5] - 2023-03-10

### Commits

- Added support for `en_GB` locale. ([5b3fc51](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/5b3fc515d908ab10dc66c8611054197743f5c690))
- Set `wordpress-plugin` type for Composer (pronamic/wp-pronamic-pay-with-mollie-for-contact-form-7#3). ([7517c92](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/7517c92862b7a6c6e31a921f3871c1c9cacabdbd))
- Updated .gitattributes ([c12d414](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/c12d41423e90582570570afc95ace7594b914199)) ([be417d7](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/be417d73795fd3be2d82c24a4a8fb5584aac7fff))
- Ignore `/vendor-bin/` in export to to archive files. ([baca178](https://github.com/pronamic/wp-pronamic-pay-mollie/commit/baca17883e8f4213fe89307b169b5947f47f4038))

Full set of changes: [`4.7.4...4.7.5`][4.7.5]

[4.7.5]: https://github.com/pronamic/wp-pronamic-pay-mollie/compare/v4.7.4...v4.7.5

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
