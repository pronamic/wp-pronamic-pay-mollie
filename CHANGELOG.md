# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

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
- Cancel subscriptions if first payment fails, to prevent future reactivation when a vailid customer ID becomes available.
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
- Changed WordPress pay core library requirment from `~1.0.1` to `>=1.0.1`.

## [1.1.0] - 2015-02-16
- Improved support for unsupported Mollie locales.
- Added support for the SOFORT Banking payment method.

## 1.0.0 - 2015-01-19
- First release.

[unreleased]: https://github.com/wp-pay-gateways/mollie/compare/2.1.0...HEAD
[2.1.0]: https://github.com/wp-pay-gateways/mollie/compare/2.0.10...2.1.0
[2.0.10]: https://github.com/wp-pay-gateways/mollie/compare/2.0.9...2.0.10
[2.0.9]: https://github.com/wp-pay-gateways/mollie/compare/2.0.8...2.0.9
[2.0.8]: https://github.com/wp-pay-gateways/mollie/compare/2.0.7...2.0.8
[2.0.7]: https://github.com/wp-pay-gateways/mollie/compare/2.0.6...2.0.7
[2.0.6]: https://github.com/wp-pay-gateways/mollie/compare/2.0.5...2.0.6
[2.0.5]: https://github.com/wp-pay-gateways/mollie/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/wp-pay-gateways/mollie/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/wp-pay-gateways/mollie/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/wp-pay-gateways/mollie/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/wp-pay-gateways/mollie/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/wp-pay-gateways/mollie/compare/1.1.15...2.0.0
[1.1.15]: https://github.com/wp-pay-gateways/mollie/compare/1.1.14...1.1.15
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
