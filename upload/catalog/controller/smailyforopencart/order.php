<?php

class ControllerSmailyForOpencartOrder extends Controller {

    /**
     * Removes customer from smaily abandoned carts table after placed order.
     *
     * @return void
     */
    public function removeSent() {
        $customer_id = $this->customer->getId();
        // Continue only if it is a customer order.
        if (isset($customer_id)) {
            $this->db->query(
                "DELETE FROM " . DB_PREFIX . "smaily_abandoned_carts " .
                "WHERE customer_id = '" . (int) $customer_id . "'"
            );
        }
    }

    /**
     * Removes customer from abandoned carts table when clearing all items from cart.
     * Event handler for catalog/controller/checkout/cart/remove/after.
     *
     * @return void
     */
    public function removeWhenCartEmpty() {
        $customer_id = $this->customer->getId();
        if (isset($customer_id)) {
            $this->load->model('smailyforopencart/helper');
            if ($this->model_smailyforopencart_helper->isCartEmpty((int) $customer_id)) {
                $this->db->query(
                    "DELETE FROM " . DB_PREFIX . "smaily_abandoned_carts " .
                    "WHERE customer_id ='" . $this->db->escape((int) $customer_id) . "'"
                );
            }
        }
    }
}
