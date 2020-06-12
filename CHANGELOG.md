# Changelog

### 1.2.0

**Breaking change**

This update adds upgrade functionality to our plugin.
For further updates to work please uninstall older version and reinstall this module.

- Change `firstname` to `first_name` and `lastname` to `last_name`
- Fix bug where running customer synchronization throws PHP error.


### 1.1.4

- Customer synchronization is now split into chunks, increases stability.
- Full URL for CRON links is now displayed.

### 1.1.3

- Add support for PHP 5.6

### 1.1.2

Bugfix

- Additional fields list generating error log when no values selected

### 1.1.1

Bugfixes:

- Wrong URL redirect after joining newsletter
- Additional sync values did't stay selected when there was form validaton error in settings page
- Added description for cron tokens

### 1.1.0

- Changes due to Smaily workflows automation
- No autoresponder needed for sign up form
- Subdomain parsed when full URL entered in subdomain field
- Cron tokens automatically genereted when empty

### 1.0.0

- This is the first public release.
