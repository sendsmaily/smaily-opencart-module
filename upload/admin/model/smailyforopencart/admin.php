<?php

class ModelSmailyForOpencartAdmin extends Model {
    /**
     * getSettingValue was only added in 2.2+
     * This is a workaround to editing system files.
     * The function returns the "value" parameter of a given setting ($key).
     */
    public function getSettingValue($group, $key, $store_id = 0) {
        $query = $this->db->query("SELECT serialized, value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "'");
        if ($query->num_rows) {
            return !$query->row['serialized'] ? $query->row['value'] : unserialize($query->row['value']);
        } else {
            return null;
        }
    }

    public function editSettingValue($group = '', $key = '', $value = '', $store_id = 0) {
        // Use UPDATE if key exist, otherwise INSERT it into db.
        if ($this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key`='" . $this->db->escape($key) . "'")->num_rows == 0) {
            if (!is_array($value)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
            }
        } else {
            if (!is_array($value)) {
                $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
            } else {
                $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(serialize($value)) . "' WHERE `group` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "' AND serialized = '1'");
            }
        }
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
        $settings = [
            'subdomain' => $this->db->escape($subdomain),
            'password' => $this->db->escape($password),
            'username' => $this->db->escape($username),
        ];
        $this->editSettingValue('smaily', 'smaily_api_credentials', $settings);

    }

}
