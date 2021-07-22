<?php

require_once DIR_SYSTEM . 'library/smailyforopencart/request.php';

class ControllerExtensionSmailyForOpencartCronCart extends Controller {
	public function index() {
		$this->load->model('extension/smailyforopencart/config');
		$config_model = $this->model_extension_smailyforopencart_config->initialize();

		// Ensure user has access to run the CRON job.
		if (
			!isset($this->request->get['token']) ||
			$config_model->get('abandoned_cart_token') !== $this->request->get['token']
		) {
			echo "Unauthorized";
			die(1);
		}

		// Ensure Abandoned Cart feature is enabled.
		if ($config_model->get('abandoned_cart_enabled') === false) {
			echo "Abandoned Cart disabled!";
			die(1);
		}

		$benchmark_start = microtime(true);
		$metric_sent_abandoned_carts = 0;

		$delay = $config_model->get('abandoned_cart_delay');
		$fields = $config_model->get('abandoned_cart_fields');
		$product_fields = array(
			'base_price',
			'description',
			'name',
			'price',
			'quantity',
			'sku',
		);
		$started_at = $config_model->get('abandoned_cart_started_at');

		// Initialize Smaily API client.
		$http_client = (new \SmailyForOpenCart\Request)
			->setSubdomain($config_model->get('api_subdomain'))
			->setCredentials($config_model->get('api_username'), $config_model->get('api_password'));

		// Initialize helper model.
		$this->load->model('extension/smailyforopencart/helper');
		$helper_model = $this->model_extension_smailyforopencart_helper;

		// Fetch Abandoned Carts.
		$pending_abandoned_carts = $helper_model->listPendingAbandonedCarts($delay, $started_at);
		foreach ($pending_abandoned_carts as $abandoned_cart) {
			$payload = array(
				'email' => $abandoned_cart['email'],
				'over_10_products' => 'false',
			);

			// Add customer fields.
			if (in_array('first_name', $fields)) {
				$payload['first_name'] = isset($abandoned_cart['firstname']) ? $abandoned_cart['firstname'] : '';
			}
			if (in_array('last_name', $fields)) {
				$payload['last_name'] = isset($abandoned_cart['lastname']) ? $abandoned_cart['lastname'] : '';
			}

			// Populate product empty values.
			foreach ($product_fields as $field) {
				for ($i = 1; $i < 11; $i++) {
					$payload['product_' . $field . '_' . $i] = '';
				}
			}

			// Populate address fields with up to 10 products.
			$j = 1;
			foreach ($abandoned_cart['products'] as $product) {
				if ($j > 10) {
					$payload['over_10_products'] = 'true';
					break;
				}

				if (in_array('name', $fields, true)) {
					$payload['product_name_' . $j] = trim($product['name']);
				}
				if (in_array('description', $fields, true)) {
					$payload['product_description_' . $j] = htmlspecialchars(trim($product['description']));
				}
				if (in_array('sku', $fields, true)) {
					$payload['product_sku_' . $j] = $product['sku'];
				}
				if (in_array('quantity', $fields, true)) {
					$payload['product_quantity_' . $j] = $product['quantity'];
				}
				if (in_array('price', $fields, true)) {
					$price = !empty($product['special']) ? $product['special'] : $product['price'];
					$payload['product_price_' . $j] = $this->getProductDisplayPrice($price, $product['tax_class_id']);
				}
				if (in_array('base_price', $fields, true)) {
					$payload['product_base_price_' . $j] = $this->getProductDisplayPrice(
						$product['price'],
						$product['tax_class_id']
					);
				}

				$j++;
			}

			try {
				$http_client->post('autoresponder', array(
					'autoresponder' => $config_model->get('abandoned_cart_autoresponder'),
					'addresses' => [$payload],
				));

				$metric_sent_abandoned_carts += 1;
			} catch (SmailyForOpenCart\HTTPError $error) {
				$this->log->write($error);
				die($error);
			} catch (SmailyForOpenCart\APIError $error) {
				$this->log->write($error);

				// Ignore invalid email address errors, because we do not want to repeatedly retry sending.
				if ($error->getCode() !== SmailyForOpenCart\Request::API_ERR_INVALID_DATA) {
					die($error);
				}
			}

			// Mark Abandoned Cart as sent.
			$helper_model->markAbandonedCartSent($abandoned_cart['customer_id']);
		}

		printf(
			"Finished in %f seconds. Sent %d abandoned carts out of %d.",
			microtime(true) - $benchmark_start,
			$metric_sent_abandoned_carts,
			count($pending_abandoned_carts)
		);
	}

	/**
	 * Calculates and returns product display price with tax and default currency code.
	 *
	 * @param string $price         Product price.
	 * @param string $tax_class_id  Product tax class number.
	 * @return void
	 */
	protected function getProductDisplayPrice($price, $tax_class_id) {
		$price_with_tax = $this->tax->calculate($price, $tax_class_id, $this->config->get('config_tax'));
		return $this->currency->format($price_with_tax, $this->config->get('config_currency'));
	}
}
