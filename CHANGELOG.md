# Changelog

### 1.5.0

- Add admin page for managing abandoned carts - [[#65](https://github.com/sendsmaily/smaily-opencart-module/issues/65)]
- Fix abandoned cart CRON getting stuck - [[#124](https://github.com/sendsmaily/smaily-opencart-module/issues/124)]
- Display module version in admin area - [[#125](https://github.com/sendsmaily/smaily-opencart-module/issues/125)]
- Fix RSS product limit value error popping up when saving for first time - [[#138](https://github.com/sendsmaily/smaily-opencart-module/issues/138)]
- Fix unsubscribing customers not working by using small chunks of 500 - [[#147](https://github.com/sendsmaily/smaily-opencart-module/pull/147)]

### 1.4.0

- More user-friendly RSS URL generation with extra parameters - [[#93](https://github.com/sendsmaily/smaily-opencart-module/pull/93)]
- Unify use of first_name and last_name field names - [[#99](https://github.com/sendsmaily/smaily-opencart-module/pull/99)]
- Add language field to form - [[#102](https://github.com/sendsmaily/smaily-opencart-module/pull/102)]

### 1.3.2

- Abandoned cart sent status is reset when customer empties cart [[#73](https://github.com/sendsmaily/smaily-opencart-module/issues/73)]
- Abandoned cart status is now calculated from last item adding time instead of the first item [[#75](https://github.com/sendsmaily/smaily-opencart-module/issues/75)]
- Abandoned cart activation time is used to calculate the oldest abandoned cart. Abandoned cart emails are no longer sent to very old carts. [[#72](https://github.com/sendsmaily/smaily-opencart-module/issues/72)]
- Module created event hooks are now removed when uninstalling module [[#81](https://github.com/sendsmaily/smaily-opencart-module/issues/81)]

### 1.3.1

- Fix valid credentials not accepted when validating connection with Smaily

### 1.3.0

- Enable module on successful credentials validation
- Add section to reset module credentials

### 1.2.1

- Fixes last customer synchronization time not being updated

### 1.2.0

**Breaking change**

This update adds upgrade functionality to our plugin.
For further updates to work please uninstall older version and reinstall this module.

- Standardize abandoned cart additional fields across integrations
- Change `firstname` to `first_name` and `lastname` to `last_name`
- Add `product_base_price` field. Product `price` and `base_price` fields now pass prices with currency symbol.
- Add `product_sku` field.
- Fix bug where running customer synchronization throws PHP error.

### 1.1.4

- Customer synchronization is now split into chunks, increases stability.
- Full URL for CRON links is now displayed.

### 1.1.3

- Add support for PHP 5.6

### 1.1.2

Bugfix

- Abandoned cart autoresponder saving wrong value
- Additional fields list generating error log when no values selected

### 1.1.1

Bugfixes:

- Wrong URL redirect after joining newsletter
- Additional sync values did't stay selected when there was form validaton error in settings page
- OpenCart 3+ version no autoresponder error message when enabling abandoned cart sync
- Added description for cron tokens

### 1.1.0

- Changes due to Smaily workflows automation
- No autoresponder needed for sign up form
- Subdomain parsed when full URL entered in subdomain field
- Abandoned cart delay time 15 minutes
- Cron tokens automatically genereted when empty

### 1.0.0

- This is the first public release.
