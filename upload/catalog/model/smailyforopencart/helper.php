<?php

class ModelSmailyForOpencartHelper extends Model {
    /**
     * Makes cUrl call to smaily api endpoint. Returns body or empty if error.
     *
     * @param string $endpoint Smaily api endpoint without .php
     * @param array $data      Data to send to smaily.
     * @param string $method   POST or GET
     * @return array $response Response body for success, empty if error.
     */
    public function apiCall($endpoint, array $data = [], $method = 'GET') {
        // Response.
        $response = [];
        // Smaily settings from database.
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');
        // Credentials
        $username = $settings['smaily_for_opencart_username'];
        $password = $settings['smaily_for_opencart_password'];
        $subdomain = $settings['smaily_for_opencart_subdomain'];
        // Url.
        $apiUrl = 'https://' . $subdomain . '.sendsmaily.net/api/' . $endpoint . '.php';
        // Curl call.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

        // API call with GET request.
        if ($method === 'GET') {
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_URL, $apiUrl . '?' . http_build_query($data));
            }
            $api_call = json_decode(curl_exec($ch), true);
            // Response code from Smaily API.
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // If Method POST.
        } elseif ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $api_call = json_decode(curl_exec($ch), true);
            // Response code from Smaily API.
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // Validate response
            if (!array_key_exists('code', $api_call)) {
                $this->log->write('Something went wrong with Smaily request!');
            }
            if (isset($api_call['code']) && (int) $api_call['code'] !== 101) {
                $this->log->write($api_call['message']);
            }
        }
        // Return response if success.
        if ($http_code === 200) {
            // POST.
            if ($method === 'POST') {
                if (array_key_exists('code', $api_call) && (int) $api_call['code'] === 101) {
                    $response = $api_call;
                }
            // GET.
            } else {
                $response = $api_call;
            }
        } else {
            // Log error.
            $this->log->write('Error in smaily api call with code: ' . $http_code);
        }
        // Response from API call.
        return $response;
    }

    /**
     * Get all subscribed customers.
     *
     * @param int $offset Id counter
     * @return array $customers All subscribed customers in array.
     */
    public function getSubscribedCustomers($offset, $sync_time) {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "customer
            WHERE (`customer_id` > " . (int)$offset . " AND `newsletter` = '1'
            AND `date_added` > " . "'" . $this->db->escape($sync_time) . "')" .
            " LIMIT 2500"
        );
        return $query->rows;
    }

    /**
     * Sets newsletter status to 0 in customer table.
     *
     * @param array $emails Emails to unsubscribe.
     * @return void
     */
    public function unsubscribeCustomers($emails) {
        // Split email array to chunks of 500, in case query is too long.
        $chunks = array_chunk($emails, 500);
        foreach ($chunks as $chunk) {
            $binds = array();
            foreach ($emails as $email) {
                $binds[] = $this->db->escape($email);
            }
            // Add all emails to long string seperated by commas.
            $this->db->query(
                "UPDATE " . DB_PREFIX . "customer SET newsletter = '0' WHERE `email` IN ('" . implode("','", $binds) . "')");
        }
    }

    /**
     * Get additional sync fields from settings table.
     *
     * @return array $sync_additional Fields to sync.
     */
    public function getSyncFields() {
        $this->load->model('setting/setting');
        // Null if no additional fields provided.
        $sync_additional = $this->config->get('smaily_for_opencart_syncronize_additional');
        $sync_additional[] = 'email';
        return $sync_additional;
    }

    /**
     * Get abandoned cart additional fields from settings table
     *
     * @return array $cart_additional Additional fields to sync.
     */
    public function getAbandonedSyncFields() {
        $fields = [];
        $this->load->model('setting/setting');
        // Null if no additional fields provided.
        $cart_additional = $this->config->get('smaily_for_opencart_abandoned_additional');
        if ($cart_additional) {
            $fields = $cart_additional;
        }
        return $fields;
    }

    /**
     * Get carts from db before delay time.
     *
     * @return void
     */
    public function getAbandonedCarts() {
        $this->load->model('setting/setting');
        $this->load->model('catalog/product');
        // Get delay time.
        $delay_time = $this->config->get('smaily_for_opencart_cart_delay');
        // Get abandoned cart activation time.
        $start_time = $this->config->get('smaily_for_opencart_abandoned_cart_time');
        $abandoned_carts = [];
        // Select all customers with abandoned carts. Last cart item addition time - delay time.
        // And customer doesn't have record in smaily_abandoned_carts table.
        // Customer data available id, email, firstname, lastname, last_date_added.
        $customers = $this->db->query(
            "SELECT cart.customer_id, customer.email, customer.firstname, customer.lastname, " .
            "MAX(cart.date_added) AS last_date_added " .
            "FROM " . DB_PREFIX . "cart AS cart " .
            "LEFT JOIN " . DB_PREFIX . "customer AS customer " .
            "ON cart.customer_id = customer.customer_id " .
            "LEFT JOIN " . DB_PREFIX . "smaily_abandoned_carts AS smaily " .
            "ON cart.customer_id = smaily.customer_id " .
            "WHERE smaily.customer_id IS NULL " .
            "AND cart.customer_id > '0' " .
            "GROUP BY cart.customer_id " .
            "HAVING last_date_added <= DATE_SUB(NOW(), INTERVAL " . (int) $delay_time . " MINUTE)" .
            "AND last_date_added >= '" . $this->db->escape($start_time) . "'"
        );

        // Select all products and quantities for customer.
        foreach ($customers->rows as $customer) {
            $products = $this->db->query(
                "SELECT product_id, quantity " .
                "FROM " . DB_PREFIX . "cart " .
                "WHERE customer_id = '" . (int) $customer['customer_id'] . "'"
            );

            // Prepare products array.
            $products_list = [];
            foreach ($products->rows as $product) {
                // Get product data by id.
                $prod_data = $this->model_catalog_product->getProduct($product['product_id']);
                array_push($products_list, array(
                    'product_id' => $product['product_id'],
                    'data' => $prod_data,
                    'quantity' => $product['quantity']
                ));
            }

            // Add customer info and products data to abandoned_carts array.
            $abandoned_carts[] = array(
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'products' => $products_list
            );
        }

        return $abandoned_carts;
    }

    public function addSentCart($customer_id) {
        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "smaily_abandoned_carts (customer_id, sent_time)" .
            "VALUES (" . "'" . (int) $customer_id . "', NOW())"
        );
    }

    /**
     * Get ISO sync time from settings and convert it to MySQL format.
     *
     * @return string $sync_time Time of last sync
     */
    public function getSyncTime() {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');
        $sync_time = date('c', 0); // First sync failsafe.
        // Previous sync time.
        if (array_key_exists('smaily_for_opencart_sync_time', $settings)) {
            $sync_time = $settings['smaily_for_opencart_sync_time'];
        }

        return $sync_time;
    }

    public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
        if (!is_array($value)) {
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "', serialized = '0'  WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
        } else {
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(json_encode($value)) . "', serialized = '1' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
        }
    }

    /**
     * Checks if customer cart is empty.
     *
     * @param int $customer_id
     * @return boolean
     */
    public function isCartEmpty($customer_id) {
        $query = $this->db->query(
            "SELECT COUNT(*) AS cart_items FROM " . DB_PREFIX . "cart " .
            "WHERE customer_id='" . $this->db->escape($customer_id) ."'"
        );

        $data = $query->row;
        if ((int) $data['cart_items'] != 0) {
            return false;
        };

        return true;
    }
}
