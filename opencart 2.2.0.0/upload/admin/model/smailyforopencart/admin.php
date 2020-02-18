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


    public function upgrade() {
        // Load models.
        $this->load->model('extension/modification');
        $this->load->model('setting/setting');
        // Module modification settings.
        $smaily_module = $this->model_extension_modification->getModificationByCode('smaily_for_opencart_extension');
        
        // Stop if module hasn't been installed with adding it as a modification.
        if (empty($smaily_module)) {
            return;
        }

        $version = $smaily_module['version'];

        // Version 1.2.0 - standardize abandoned cart fields.
        if (version_compare($version, '1.2.0', '=')) {
            $cart_additional = $this->config->get('smaily_for_opencart_abandoned_additional');

            // Add fist_name and last_name if abandoned cart was active. Previous default values.
            $enabled = $this->config->get('smaily_for_opencart_enable_abandoned');
            if ((int) $enabled === 1) {
                // If there was no additional fields before add defaults.
                if (! $cart_additional) {
                    $this->model_setting_setting->editSettingValue(
                        'smaily_for_opencart',
                        'smaily_for_opencart_abandoned_additional',
                        ['first_name', 'last_name']
                    );
                } else {
                    // Add new fields to the list.
                    if (!in_array('first_name', $cart_additional)) {
                        array_push($cart_additional, 'first_name');
                    }

                    if (!in_array('last_name', $cart_additional)) {
                        array_push($cart_additional, 'last_name');
                    }

                    $this->model_setting_setting->editSettingValue(
                        'smaily_for_opencart',
                        'smaily_for_opencart_abandoned_additional',
                        $cart_additional
                    );
                }
            }
        }
    }

    /**
     * Remove smaily_abandoned_carts table.
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
}
