# Smaily for OpenCart

Smaily email marketing and automation module for OpenCart.

Automatically synchronize subscribed customers to Smaily, generate RSS-feed based on products for easy template import and set up opt-in form with CAPTCHA for collecting newsletter subscribers directly to Smaily.

[Changelog](CHANGELOG.md)

## Features

### OpenCart Newsletter Subscribers

- Synchronize subscribed customers directly from your store
- Get unsubscribers from Smaily unsubscribed list
- Update unsubscribed status in OpenCart users database

### OpenCart Products RSS-feed

- Generate RSS-feed with 50 latest updated products for easy import to Smaily template
- Option to customize generated RSS-feed based on products categories
- Option to limit generated RSS-feed products amount with prefered value

### Opt-in subscription form

- Smaily subscriber sign up form with built in CAPTCHA
- Easy to use form

### Abandoned cart reminder emails

- Automatically notify customers about their abandoned cart
- Send abandoned cart information to smaily for easy use on templates
- Set delay time when cart is considered abadoned

## Requirements

- PHP 5.6 (PHP 7.2+ is recommended)
- OpenCart 2.3.0.0 to 2.3.0.2

## Documentation & Support

Online documentation and code samples are available via our [Help Center](https://smaily.com/help/user-manuals/).

## Contribute

All development for Smaily for OpenCart is [handled via GitHub](https://github.com/sendsmaily/smaily-opencart-module). Opening new issues and submitting pull requests are welcome.

## Installation

Before starting the installation process, download the version of the module you wish to install under [releases](https://github.com/sendsmaily/smaily-opencart-module/releases).

> Please make sure downloaded module is compatible with your OpenCart installation!

### Using Extension Installer

**Note!** In order to use Extension Installer you need to configure FTP-settings (in **System** &rarr; **Settings** &rarr; **FTP**).

1. Log in to your OpenCart store administration panel.
2. Navigate to **Extensions** &rarr; **Extension Installer**.
3. Upload ZIP-file using Extension Installer's file browser.
4. Navigate to **Extensions**.
5. Select "Modules" extension type from the dropdown.
6. Look for `Smaily for OpenCart`, and install the module.

### Manual

1. Extract ZIP-file to your local path.
2. Using FTP (or SFTP) copy contents of `upload` directory to your OpenCart installations's `root` directory.
3. Navigate to **Extensions**.
4. Select "Modules" extension type from the dropdown.
5. Look for `Smaily for OpenCart`, and install the module.

## Usage

1. Go to **Extensions** &rarr; **Modules** &rarr; **Smaily for OpenCart**
2. Insert your Smaily API authentication information to get started.
3. Click Validate button to validate credentials.
4. Select if you want to use Cron for contact synchronization between OpenCart and Smaily
5. Select autoresponder for customer synchronization and extra fields you would like to add. Cron token must be added for security.
6. Select if you want to use Cron for sending abandoned cart emails.
7. Select autoresponder for abandoned carts, extra fields you would like to add to template and delay for abandoned carts. Cron token required for security.
8. To add subscription form to your homepage go to **Design** &rarr; **Layouts** &rarr; **Home** and add `Smaily for OpenCart` to your prefered position.
9. That's it, your OpenCart store is now integrated with Smaily!

## Using Cron to Automate Customer Synchronization and Sending Abandoned Carts Emails

To use cron for customer synchronization and for sending abandoned carts emails automatically follow opencart instructions for [setting up cron](http://docs.opencart.com/en-gb/extension/cron/). URL for cron is provided in Smaily settings.

You need to provide a **token** as a parameter for security reasons so that only you can run cronjobs. Cron token is an URL query parameter that is checked when cron runs. Enter your token in Smaily admin page under **Subscriber Synchronization** &rarr; **Customer Cron Token** and **Abandoned Cart** &rarr; **Abandoned Cart Token** fields. Token can be any random text/number combination (preferably ASCII characters only).

We recommend to run Customer Synchronization daily and Abandoned Carts not more often than every hour.

## Using Module to View Status of Abandoned Carts

To view the status of sent and pending abandoned carts you must open up the **Abandoned Cart Status** tab under module settings.

The section will be populated by unsent abandoned carts, this happens automatically when a registered customer adds items to their shopping cart. The cart will be considered abandoned when items have been sitting in the shopping cart longer than the configured **Abandoned cart delay time** and during which the customer has not finalized their order. If you have enabled Abandoned Cart in module settings and when the cart has been considered abandoned, an automated abandoned cart email will be sent to the customer. If an abandoned cart email has been successfully sent, the cart will be marked as SENT and the Date & Time of email will be saved to the table. The shopping cart contents of sent abandoned cart emails will not be saved to the table.

## Frequently Asked Questions

### How do I sort abandoned carts in the Abandoned Cart Status table?

Abandoned carts in the table can be sorted by: customer name, customer e-mail, email date & time and status under **Abandoned Cart Status** tab.
Sorting can be accomplished by clicking on the table headers of the same name (i.e status) marked blue. Clicking twice on them will change the order of sorting, to ascending or descending.

### How can I filter RSS-feed output by category and limit results?

Go to the RSS feed tab under **Extensions** &rarr; **Modules** &rarr; **Smaily for OpenCart** and select the category of products to be displayed.

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

1. OpenCart Smaily general settings screen.
2. OpenCart Smaily customer synchronisation settings screen.
3. OpenCart Smaily abadoned cart settings screen.
4. OpenCart Smaily RSS-feed screen.
5. OpenCart Smaily form screen.
