<?php

class ModelExtensionSmailyForOpencartHelper extends Model {

	/**
	 * Get all customers subscribed to newsletter.
	 *
	 * @param string $since_at
	 * @param int $since_customer_id
	 * @return array $customers
	 */
	public function listNewsletterSubscribers($since_at, $since_customer_id) {
		$db_prefix = DB_PREFIX;
		$since_customer_id = (int)$since_customer_id;

		$escaped_since_at = new DateTime($since_at);
		$escaped_since_at = $this->db->escape($escaped_since_at->format('Y-m-d H:i:s'));

		$sql = <<<EOT
		SELECT
			customer_id,
			email,
			firstname,
			lastname,
			telephone,
			date_added
		FROM ${db_prefix}customer
		WHERE
			customer_id > ${since_customer_id} AND
			newsletter = 1 AND
			date_added > "${escaped_since_at}"
		LIMIT 2500
		EOT;

		return $this->db->query($sql)->rows;
	}

	/**
	 * Change newsletter status to 0 in OpenCart.
	 *
	 * @param array $emails
	 * @return void
	 */
	public function optOutCustomers($emails) {
		$db_prefix = DB_PREFIX;

		$escaped_emails = array_map(array($this->db, 'escape'), $emails);
		$escaped_emails = implode("','", $escaped_emails);

		$sql = "UPDATE " . DB_PREFIX . "customer SET newsletter = 0 WHERE email IN ('" . $escaped_emails . "')";
		$this->db->query($sql);
	}

	/**
	 * Mark Abandoned Cart sent.
	 *
	 * @param int $cart_id
	 * @return void
	 */
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

	/**
	 * List pending Abandoned Carts.
	 *
	 * @param int $delay
	 * @param string $started_at
	 * @return array
	 */
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

	/**
	 * Fetch products of a cart.
	 *
	 * @param int $cart
	 * @return array
	 */
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
	 * Update Customer Synchronization last run time.
	 *
	 * @param string $dt
	 * @return void
	 */
	public function editCustomerSyncLastRunAt($dt) {
		$db_prefix = DB_PREFIX;
		$escaped_dt = $this->db->escape($dt);

		$sql = <<<EOT
		UPDATE ${db_prefix}setting
		SET
			`value` = "${escaped_dt}"
		WHERE
			`code` = "module_smaily_for_opencart" AND
			`key` = "module_smaily_for_opencart_sync_time" AND
			`store_id` = 0
		EOT;

		$this->db->query($sql);
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
