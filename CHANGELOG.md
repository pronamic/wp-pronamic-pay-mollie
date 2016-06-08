# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

## [1.1.6] - 2016-06-08
- Added support for Mollie Checkout.
- Reduced the use of else expressions.
- Added WordPress payment method to Mollie method transform function.
- Added readonly Mollie user profile fields.
- Simplified the gateay payment start function.

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
- Changed WordPress pay core library requirment from ~1.0.1 to >=1.0.1.

## [1.1.0] - 2015-02-16
- Improved support for unsupported Mollie locales.
- Added support for the SOFORT Banking payment method.

## 1.0.0 - 2015-01-19
- First release.

[unreleased]: https://github.com/wp-pay-gateways/mollie/compare/1.1.6...HEAD
[1.1.6]: https://github.com/wp-pay-gateways/mollie/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/wp-pay-gateways/mollie/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/wp-pay-gateways/mollie/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/wp-pay-gateways/mollie/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/wp-pay-gateways/mollie/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/wp-pay-gateways/mollie/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/wp-pay-gateways/mollie/compare/1.0.0...1.1.0
