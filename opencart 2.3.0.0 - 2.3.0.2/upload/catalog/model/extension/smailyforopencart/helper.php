<?php

class ModelExtensionSmailyForOpencartHelper extends Model{
    /**
     * Makes cUrl call to smaily api endpoint. Returns body or empty if error.
     *
     * @param string $endpoint Smaily api endpoint without .php
     * @param array $data      Data to send to smaily.
     * @param string $method   POST or GET
     * @return array $response Response body for success, empty if error.
     */
    public function apiCall(string $endpoint, array $data = [], $method = 'GET') {
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
            $api_call = json_decode(curl_exec($ch),true);
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
                if (array_key_exists('code',$api_call) && (int) $api_call['code'] === 101){
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
     * @return array $customers All subscribed customers in array.
     */
    public function getSubscribedCustomers() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE newsletter = '1'");
        return $query->rows;
    }

    /**
     * Sets newsletter status to 0 in customer table.
     *
     * @param string $customer_id Customers id.
     * @return void
     */
    public function unsubscribeCustomer($customer_id) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = '0' WHERE customer_id = '" . (int)$customer_id . "'");
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
    public function getAbandonedSyncFields(){
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
        $abandoned_carts = [];
        // Select all customers with abandoned carts. Carts before now - delay time.
        // And customer doesn't have record in smaily_abandoned_carts table.
        // Customer data available id, email, firstname, lastname.
        $customers = $this->db->query(
            "SELECT cart.customer_id, customer.email, customer.firstname, customer.lastname " .
            "FROM " . DB_PREFIX . "cart AS cart " .
            "LEFT JOIN " . DB_PREFIX . "customer AS customer " .
            "ON cart.customer_id = customer.customer_id " .
            "LEFT JOIN " . DB_PREFIX . "smaily_abandoned_carts AS smaily " .
            "ON cart.customer_id = smaily.customer_id " .
            "WHERE smaily.customer_id IS NULL " .
            "AND cart.date_added <= DATE_SUB(NOW(), INTERVAL " . (int) $delay_time . " MINUTE) " .
            "AND cart.customer_id > '0' " .
            "GROUP BY cart.customer_id"
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

}