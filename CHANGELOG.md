# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

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

[unreleased]: https://github.com/wp-pay-extensions/formidable-forms/compare/4.1.0...HEAD
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
