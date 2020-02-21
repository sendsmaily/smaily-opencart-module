# Smaily for Opencart

Simple and flexible Smaily integration for Opencart.

## Description

Smaily email marketing and automation extension module for Opencart.

Automatically subscribe newsletter subscribers to a Smaily subscribers list, generate rss-feed based on products for easy template import, send abandoned cart emails with smaily templates and generate opt-in form with CAPTCHA for newsletter subscriptions directly to your Smaily account.

## Features

### Opencart Newsletter Subscribers

- Synchronize subscribed customers directly from your store
- Get unsubscribers from Smaily unsubscribed list
- Update unsubscribed status in Opencart users database

### Opencart Products RSS-feed

- Generate RSS-feed with 50 latest products for easy import to Smaily template
- Option to customize generated RSS-feed based on products categories
- Option to limit generated RSS-feed products amount with prefered value

### Opt-in subscription form

- Smaily subscriber sign up form with built in captcha
- Easy to use form

### Abandoned cart reminder emails (Opencart 2.2+)

- Automatically notify customers about their abandoned cart
- Send abandoned cart information to smaily for easy use on templates
- Set delay time when cart is considered abadoned

## Requirements

Smaily for Opencart requires PHP 5.6+ (PHP 7.0+ recommended). You'll also need to be running Opencart 1.5.6.4 or Opencart 2.2+.

## Documentation & Support

Online documentation and code samples are available via our [Help Center](http://help.smaily.com/en/support/home).

## Contribute

All development for Smaily for Opencart is [handled via GitHub](https://github.com/sendsmaily/smaily-opencart-module). Opening new issues and submitting pull requests are welcome.

## Installation (Opencart 2.2+)

1. Upload or extract the `opencart-[version]/upload` folder content to your site's `root` directory.
2. If you have configured your FTP-settings in `system->settings->FTP` you can also use built in extension installer. For Opencart 3+ there is no need to configure FTP settings-
3. Upload `smailyforopencart.ocmod.zip` file under **Extensions->Extension Installer**.
4. Install the plugin from the **Extensions->Modules** - menu in Opencart.

## Installation (Opencart 1.5.6.4)
1. Upload or extract the `opencart-[version]/upload` folder content to your site's `root` directory.
2. Install the plugin from the **Extensions->Modules** - menu in Opencart.

## Usage

1. Go to Extensions -> Modules -> Smaily for Opencart
2. Insert your Smaily API authentication information to get started.
3. Click Validate button to validate credentials.
4. Select if you want to use Cron for contact synchronization between Opencart and Smaily
5. Select autoresponder for customer synchronization and extra fields you would like to add. Cron token must be added for security.
6. Select if you want to use Cron for sending abandoned cart emails.
7. Select autoresponder for abandoned carts, extra fields you would like to add to template and delay for abandoned carts. Cron token required for security.
8. To add subscription form to your homepage go to **Design->Layouts->Home** and add `Smaily for Opencart` to your prefered position.
9. That's it, your Opencart store is now integrated with Smaily Plugin!

## Usage (Opencart 1.5.6.4)

1. Go to Extensions -> Modules -> Smaily for Opencart
2. Insert your Smaily API authentication information to get started.
3. Click Validate button to validate API credentials.
4. To add the subscription form to your homepage go to **Smaily for Opencart -> Edit** and click `Add Layout` and edit it to your prefered position.
5. That's it, your Opencart store is now integrated with Smaily!

## Using Cron to Automate Customer Synchronization and Sending Abandoned Carts Emails

To use cron for customer synchronization and for sending abandoned carts emails automatically follow opencart instructions for [setting up cron](http://docs.opencart.com/en-gb/extension/cron/). Url for cron is provided in Smaily settings.

You need to provide a **token** as a parameter for security reasons so that only you can run cronjobs. Cron token is an url-parameter that is checked when cron runs. Enter your token in Smaily admin page under **Subscriber Synchronization->Customer Cron Token** and **Abandoned Cart->Abandoned Cart Token** fields. Token can be any random text/number combination.

We recommend to run Customer Synchronization daily and Abandoned Carts not more often than every hour.

## Frequently Asked Questions

### Why RSS-feed is not displaying products from category?

Product categories must be entered as found from **Catalog -> Categories** page and must be url-encoded. For example category `Laptops & Notebooks` becomes `Laptops%20%26%20Notebooks`. Lower- and uppercase also matters.

### How can I filter RSS-feed output by category and limit results?

You can access RSS feed by visiting ulr `[store_url]/index.php?route=smailyforopencart/rss` or `index.php?route=extension/smailyforopencart/rss` for version 2.3+ and you can add parameters (category and limit) by appending them to url. For example `store_url/index.php?route=smailyforopencart/rss&category=Laptops%20%26%20Notebooks&limit=3`. Regular RSS-feed shows 50 last updated products.

### How can I access additional Abandoned cart parameters in Smaily template editor?

List of all parameters available in Smaily email templating engine:

- Customer first name: `{{ first_name }}`.

- Customer last name: `{{ last_name }}`.

Up to 10 products can be received in Smaily templating engine. You can refrence each product with number 1-10 behind parameter name.

- Product name: `{{ product_name_[1-10] }}`.

- Product description: `{{ product_description_[1-10] }}`.

- Product quantity: `{{ product_quantity_[1-10] }}`.

- Product SKU: `{{ product_sku_[1-10] }}`.

- Products price: `{{ product_price_[1-10] }}`.

- Products base price: `{{ product_base_price_[1-10] }}`.

Also you can determine if customer had more than 10 items in cart

- More than 10 items: `{{ over_10_products }}`.

## Screenshots found in /assets

1. Opencart Smaily general settings screen.
2. Opencart Smaily customer synchronisation settings screen.
3. Opencart Smaily abadoned cart settings screen.
4. Opencart Smaily RSS-feed screen.
5. Opencart Smaily form screen.

## Changelog

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

- Wrong url redirect after joining newsletter
- Additional sync values did't stay selected when there was form validaton error in settings page
- Opencart 3+ version no autoresponder error message when enabling abandoned cart sync

- Added description for cron tokens

### 1.1.0

- Changes due to Smaily workflows automation
- No autoresponder needed for sign up form
- Subdomain parsed when full url entered in subdomain field
- Abandoned cart delay time 15 minutes
- Cron tokens automatically genereted when empty

### 1.0.0

- This is the first public release.
