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
     * Normalize subdomain into the bare necessity.
     *
     * @param string $subdomain
     *  Messy subdomain, http://demo.sendsmaily.net for example.
     * @return string
     *   'demo' from demo.sendsmaily.net
     */
    public function normalizeSubdomain($subdomain) {
        // First, try to parse as full URL.
        // If that fails, try to parse as subdomain.sendsmaily.net.
        // Last resort clean up subdomain and pass as is.
        if (filter_var($subdomain, FILTER_VALIDATE_URL)) {
            $url = parse_url($subdomain);
            $parts = explode('.', $url['host']);
            $subdomain = (count($parts) >= 3) ? $parts[0] : '';
        }
        elseif (preg_match('/^[^\.]+\.sendsmaily\.net$/', $subdomain)) {
            $parts = explode('.', $subdomain);
            $subdomain = $parts[0];
        }
        $subdomain = preg_replace('/[^a-zA-Z0-9]+/', '', $subdomain);
        return $subdomain;
    }

    /**
     * Save credentials provided in arguments to database and set extension to validated state.
     *
     * @param string $subdomain Smaily API subdomain.
     * @param string $username Smaily API username.
     * @param string $password Smaily API password.
     * @return void
     */
    public function saveAPICredentials($subdomain, $username, $password) {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');
        // Activate module.
        $settings['smaily_for_opencart_status'] = 1;
        // Used because save button saves whole form.
        $settings['smaily_for_opencart_validated'] = 1;
        $settings['smaily_for_opencart_subdomain'] = $this->db->escape($subdomain);
        $settings['smaily_for_opencart_username'] = $this->db->escape($username);
        $settings['smaily_for_opencart_password'] = $this->db->escape($password);
        // Save credentials to db.
        $this->model_setting_setting->editSetting('smaily_for_opencart', $settings);
    }
}
