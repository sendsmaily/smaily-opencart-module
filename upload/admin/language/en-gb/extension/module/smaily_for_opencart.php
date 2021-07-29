<?php

/**
 * Prefix legend:
 *
 * button      - Used for button texts;
 * heading     - Used for headers (h1, h2, etc.);
 * help        - Used for small helper/description texts next to an input field;
 * label       - Used for input field label texts;
 * placeholder - Used for input field placeholder texts;
 * section     - Used for tab naming;
 * text        - Used for generic texts;
 */

$_['heading_title'] = 'Smaily for OpenCart';

$_['text_ascending'] = 'Ascending';
$_['text_descending'] = 'Descending';
$_['text_minutes'] = 'minutes';
$_['text_module'] = 'Modules';
$_['text_never'] = 'Never';
$_['text_products'] = 'products';

// Connection status.
$_['heading_connection_status'] = 'Connection status';

$_['help_connected'] = 'Smaily for OpenCart is connected to <strong>%subdomain%.sendsmaily.net</strong> using API user <strong>%username%</strong>.';
$_['text_connected'] = 'Connected';

$_['help_disconnected'] = 'In order to use Smaily for OpenCart you need to connect the module to your Smaily account.';
$_['text_disconnected'] = 'Disconnected';

// General.
$_['heading_edit'] = 'General';

$_['label_enabled'] = 'Enabled';

// API credentials.
$_['help_api_password'] = 'How to create API credentials?';
$_['label_api_password'] = 'API password';
$_['placeholder_api_password'] = 'Please enter API password';

$_['label_api_subdomain'] = 'Account subdomain';
$_['placeholder_api_subdomain'] = 'Please enter account subdomain';

$_['label_api_username'] = 'API username';
$_['placeholder_api_username'] = 'Please enter API username';

// Customer Synchronization.
$_['heading_customer_sync'] = 'Customer Synchronization';

$_['help_customer_sync_cron_url'] = 'Use this URL to run Customer Synchronization cron job.';
$_['label_customer_sync_cron_url'] = 'Cron URL';

$_['label_customer_sync_fields'] = 'Synchronization fields';
$_['help_customer_sync_fields'] = 'Select fields you wish to synchronize to Smaily.';

$_['text_reset_customer_sync_cron_token'] = 'Reset cron token';

// Customer Synchronization field options.
$_['customer_sync_field_option_date_added'] = 'Date added';
$_['customer_sync_field_option_firstname'] = 'First name';
$_['customer_sync_field_option_lastname'] = 'Last name';
$_['customer_sync_field_option_telephone'] = 'Telephone';

// Abandoned Cart.
$_['heading_abandoned_cart'] = 'Abandoned Cart';

$_['help_abandoned_cart_autoresponder'] = 'Only automation workflows built with "form submitted" trigger are listed.';
$_['label_abandoned_cart_autoresponder'] = 'Autoresponder ID';

$_['help_abandoned_cart_cron_url'] = 'Use this URL to run Abandoned Cart cron job.';
$_['label_abandoned_cart_cron_url'] = 'Cron URL';

$_['help_abandoned_cart_delay'] = 'Time when cart is considered abandoned. Minimum 15 minutes.';
$_['label_abandoned_cart_delay'] = 'Delay time';

$_['help_abandoned_cart_fields'] = 'Select fields you wish to use in an Abandoned Cart message.';
$_['label_abandoned_cart_fields'] = 'Additional fields';

$_['text_reset_abandoned_cart_cron_token'] = 'Reset cron token';

$_['text_missing_automation_workflows'] = 'No automation workflows';

// Abandoned Cart field options.
$_['abandoned_cart_field_option_base_price'] = 'Product base price';
$_['abandoned_cart_field_option_description'] = 'Product description';
$_['abandoned_cart_field_option_first_name'] = 'Customer first name';
$_['abandoned_cart_field_option_last_name'] = 'Customer last name';
$_['abandoned_cart_field_option_name'] = 'Product name';
$_['abandoned_cart_field_option_price'] = 'Product price';
$_['abandoned_cart_field_option_quantity'] = 'Product quantity';
$_['abandoned_cart_field_option_sku'] = 'Product SKU';

// RSS.
$_['heading_rss'] = 'Products RSS feed';

$_['label_rss_category'] = 'Category';

$_['help_rss_feed_url'] = 'Copy this URL into your template editor\'s RSS block to import products.';
$_['label_rss_feed_url'] = 'RSS feed URL';

$_['help_rss_limit'] = 'Number of products in the feed. Must be between 1 and 250.';
$_['label_rss_limit'] = 'Limit';

$_['label_rss_sort_by'] = 'Sort by';

$_['label_rss_sort_order'] = 'Sort order';

$_['text_all_products'] = 'All products';

// RSS feed sort options.
$_['rss_sort_option_p_model'] = 'Model';
$_['rss_sort_option_p_price'] = 'Price';
$_['rss_sort_option_p_sort_order'] = 'Sort Order';
$_['rss_sort_option_p_status'] = 'Status';
$_['rss_sort_option_pd_name'] = 'Name';

// Abandoned Cart status table.
$_['heading_abandoned_carts'] = 'Abandoned Carts';

$_['label_abandoned_cart_table_cart'] = 'Shopping Cart';
$_['label_abandoned_cart_table_date']= 'Email Date & Time';
$_['label_abandoned_cart_table_email'] = 'Customer E-Mail';
$_['label_abandoned_cart_table_id'] = 'Customer ID';
$_['label_abandoned_cart_table_name'] = 'Customer Name';
$_['label_abandoned_cart_table_status'] = 'Status';

$_['text_sent'] = 'sent';
$_['text_pending'] = 'pending';

// Error messages.
$_['error_success'] = 'Smaily for OpenCart settings changed!';

$_['error_abandoned_cart_autoresponder_not_selected'] = 'Please select Abandoned Cart automation workflow';
$_['error_abandoned_cart_delay_minimum'] = 'Delay time must be at least 15 minutes';
$_['error_api_notfound'] = 'Wrong subdomain!';
$_['error_api_password_empty'] = 'Password can\'t be empty';
$_['error_api_subdomain_empty'] = 'Subdomain can\'t be empty';
$_['error_api_unauthorized'] = 'Wrong credentials!';
$_['error_api_unknown'] = 'Something went wrong with validating!';
$_['error_api_username_empty'] = 'Username can\'t be empty';
$_['error_permission'] = 'Warning: You do not have permission to modify Smaily for OpenCart!';
$_['error_rss_limit_exceeded'] = 'Products RSS limit must be between 1 and 250';

// Buttons.
$_['button_reset_credentials'] = 'Reset API credentials';
