# Changelog

### 1.5.2

- Fix validation error not showing if RSS product limit higher than 250 - [[#194](https://github.com/sendsmaily/smaily-opencart-module/pull/194)]

### 1.5.1

- Fix RSS feed not displaying product pictures - [[#168](https://github.com/sendsmaily/smaily-opencart-module/issues/168)]

### 1.5.0

- Add admin page for managing abandoned carts - [[#65](https://github.com/sendsmaily/smaily-opencart-module/issues/65)]
- Fix abandoned cart CRON getting stuck - [[#124](https://github.com/sendsmaily/smaily-opencart-module/issues/124)]
- Display module version in admin area - [[#125](https://github.com/sendsmaily/smaily-opencart-module/issues/125)]
- Fix RSS product limit value error popping up when saving for first time - [[#138](https://github.com/sendsmaily/smaily-opencart-module/issues/138)]
- Fix unsubscribing customers not working by using small chunks of 500 - [[#146](https://github.com/sendsmaily/smaily-opencart-module/pull/146)]

### 1.4.0

- More user-friendly RSS URL generation with extra parameters - [[#96](https://github.com/sendsmaily/smaily-opencart-module/pull/96)]
- Unify use of first_name and last_name field names - [[#98](https://github.com/sendsmaily/smaily-opencart-module/pull/98)]
- Add language field to form - [[#103](https://github.com/sendsmaily/smaily-opencart-module/pull/103)]

### 1.3.2

- Abandoned cart sent status is reset when customer empties cart [[#73](https://github.com/sendsmaily/smaily-opencart-module/issues/73)]
- Abandoned cart status is now calculated from last item adding time instead of the first item [[#75](https://github.com/sendsmaily/smaily-opencart-module/issues/75)]
- Abandoned cart activation time is used to calculate the oldest abandoned cart. Abandoned cart emails are no longer sent to very old carts. [[#72](https://github.com/sendsmaily/smaily-opencart-module/issues/72)]

### 1.3.1

- Fix valid credentials not accepted when validating connection with Smaily

### 1.3.0

- Enable module on successful credentials validation
- Add section to reset module credentials

### 1.2.1

- Fixes customer synchronization cron not executing

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

- Abandoned cart autoresponder saving wrong value
- Additional fields list generating error log when no values selected

### 1.1.1

- Wrong url redirect after joining newsletter
- Additional sync values did't stay selected when there was form validaton error in settings page
- Added description for cron tokens

### 1.1.0

- Changes due to Smaily workflows automation
- No autoresponder needed for sign up form
- Subdomain parsed when full url entered in subdomain field
- Abandoned cart delay time 15 minutes
- Cron tokens automatically genereted when empty

### 1.0.0

- This is the first public release.
