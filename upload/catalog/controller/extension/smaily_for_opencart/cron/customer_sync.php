<?php

require_once DIR_SYSTEM . 'library/smaily_for_opencart/request.php';

class ControllerExtensionSmailyForOpencartCronCustomerSync extends Controller {
	public function index() {
		$this->load->model('extension/smaily_for_opencart/config');
		$config_model = $this->model_extension_smaily_for_opencart_config->initialize();

		// Ensure user has access to run the CRON job.
		if (
			!isset($this->request->get['cron_token']) ||
			$config_model->get('customer_sync_token') !== $this->request->get['cron_token']
		) {
			echo "Unauthorized";
			die(1);
		}

		// Ensure Abandoned Cart feature is enabled.
		if ($config_model->get('customer_sync_enabled') === false) {
			echo "Customer Synchronization disabled!";
			die(1);
		}

		$benchmark_start = microtime(true);
		$metric_synced_optouts = 0;
		$metric_synced_subscribers = 0;

		// Initialize Smaily API client.
		$http_client = (new \SmailyForOpenCart\Request)
			->setSubdomain($config_model->get('api_subdomain'))
			->setCredentials($config_model->get('api_username'), $config_model->get('api_password'));

		// Initialize helper model.
		$this->load->model('extension/smaily_for_opencart/helper');
		$helper_model = $this->model_extension_smaily_for_opencart_helper;

		// Synchronize opt-outs from Smaily to OpenCart.
		$offset = 0;
		while (true) {
			$optouts = array();

			try {
				$optouts = $http_client->get('contact', array(
					'list' => 2,
					'offset' => $offset,
					'limit' => 2500,
				));
			} catch (SmailyForOpenCart\HTTPError $error) {
				$this->log->write($error);
				die($error);
			} catch (SmailyForOpenCart\APIError $error) {
				$this->log->write($error);
				die($error);
			}

			// Exit loop, if no opt-outs where returned.
			if (empty($optouts)) {
				break;
			}

			$emails = array();
			foreach ($optouts as $optout) {
				$emails[] = $optout['email'];
				$metric_synced_optouts += 1;
			}

			$helper_model->optOutCustomers($emails);

			$offset++;
		}

		// Synchronize newsletter subscribers to Smaily.
		$last_run_at = $config_model->get('customer_sync_last_run_at');
		$now_at = date('c');

		$fields = $config_model->get('customer_sync_fields');

		$last_customer_id = 0;
		while (true) {
			$subscribers = $helper_model->listNewsletterSubscribers($last_run_at, $last_customer_id);

			// Exit loop, if no (more) subscribers returned.
			if (empty($subscribers)) {
				break;
			}

			$payload = array();
			foreach ($subscribers as $subscriber) {
				$dataset = array();

				foreach ($fields as $from) {
					$to = $from;

					if ($from === 'firstname') {
						$to = 'first_name';
					} elseif ($from === 'lastname') {
						$to = 'last_name';
					}

					$dataset[$to] = $subscriber[$from];
				}

				$payload[] = array_merge($dataset, array(
					'email' => $subscriber['email'],
					'is_unsubscribed' => '0',
				));

				$metric_synced_subscribers += 1;

				$last_customer_id = (int)$subscriber['customer_id'];
			}

			try {
				$http_client->post('contact', $payload);
			} catch (SmailyForOpenCart\HTTPError $error) {
				$this->log->write($error);
				die($error);
			} catch (SmailyForOpenCart\APIError $error) {
				$this->log->write($error);
				// Stop code execution and display error unless an invalid email was in query.
				// Smaily subscribes all valid emails and discards the rest.
				if ($error->getCode() !== SmailyForOpenCart\Request::API_ERR_INVALID_DATA) {
					die($error);
				}
			}
		}

		$helper_model->editCustomerSyncLastRunAt($now_at);

		printf(
			"Finished in %f seconds. Synchronized %d opt-outs and %d newsletter subscribers.",
			microtime(true) - $benchmark_start,
			$metric_synced_optouts,
			$metric_synced_subscribers
		);
	}
}
