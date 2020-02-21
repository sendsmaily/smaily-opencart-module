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
        // Get sync values selected from admin panel.
        $cart_sync_values = $this->model_smailyforopencart_helper->getAbandonedSyncFields();
        $fields_available = [
            'name',
            'description',
            'sku',
            'quantity',
            'price',
            'base_price'
        ];
        $selected_fields = array_intersect($fields_available, $cart_sync_values);

        foreach ($abandoned_carts as $cart) {
            // Address array for smaily api call.
            $address = array(
                'email' => $cart['email'],
            );

            // Add customer fields.
            if (in_array('first_name', $cart_sync_values)) {
                $address['first_name'] = isset($cart['firstname']) ? $cart['firstname'] : '';
            }
            if (in_array('last_name', $cart_sync_values)) {
                $address['last_name'] = isset($cart['lastname']) ? $cart['lastname'] : '';
            }

            // Populate products list with empty values for legacy api.
            foreach ($selected_fields as $field) {
                for ($i=1; $i < 11; $i++) {
                    $address['product_' . $field . '_' . $i] = '';
                }
            }

            // Populate address fields with up to 10 products.
            $j = 1;
            foreach ($cart['products'] as $product) {
                if ($j > 10) {
                    $address['over_10_products'] = 'true';
                    break;
                }
                foreach ($selected_fields as $sync_value) {
                    switch ($sync_value) {
                        case 'description':
                            $address['product_description_' . $j] = htmlspecialchars(
                                $product['data'][$sync_value]
                            );
                            break;
                        case 'quantity':
                            $address['product_quantity_' . $j] = $product[$sync_value];
                            break;
                        case 'price':
                            // Use special price if available.
                            if (isset($product['data']['special'])) {
                                $price = $product['data']['special'];
                            } else {
                                $price = $product['data']['price'];
                            }
                            $address['product_price_' . $j] = $this->getProductDisplayPrice(
                                $price,
                                $product['data']['tax_class_id']
                            );
                            break;
                        case 'base_price':
                            $address['product_base_price_' . $j] = $this->getProductDisplayPrice(
                                $product['data']['price'],
                                $product['data']['tax_class_id']
                            );
                            break;
                        default:
                            $address['product_' . $sync_value . '_' . $j] = $product['data'][$sync_value];
                            break;
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
                'addresses' => [$address],
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

    /**
     * Calculates and returns product display price with tax and default currency code.
     *
     * @param string $price         Product price.
     * @param string $tax_class_id  Product tax class number.
     * @return void
     */
    public function getProductDisplayPrice($price, $tax_class_id) {
        $price_with_tax = $this->tax->calculate(
            $price,
            $tax_class_id,
            $this->config->get('config_tax')
        );
        
        return $this->currency->format(
            $price_with_tax,
            $this->config->get('config_currency')
        );
    }
}
