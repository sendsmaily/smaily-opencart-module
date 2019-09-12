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
}
