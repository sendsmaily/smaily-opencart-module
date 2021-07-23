<?php

class ModelExtensionSmailyForOpencartConfig extends Model {
	const NAMESPACE = 'module_smaily_for_opencart';

	protected $defaults = array(
		'abandoned_cart_autoresponder' => 0,
		'abandoned_cart_delay' => 60,
		'abandoned_cart_enabled' => false,
		'abandoned_cart_fields' => array(),
		'abandoned_cart_enabled_at' => '',
		'abandoned_cart_token' => '',
		'api_password' => '',
		'api_subdomain' => '',
		'api_username' => '',
		'customer_sync_enabled' => false,
		'customer_sync_fields' => array(),
		'customer_sync_last_run_at' => '',
		'customer_sync_token' => '',
		'enabled' => false,
		'rss_category' => 0,
		'rss_limit' => 50,
		'rss_sort_by' => 'pd.name',
		'rss_sort_order' => 'ASC',
		'validated' => false,
	);

	protected $mapping = array(
		'abandoned_cart_autoresponder' => 'abandoned_autoresponder',
		'abandoned_cart_delay' => 'cart_delay',
		'abandoned_cart_enabled' => 'enable_abandoned',
		'abandoned_cart_fields' => 'abandoned_additional',
		'abandoned_cart_enabled_at' => 'cart_time',
		'abandoned_cart_token' => 'cart_token',
		'api_password' => 'password',
		'api_subdomain' => 'subdomain',
		'api_username' => 'username',
		'customer_sync_enabled' => 'enable_subscribe',
		'customer_sync_fields' => 'syncronize_additional',
		'customer_sync_last_run_at' => 'sync_time',
		'customer_sync_token' => 'sync_token',
		'enabled' => 'status',
		'rss_category' => 'rss_category',
		'rss_limit' => 'rss_limit',
		'rss_sort_by' => 'rss_sort_by',
		'rss_sort_order' => 'rss_sort_order',
		'validated' => 'validated',
	);

	protected $settings = null;

	public function initialize() {
		$this->load->model('setting/setting');

		if ($this->settings === null) {
			$stored = $this->model_setting_setting->getSetting(self::NAMESPACE);

			$settings = array();
			foreach ($this->defaults as $to => $default) {
				$from = self::NAMESPACE . '_' . $this->mapping[$to];
				$settings[$to] = isset($stored[$from]) ? $stored[$from] : $default;
			}

			$settings = $this->normalize($settings);

			$this->settings = $settings;
		}

		return $this;
	}

	public function get($setting=null) {
		if ($setting === null) {
			return $this->settings;
		}

		return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
	}

	protected function normalize($input) {
		// Note! Only settings with integer or boolean values are normalized.

		// Normalize module settings.
		$input['enabled'] = (bool)(int)$input['enabled'];

		// Normalize API credentials.
		$input['validated'] = (bool)(int)$input['validated'];

		// Normalize customer synchronization settings.
		$input['customer_sync_enabled'] = (bool)(int)$input['customer_sync_enabled'];
		$input['customer_sync_fields'] = (array)$input['customer_sync_fields'];

		// Normalize abandoned cart settings.
		$input['abandoned_cart_autoresponder'] = (int)$input['abandoned_cart_autoresponder'];
		$input['abandoned_cart_delay'] = (int)$input['abandoned_cart_delay'];
		$input['abandoned_cart_enabled'] = (bool)(int)$input['abandoned_cart_enabled'];

		// Normalize RSS settings.
		$input['rss_limit'] = (int)$input['rss_limit'];

		return $input;
	}
}
