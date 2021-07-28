<?php

class ModelExtensionSmailyForOpencartConfig extends Model {
	const NAMESPACE = 'module_smaily_for_opencart';

	protected $settings = null;

	public function __construct($registry) {
		parent::__construct($registry);

		// Ensure settings model is loaded.
		$this->load->model('setting/setting');
	}

	public function initialize() {
		if ($this->settings === null) {
			$stored = $this->model_setting_setting->getSetting(self::NAMESPACE);

			$settings = array();
			foreach ($stored as $from => $value) {
				$to = ltrim(str_replace(self::NAMESPACE, '', $from), '_');
				$settings[$to] = $value;
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

	public function set($setting, $value) {
		$this->settings[$setting] = $value;
		return $this;
	}

	public function update($input) {
		foreach ($input as $field => $value) {
			$this->settings[$field] = $value;
		}

		return $this;
	}

	public function save() {
		$settings = array();

		// Prepare settings for database storage.
		$prepared = $this->prepare($this->settings);
		foreach ($prepared as $field => $value) {
			$settings[self::NAMESPACE . '_' . $field] = $value;
		}

		$this->model_setting_setting->editSetting(self::NAMESPACE, $settings);
	}

	/**
	 * Normalize settings for usage.
	 *
	 * @param array $input
	 * @return array.
	 */
	protected function normalize($input) {
		// Normalize module settings.
		$input['status'] = (bool)(int)$input['status'];

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

	/**
	 * Prepare settings for database.
	 *
	 * @param array $input
	 * @return array
	 */
	protected function prepare($input) {
		// Normalize module settings.
		$input['status'] = (int)$input['status'];

		// Normalize API credentials.
		$input['validated'] = (int)$input['validated'];

		// Normalize customer synchronization settings.
		$input['customer_sync_enabled'] = (int)$input['customer_sync_enabled'];

		// Normalize abandoned cart settings.
		$input['abandoned_cart_enabled'] = (int)$input['abandoned_cart_enabled'];

		return $input;
	}
}
