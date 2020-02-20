<?php

/**
 * Smaily abandoned carts.
 */
class ControllerExtensionSmailyForOpencartCronCart extends Controller {

    public function index() {
        // Customer model.
        $this->load->model('account/customer');
        // Load Smaily settings.
        $this->load->model('setting/setting');
        // Load Smaily helper.
        $this->load->model('extension/smailyforopencart/helper');

        $settings = $this->model_setting_setting->getSetting('module_smaily_for_opencart');
        // Validate cron token.
        if (! array_key_exists('module_smaily_for_opencart_cart_token', $settings)) {
            echo('Invalid abandoned cart settings!');
            die(1);
        }
        if (empty($this->request->get['token']) ||
            $settings['module_smaily_for_opencart_cart_token'] !== $this->request->get['token']
        ) {
            echo('Unauthorized');
            die(1);
        }

        if (! array_key_exists('module_smaily_for_opencart_enable_abandoned', $settings) ||
            (int) $settings['module_smaily_for_opencart_enable_abandoned'] !== 1
        ) {
            echo('Abandoned cart disabled!');
            die(1);
        }

        // Get abandoned carts.
        $abandoned_carts = $this->model_extension_smailyforopencart_helper->getAbandonedCarts();
        if (!empty($abandoned_carts)) {
            echo('No abandoned carts');
            die();
        }

        // Sync values selected from admin panel.
        $cart_sync_values = $this->model_extension_smailyforopencart_helper->getAbandonedSyncFields();

        foreach ($abandoned_carts as $cart) {
            // Address array for smaily api call.
            $address = array(
                'email' => $cart['email'],
                'firstname' => isset($cart['firstname']) ? $cart['firstname'] : '',
                'lastname' => isset($cart['lastname']) ? $cart['lastname'] : '',
            );

            // Populate products list with empty values for legacy api.
            foreach ($cart_sync_values as $sync_value) {
                for ($i=1; $i < 11; $i++) {
                    $address['product_' . $sync_value . '_' . $i] = '';
                }
            }

            // Populate address fields with up to 10 products.
            $j = 1;
            foreach ($cart['products'] as $product) {
                if ($j <= 10) {
                    foreach ($cart_sync_values as $sync_value) {
                        if ($sync_value === 'quantity') {
                            $address['product_' . $sync_value . '_' . $j] = $product[$sync_value];
                        } else {
                            $address['product_' . $sync_value . '_' . $j] = $product['data'][$sync_value];
                        }
                    }
                    $j++;
                }
            }

            // Get autoresponder from settings.
            $autoresponder = html_entity_decode($settings['module_smaily_for_opencart_abandoned_autoresponder']);
            $autoresponder = json_decode($autoresponder, true);
            $autoresponder_id = $autoresponder['id'];

            // Api call query.
            $query = array(
                'autoresponder' => $autoresponder_id,
                'addresses' => [$address],
            );
            // Make api call.
            $response = $this->model_extension_smailyforopencart_helper->apiCall('autoresponder', $query, 'POST');
            // If successful add customer to smaily_abandoned_carts table
            if (array_key_exists('code', $response) && (int) $response['code'] === 101) {
                $this->model_extension_smailyforopencart_helper->addSentCart($cart['customer_id']);
            }
        }
        echo 'Abandoned carts sent!';
    }
}
