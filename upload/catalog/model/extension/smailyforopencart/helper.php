<?php

class ModelExtensionSmailyForOpencartHelper extends Model {

	/**
	 * Get all subscribed customers.
	 *
	 * @param int $offset Id counter
	 * @return array $customers All subscribed customers in array.
	 */
	public function getSubscribedCustomers($offset, $sync_time) {
		$query = $this->db->query(
			"SELECT * FROM " . DB_PREFIX . "customer
			WHERE (`customer_id` > " . (int)$offset . " AND `newsletter` = '1'
			AND `date_added` > " . "'" . $this->db->escape($sync_time) . "')" .
			" LIMIT 2500"
		);
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
		$sync_additional = $this->config->get('module_smaily_for_opencart_syncronize_additional');
		$sync_additional[] = 'email';
		return $sync_additional;
	}

	public function markAbandonedCartSent($cart_id) {
		$cart_id = (int)$cart_id;
		$db_prefix = DB_PREFIX;

		$sql = <<<EOT
			INSERT INTO ${db_prefix}smaily_abandoned_carts
			SET
				customer_id = ${cart_id},
				sent_time = NOW()
		EOT;

		$this->db->query($sql);
	}

	public function listPendingAbandonedCarts($delay, $started_at) {
		$db_prefix = DB_PREFIX;
		$escaped_started_at = $this->db->escape($started_at);

		$sql = <<<EOT
		SELECT
			cart.customer_id,
			customer.email,
			customer.firstname,
			customer.lastname,
			MAX(cart.date_added) AS last_date_added
		FROM ${db_prefix}cart AS cart
		LEFT JOIN ${db_prefix}customer AS customer ON cart.customer_id = customer.customer_id
		LEFT JOIN ${db_prefix}smaily_abandoned_carts AS smaily ON cart.customer_id = smaily.customer_id
		WHERE
			smaily.customer_id IS NULL AND
			cart.customer_id > 0
		GROUP BY cart.customer_id
		HAVING
			last_date_added <= DATE_SUB(NOW(), INTERVAL ${delay} MINUTE) AND
			last_date_added >= "${escaped_started_at}"
		EOT;

		$abandoned_carts = array();
		foreach ($this->db->query($sql)->rows as $abandoned_cart) {
			$customer_id = (int)$abandoned_cart['customer_id'];

			$abandoned_carts[] = array(
				'customer_id' => $customer_id,
				'email' => $abandoned_cart['email'],
				'firstname' => $abandoned_cart['firstname'],
				'lastname' => $abandoned_cart['lastname'],
				'products' => $this->fetchCartProducts($customer_id),
			);
		}

		return $abandoned_carts;
	}

	protected function fetchCartProducts($cart_id) {
		$customer_group_id = (int)$this->config->get('config_customer_group_id');
		$db_prefix = DB_PREFIX;
		$language_id = (int)$this->config->get('config_language_id');

		$sql = <<<EOT
		SELECT
			c.product_id,
			c.quantity,
			p.price,
			(
				SELECT price
				FROM ${db_prefix}product_special ps
				WHERE
					ps.product_id = c.product_id AND
					ps.customer_group_id = ${customer_group_id} AND
					(
						(ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND
						(ps.date_end = '0000-00-00' OR ps.date_end > NOW())
					)
				ORDER BY
					ps.priority ASC,
					ps.price ASC
				LIMIT 1
			) AS special,
			p.sku,
			p.tax_class_id,
			pd.name,
			pd.description
		FROM ${db_prefix}cart AS c
		LEFT JOIN ${db_prefix}product AS p ON c.product_id = p.product_id
		LEFT JOIN ${db_prefix}product_description AS pd ON c.product_id = pd.product_id AND pd.language_id = ${language_id}
		WHERE customer_id = ${cart_id}
		EOT;

		return $this->db->query($sql)->rows;
	}

	/**
	 * Get UTC sync time from settings.
	 *
	 * @return string $sync_time Time of last sync
	 */
	public function getSyncTime() {
		$this->load->model('setting/setting');
		$sync_time = $this->model_setting_setting->getSettingValue('module_smaily_for_opencart_sync_time');
		if (!isset($sync_time)) {
			return date('c', 0); #Failsafe for first sync.
		}

		return $sync_time;
	}

	public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
		if (!is_array($value)) {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "', serialized = '0'  WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(json_encode($value)) . "', serialized = '1' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		}
	}

	/**
	 * Checks if customer cart is empty.
	 *
	 * @param int $customer_id
	 * @return boolean
	 */
	public function isCartEmpty($customer_id) {
		$query = $this->db->query(
			"SELECT COUNT(*) AS cart_items FROM " . DB_PREFIX . "cart " .
			"WHERE customer_id='" . $this->db->escape($customer_id) ."'"
		);

		$data = $query->row;
		if ((int)$data['cart_items'] != 0) {
			return false;
		};

		return true;
	}

}
