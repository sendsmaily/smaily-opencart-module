<?php

class ControllerExtensionSmailyForOpencartOrder extends Controller {
	/**
	 * Removes customer from smaily abandoned carts table after placed order.
	 *
	 * Implements event handler catalog/controller/checkout/confirm/after.
	 *
	 * @return void
	 */
	public function removeSent() {
		$customer_id = (int)$this->customer->getId();

		if (empty($customer_id)) {
			return;
		}

		$this->db->query(
			"DELETE FROM " . DB_PREFIX . "smaily_abandoned_carts WHERE customer_id = " . $customer_id
		);
	}

	/**
	 * Removes customer from abandoned carts table when clearing all items from cart.
	 *
	 * Implements event handler catalog/controller/checkout/cart/remove/after.
	 *
	 * @return void
	 */
	public function removeWhenCartEmpty() {
		$customer_id = (int)$this->customer->getId();

		if (empty($customer_id)) {
			return;
		}

		$this->load->model('extension/smailyforopencart/helper');
		$helper_model = $this->model_extension_smailyforopencart_helper;

		if ($helper_model->isCartEmpty($customer_id)) {
			$this->db->query(
				"DELETE FROM " . DB_PREFIX . "smaily_abandoned_carts WHERE customer_id = " . $customer_id
			);
		}
	}
}
