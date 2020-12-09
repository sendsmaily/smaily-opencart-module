<?php
include_once(DIR_SYSTEM . 'library/smailyforopencart/request.php');
class ModelExtensionSmailyForOpencartAdmin extends Model {

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
     * Remove smaily_abandoned_carts table and settings.
     *
     * @return void
     */
    public function uninstall() {
        $this->load->model('setting/setting');
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "smaily_abandoned_carts");
        // Remove plugin settings.
        $this->model_setting_setting->deleteSetting('smaily_for_opencart');
    }

    public function getAutoresponders() {
        // Smaily settings from database.
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_smaily_for_opencart');
        // Credentials
        $subdomain = $settings['module_smaily_for_opencart_subdomain'];
        $username = $settings['module_smaily_for_opencart_username'];
        $password = $settings['module_smaily_for_opencart_password'];
        try {
            $autoresponders = (new \Smaily\Request)
                ->auth($subdomain, $username, $password)
                ->setUrlViaEndpoint('workflows')
                ->setData(array('trigger_type' => 'form_submitted'))
                ->get();
        } catch (\Smaily\APIError $error) {
            $this->log->write($error->getMessage());
        }

        if (empty($autoresponders)) {
            return array();
        }

        $list = [];
        foreach ($autoresponders as $autoresponder) {
            if (!empty($autoresponder['id']) && !empty($autoresponder['title'])) {
                $list[$autoresponder['id']] = trim($autoresponder['title']);
            }
        }
        return $list;
    }

    public function validateCredentials($subdomain, $username, $password) {
        // In case subdomain was entered as http://demo.sendsmaily.net.
        $subdomain = $this->normalizeSubdomain($subdomain);
        $username = html_entity_decode($username);
        $password = html_entity_decode($password);

        // Validate credentials with a call to Smaily.
        (new \Smaily\Request)
            ->auth($subdomain, $username, $password)
            ->setUrlViaEndpoint('workflows')
            ->setData(array('trigger_type' => 'form_submitted'))
            ->get();
        return;
    }

    /**
     * Normalize subdomain into the bare necessity.
     *
     * @param string $subdomain
     *   Messy subdomain, http://demo.sendsmaily.net for example.
     *
     * @return string
     *   'demo' from demo.sendsmaily.net
     */
    private function normalizeSubdomain($subdomain) {
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

    public function saveValidatedCredentials($subdomain, $username, $password) {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('module_smaily_for_opencart');
        // Activate module.
        $settings['module_smaily_for_opencart_status'] = 1;
        // Used because save button saves whole form.
        $settings['module_smaily_for_opencart_validated'] = 1;
        $settings['module_smaily_for_opencart_subdomain'] = $this->db->escape($subdomain);
        $settings['module_smaily_for_opencart_username'] = $this->db->escape($username);
        $settings['module_smaily_for_opencart_password'] = $this->db->escape($password);
        // Save credentials to db.
        $this->model_setting_setting->editSetting('module_smaily_for_opencart', $settings);
    }
}
