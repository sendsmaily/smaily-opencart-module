<?php

class ModelSmailyForOpencartAdmin extends Model {

    /**
     * Create smaily_abandoned_carts table.
     *
     * @return void
     */
    public function install() {
        // Create smaily abandoned cart database.
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "smaily_abandoned_carts (
            customer_id int(11) NOT NULL,
            sent_time datetime NOT NULL,
            PRIMARY KEY (customer_id)
            )"
        );
    }

    /**
     * Remove smaily_abandoned_carts table and saved settings.
     *
     * @return void
     */
    public function uninstall() {
        $this->load->model('setting/setting');
        // Remove abandoned carts table.
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "smaily_abandoned_carts");
        // Remove plugin settings.
        $this->model_setting_setting->deleteSetting('smaily_for_opencart');
    }

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

    public function getAbandonedCartsForTemplate($data = array()) {
        $this->load->model('setting/setting');
        $this->load->model('catalog/product');
        // Get delay time.
        $delay_time = $this->config->get('module_smaily_for_opencart_cart_delay');
        // Get abandoned cart activation time.
        $start_time = $this->config->get('module_smaily_for_opencart_abandoned_cart_time');
        $abandoned_carts = [];
        // Select all customers with abandoned carts. Last cart item addition time - delay time.
        // Add customers who have record in smaily_abandoned_carts table.
        $sql = (
            "SELECT cart.customer_id, customer.email, customer.firstname, customer.telephone, customer.lastname, " .
            "MAX(cart.date_added) AS last_date_added, smaily.sent_time, " .
            "IF(smaily.customer_id IS NOT NULL, 1, 0) AS is_sent " .
            "FROM " . DB_PREFIX . "cart AS cart " .
            "LEFT JOIN " . DB_PREFIX . "customer AS customer " .
            "ON cart.customer_id = customer.customer_id " .
            "LEFT JOIN " . DB_PREFIX . "smaily_abandoned_carts AS smaily " .
            "ON cart.customer_id = smaily.customer_id " .
            "WHERE cart.customer_id > '0' " .
            "GROUP BY cart.customer_id " .
            "HAVING last_date_added <= DATE_SUB(NOW(), INTERVAL " . (int) $delay_time . " MINUTE) " .
            "AND last_date_added >= '" . $this->db->escape($start_time) . "'"
        );

        $sort_options = array(
            'lastname',
            'email',
            'sent_time',
            'is_sent'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_options)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY customer_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $customers = $this->db->query($sql);
        foreach ($customers->rows as $customer) {
            // Prepare products array.
            $products_list = [];
            // Products are unknown for sent abandoned carts, skipping it here.
            // Only customer id and date is saved to smaily abandoned cart table.
            if ((int)$customer['is_sent'] !== 1) {
                $products = $this->db->query(
                    "SELECT product_id, quantity " .
                    "FROM " . DB_PREFIX . "cart " .
                    "WHERE customer_id = '" . (int) $customer['customer_id'] . "'"
                );

                foreach ($products->rows as $product) {
                    // Get product data by id.
                    $prod_data = $this->model_catalog_product->getProduct($product['product_id']);
                    array_push($products_list, array(
                        'product_id' => $product['product_id'],
                        'data' => $prod_data,
                        'quantity' => $product['quantity']
                    ));
                }
            }

            // Add customer info and products data to abandoned_carts array.
            $abandoned_carts[] = array(
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'is_sent' => $customer['is_sent'],
                'sent_time' => $customer['sent_time'],
                'products' => $products_list
            );
        }

        return $abandoned_carts;
    }
}
