# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

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
- Use seperate customer IDs for test and live mode.

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

[unreleased]: https://github.com/wp-pay-gateways/mollie/compare/1.1.14...HEAD
[1.1.14]: https://github.com/wp-pay-gateways/mollie/compare/1.1.13...1.1.14
[1.1.13]: https://github.com/wp-pay-gateways/mollie/compare/1.1.12...1.1.13
[1.1.12]: https://github.com/wp-pay-gateways/mollie/compare/1.1.11...1.1.12
[1.1.11]: https://github.com/wp-pay-gateways/mollie/compare/1.1.10...1.1.11
[1.1.10]: https://github.com/wp-pay-gateways/mollie/compare/1.1.9...1.1.10
[1.1.9]: https://github.com/wp-pay-gateways/mollie/compare/1.1.8...1.1.9
[1.1.8]: https://github.com/wp-pay-gateways/mollie/compare/1.1.7...1.1.8
[1.1.7]: https://github.com/wp-pay-gateways/mollie/compare/1.1.6...1.1.7
[1.1.6]: https://github.com/wp-pay-gateways/mollie/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/wp-pay-gateways/mollie/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/wp-pay-gateways/mollie/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/wp-pay-gateways/mollie/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/wp-pay-gateways/mollie/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/wp-pay-gateways/mollie/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/wp-pay-gateways/mollie/compare/1.0.0...1.1.0
