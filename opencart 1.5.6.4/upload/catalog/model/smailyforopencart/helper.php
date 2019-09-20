<?php

class ModelSmailyForOpencartHelper extends Model {
    /**
     * Makes cURL call to Smaily API endpoint. Returns body or empty if error.
     *
     * @param string $endpoint Smaily API endpoint without .php
     * @param array $data      Data to send to smaily.
     * @param string $method   POST or GET
     * @return array $response Response body for success, empty if error.
     */
    public function apiCall($endpoint, array $data = [], $method = 'GET') {
        // Response.
        $response = [];
        // Smaily settings from database.
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('smaily')['smaily_api_credentials'];
        // Credentials.
        $username = $settings['username'];
        $password = $settings['password'];
        $subdomain = $settings['subdomain'];
        // Url.
        $apiUrl = 'https://' . $subdomain . '.sendsmaily.net/api/' . $endpoint . '.php';
        // cURL call.
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
            // Validate response.
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
     * @return array $customers All subscribed customers in array.
     */
    public function getSubscribedCustomers($offset) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer
            WHERE (`customer_id` > " . (int)$offset . " AND `newsletter` = '1') LIMIT 2500");
        return $query->rows;
    }

    /**
     * Sets newsletter status to 0 in customer table.
     *
     * @param array $emails Emails to unsubscribe.
     *
     * @return void
     */
    public function unsubscribeCustomers($emails) {
        foreach ($emails as $email) {
            $this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = '0'
                WHERE (`email` = '" . $email . "' AND `customer_id` <> 0)");
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
        $sync_additional = $this->load->model_setting_setting->getSetting('smaily')['smaily_customer_sync']['fields'];
        $sync_additional[] = 'email';
        return $sync_additional;
    }
}
