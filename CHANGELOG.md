# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

## [4.3.2] - 2023-01-31
### Composer

- Changed `php` from `>=8.0` to `>=7.4`.
Full set of changes: [`4.3.1...4.3.2`][4.3.2]

[4.3.2]: https://github.com/pronamic/wp-pronamic-pay-formidable-forms/compare/v4.3.1...v4.3.2

## [4.3.1] - 2023-01-18

### Commits

- Fixed "Undefined array key" notices. ([4677a8d](https://github.com/pronamic/wp-pronamic-pay-formidable-forms/commit/4677a8d51a7640c08d9d921a1b01eb1316a8ddc9))
- Happy 2023. ([69a4bcc](https://github.com/pronamic/wp-pronamic-pay-formidable-forms/commit/69a4bccf474eeb7fb7930f70381cba8b253497ef))

Full set of changes: [`4.3.0...4.3.1`][4.3.1]

[4.3.1]: https://github.com/pronamic/wp-pronamic-pay-formidable-forms/compare/v4.3.0...v4.3.1

## [4.3.0] - 2022-12-23

### Commits

- Added https://github.com/WordPress/wp-plugin-dependencies. ([e35ee01](https://github.com/pronamic/wp-pronamic-pay-formidable-forms/commit/e35ee019d9b6bf821330a7c77930b557caedaa2a))
- No longer use `filter_` functions and deprecated `FILTER_SANITIZE_STRING`. ([58cb81a](https://github.com/pronamic/wp-pronamic-pay-formidable-forms/commit/58cb81a9d7a881d9a15ee6f361485f114cc6f1c8))

### Composer

- Changed `php` from `>=5.6.20` to `>=8.0`.
- Changed `wp-pay/core` from `^4.4` to `v4.6.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.2.1
Full set of changes: [`4.2.1...4.3.0`][4.3.0]

[4.3.0]: https://github.com/pronamic/wp-pronamic-pay-formidable-forms/compare/v4.2.1...v4.3.0

## [4.2.1] - 2022-09-27
- Update to `wp-pay/core` version `^4.4`.

## [4.2.0] - 2022-09-26
- Updated for new payment methods and fields registration.

## [4.1.1] - 2022-08-15
- Fixed not showing error messages if form success action is not 'message'.

## [4.1.0] - 2022-04-11
- Add payment action setting for gateway configuration.

## [4.0.0] - 2022-01-10
### Changed
- Updated to https://github.com/pronamic/wp-pay-core/releases/tag/4.0.0.
- Added support for checkboxes field type.
- Don't force description to have a dynamic part (use `[id]` in transaction description setting instead).

## [3.0.0] - 2021-08-05
- Updated to `pronamic/wp-pay-core`  version `3.0.0`.
- Updated to `pronamic/wp-money`  version `2.0.0`.
- Changed `TaxedMoney` to `Money`, no tax info.
- Switched to `pronamic/wp-coding-standards`.
- Ignore unsupported recurring-only payment methods in payment method select field.

## [2.2.1] - 2021-01-21
- Fixed using undefined variable.
- Removed debug code.

## [2.2.0] - 2021-01-14
- Simplified icon hover style.
- Updated form action icon.
- Added support for form settings redirect success URL.
- Removed payment data class.

## [2.1.4] - 2020-11-09
- Improved error handling on payment start.
- Fixed incorrect amount when using product fields.

## [2.1.3] - 2020-06-02
- Add payment origin post ID.

## [2.1.2] - 2020-04-20
- Updated settings description for delaying email notifications.

## [2.1.1] - 2020-04-03
- Set plugin integration name.

## [2.1.0] - 2020-03-19
- Extension extends abstract plugin integration with dependency.

## [2.0.4] - 2019-12-22
- Improved error handling with exceptions.
- Updated usage of deprecated `addItem()` method.
- Updated payment status class name.

## [2.0.3] - 2019-08-26
- Updated packages.

## [2.0.2] - 2019-05-15
- Improve support for AJAX enabled forms.

## [2.0.1] - 2018-12-12
- Use renamed item methods in payment data.

## [2.0.0] - 2018-05-14
- Switched to PHP namespaces.

## [1.0.2] - 2017-01-25
- Added filter for payment source link and description.

## [1.0.1] - 2016-03-23
- Added support for transaction description.

## 1.0.0 - 2015-11-05
- First release.

[unreleased]: https://github.com/pronamic/wp-pronamic-pay-formidable-forms/compare/4.2.1...HEAD
[4.2.1]: https://github.com/pronamic/wp-pronamic-pay-formidable-forms/compare/4.2.0...4.2.1
[4.2.0]: https://github.com/pronamic/wp-pronamic-pay-formidable-forms/compare/4.1.1...4.2.0
[4.1.1]: https://github.com/pronamic/wp-pronamic-pay-formidable-forms/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/wp-pay-extensions/formidable-forms/compare/4.0.0...4.1.0
[4.0.0]: https://github.com/wp-pay-extensions/formidable-forms/compare/3.0.0...4.0.0
[3.0.0]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.2.1...3.0.0
[2.2.1]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.1.4...2.2.0
[2.1.4]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.1.3...2.1.4
[2.1.3]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.0.4...2.1.0
[2.0.4]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/wp-pay-extensions/formidable-forms/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/wp-pay-extensions/formidable-forms/compare/1.0.2...2.0.0
[1.0.2]: https://github.com/wp-pay-extensions/formidable-forms/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/wp-pay-extensions/formidable-forms/compare/1.0.0...1.0.1
