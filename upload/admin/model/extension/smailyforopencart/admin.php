<?php
require_once(DIR_SYSTEM . 'library/smailyforopencart/request.php');
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

    /**
     * Normalize subdomain into the bare necessity.
     *
     * @param string $subdomain
     *   Messy subdomain, http://demo.sendsmaily.net for example.
     *
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
