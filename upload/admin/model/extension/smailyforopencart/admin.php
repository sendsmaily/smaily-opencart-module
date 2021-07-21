<?php

class ModelExtensionSmailyForOpencartAdmin extends Model {
	public function listAbandonedCarts($sort_by, $sort_order, $offset, $limit, $delay, $started_at) {
		$db_prefix = DB_PREFIX;
		$escaped_started_at = $this->db->escape($started_at);

		$sql = <<<EOT
		SELECT
			cart.customer_id,
			customer.email,
			customer.firstname,
			customer.telephone,
			customer.lastname,
			MAX(cart.date_added) AS last_date_added,
			smaily.sent_time,
			IF(smaily.customer_id IS NOT NULL, 1, 0) AS is_sent
		FROM ${db_prefix}cart AS cart
		LEFT JOIN ${db_prefix}customer AS customer ON cart.customer_id = customer.customer_id
		LEFT JOIN ${db_prefix}smaily_abandoned_carts AS smaily ON cart.customer_id = smaily.customer_id
		WHERE
			cart.customer_id > 0
		GROUP BY cart.customer_id
		HAVING
			last_date_added <= DATE_SUB(NOW(), INTERVAL ${delay} MINUTE) AND
			last_date_added >= "${escaped_started_at}"
		ORDER BY ${sort_by} ${sort_order}
		LIMIT ${offset}, ${limit}
		EOT;

		$abandoned_carts = array();
		foreach ($this->db->query($sql)->rows as $abandoned_cart) {
			$customer_id = (int)$abandoned_cart['customer_id'];

			$abandoned_carts[] = array(
				'customer_id' => $customer_id,
				'email' => $abandoned_cart['email'],
				'firstname' => $abandoned_cart['firstname'],
				'lastname' => $abandoned_cart['lastname'],
				'is_sent' => $abandoned_cart['is_sent'],
				'sent_time' => $abandoned_cart['sent_time'],
				'products' => $this->fetchCartProducts($customer_id),
			);
		}

		return $abandoned_carts;
	}

	public function countAbandonedCarts($delay, $started_at) {
		$db_prefix = DB_PREFIX;
		$escaped_started_at = $this->db->escape($started_at);

		$sql = <<<EOT
		SELECT
			MAX(cart.date_added) AS last_date_added
		FROM ${db_prefix}cart AS cart
		LEFT JOIN ${db_prefix}customer AS customer ON cart.customer_id = customer.customer_id
		LEFT JOIN ${db_prefix}smaily_abandoned_carts AS smaily ON cart.customer_id = smaily.customer_id
		WHERE
			cart.customer_id > 0
		GROUP BY cart.customer_id
		HAVING
			last_date_added <= DATE_SUB(NOW(), INTERVAL ${delay} MINUTE) AND
			last_date_added >= "${escaped_started_at}"
		EOT;

		return $this->db->query($sql)->num_rows;
	}

	protected function fetchCartProducts($cart_id) {
		$db_prefix = DB_PREFIX;
		$language_id = (int)$this->config->get('config_language_id');

		$sql = <<<EOT
		SELECT
			c.product_id,
			c.quantity,
			p.price,
			pd.name
		FROM ${db_prefix}cart AS c
		LEFT JOIN ${db_prefix}product AS p ON c.product_id = p.product_id
		LEFT JOIN ${db_prefix}product_description AS pd ON c.product_id = pd.product_id AND pd.language_id = ${language_id}
		WHERE customer_id = ${cart_id}
		EOT;

		return $this->db->query($sql)->rows;
	}
}
