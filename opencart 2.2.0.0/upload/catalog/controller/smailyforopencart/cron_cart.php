<?php

/**
 * Smaily abandoned carts.
 */
class ControllerSmailyForOpencartCronCart extends Controller {

    public function index() {
        // Customer model.
        $this->load->model('account/customer');
        // Load Smaily settings.
        $this->load->model('setting/setting');
        // Load Smaily helper.
        $this->load->model('smailyforopencart/helper');

        $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');
        // Check if settings have been saved.
        if (!array_key_exists('smaily_for_opencart_cart_token', $settings)) {
            echo('Invalid abandoned cart settings!');
            die(1);
        }
        // Validate cron token.
        if (empty($this->request->get['token']) ||
            $settings['smaily_for_opencart_cart_token'] !== $this->request->get['token']
        ) {
            echo('Unauthorized!');
            die(1);
        }
        // Check if abandoned cart is enabled.
        if (! array_key_exists('smaily_for_opencart_enable_abandoned', $settings) ||
            (int) $settings['smaily_for_opencart_enable_abandoned'] !== 1) {
                echo('Abandoned cart disabled!');
                die(1);
        }

        // Get abandoned carts.
        $abandoned_carts = $this->model_smailyforopencart_helper->getAbandonedCarts();
        if (empty($abandoned_carts)) {
            echo('No abandoned carts');
            die();
        }

        foreach ($abandoned_carts as $cart) {
            // Addresses array for smaily api call.
            $addresses = array(
                'email' => $cart['email'],
                'first_name' => isset($cart['firstname']) ? $cart['firstname'] : '',
                'last_name' => isset($cart['lastname']) ? $cart['lastname'] : '',
            );
            // Sync values selected from admin panel.
            $cart_sync_values = $this->model_smailyforopencart_helper->getAbandonedSyncFields();
            // Populate products list with empty values for legacy api.
            foreach ($cart_sync_values as $sync_value) {
                for ($i=1; $i < 11; $i++) {
                    $addresses['product_' . $sync_value . '_' . $i] = '';
                }
            }

            // Populate addresses fields with up to 10 products.
            $j = 1;
            foreach ($cart['products'] as $product) {
                if ($j > 10) {
                    $addresses['over_10_products'] = 'true';
                    break;
                }
                foreach ($cart_sync_values as $sync_value) {
                    if ($sync_value === 'quantity') {
                        $addresses['product_' . $sync_value . '_' . $j] = $product[$sync_value];
                    } else {
                        $addresses['product_' . $sync_value . '_' . $j] = $product['data'][$sync_value];
                    }
                }
                $j++;
            }

            // Get autoresponder from settings.
            $autoresponder = html_entity_decode($settings['smaily_for_opencart_abandoned_autoresponder']);
            $autoresponder = json_decode($autoresponder, true);
            $autoresponder_id = $autoresponder['id'];

            // Api call query.
            $query = array(
                'autoresponder' => $autoresponder_id,
                'addresses' => [$addresses],
            );
            // Make api call.
            $response = $this->model_smailyforopencart_helper->apiCall('autoresponder', $query, 'POST');
            // If successful add customer to smaily_abandoned_carts table
            if (array_key_exists('code', $response) && (int) $response['code'] === 101) {
                $this->model_smailyforopencart_helper->addSentCart($cart['customer_id']);
            }
        }
        echo 'Abandoned carts sent!';
    }
}
