<?php

class ModelSmailyForOpencartHelper extends Model {
    private $_credentials = NULL;

    /**
     * Gets Smaily API credentials from settings.
     *
     * @return array Smaily API credentials.
     */
    private function getApiCredentials() {
        if (is_null($this->_credentials)) {
            $this->load->model('setting/setting');
            $credentials = $this->model_setting_setting->getSetting('smaily')['smaily_api_credentials'];
            $this->_credentials = is_array($credentials) ? $credentials : [
                'password' => NULL,
                'subdomain' => NULL,
                'username' => NULL,
            ];
        }

        return $this->_credentials;
    }

    /**
     * Get all subscribed customers.
     *
     * @param int $offset Id counter
     * @return array $customers All subscribed customers in array.
     */
    public function getSubscribedCustomers($offset) {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "customer WHERE (`customer_id` > " . (int)$offset . " AND `newsletter` = '1') LIMIT 2500");
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
            foreach ($chunk as $email) {
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
        $sync_additional = $this->load->model_setting_setting->getSetting('smaily')['smaily_customer_sync']['fields'];
        $sync_additional[] = 'email';
        return $sync_additional;
    }
}
