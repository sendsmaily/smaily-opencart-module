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
}
