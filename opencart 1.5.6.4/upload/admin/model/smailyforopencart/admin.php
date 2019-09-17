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
}
