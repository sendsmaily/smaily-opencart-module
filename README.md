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

## Requirements

- PHP 5.6
- OpenCart 1.5.6.4

## Documentation & Support

Online documentation and code samples are available via our [Help Center](http://help.smaily.com/en/support/home).

## Contribute

All development for Smaily for OpenCart is [handled via GitHub](https://github.com/sendsmaily/smaily-opencart-module). Opening new issues and submitting pull requests are welcome.

## Installation
1. Upload or extract the `upload` folder contents to your site's `root` directory.
2. Install the plugin from the **Extensions** &rarr; **Modules** - menu in OpenCart.

## Usage

1. Go to **Extensions** &rarr; **Modules** &rarr; **Smaily for OpenCart**
2. Insert your Smaily API authentication information to get started.
3. Click Validate button to validate API credentials.
4. To add the subscription form to your homepage go to **Smaily for OpenCart** &rarr; **Edit** and click `Add Layout` and edit it to your prefered position.
5. That's it, your OpenCart store is now integrated with Smaily!

## Using Cron to Automate Customer Synchronization

To use cron for automated customer synchronization follow OpenCart instructions for [setting up cron](http://docs.opencart.com/en-gb/extension/cron/). URL for cron is provided in Smaily settings.

You need to provide a **token** as a parameter for security reasons so that only you can run cronjobs. Cron token is an URL query parameter that is checked when cron runs. Enter your token in Smaily admin page under **Subscriber Synchronization** &rarr; **Customer Cron Token**. Token can be any random text/number combination (preferably ASCII characters only).

> We recommend to run Customer Synchronization daily.

## Frequently Asked Questions

### How can I filter RSS-feed output by category and limit results?

Go to the RSS feed tab under **Extensions** &rarr; **Modules** &rarr; **Smaily for OpenCart** and select the category of products to be displayed.

> By default RSS-feed shows up to 50 last updated products.

## Screenshots found in /assets

1. OpenCart Smaily general settings screen.
2. OpenCart Smaily customer synchronisation settings screen.
3. OpenCart Smaily RSS-feed screen.
4. OpenCart Smaily form screen.
