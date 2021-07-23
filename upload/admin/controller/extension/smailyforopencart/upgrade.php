<?php

class ControllerExtensionSmailyForOpencartUpgrade extends Controller {
	const MODULE_ID = 'smaily_for_opencart_extension';

	/**
	 * Run module upgrade logic.
	 *
	 * Implements event handler for admin/model/setting/modification/addModification/after.
	 *
	 * @return void
	 */
	public function upgrade($route, $args) {
		$args = $args[0];

		// Ensure Smaily for OpenCart was upgraded.
		if (!isset($args['code']) || $args['code'] !== self::MODULE_ID) {
			return;
		}

		$this->load->model('setting/modification');
		$module = $this->model_setting_modification->getModificationByCode(self::MODULE_ID);

		// Ensure module is installed.
		if (empty($module)) {
			return;
		}

		if (version_compare($version, '1.3.2', '>=')) {
			$this->migrate_1_3_2();
		}
	}

	/**
	 * Run version 1.3.2 migrations:
	 *
	 * - Introduces event to remove Abandoned Cart on cart empty.
	 * - Register Abandoned Cart start time to ignore older carts.
	 *
	 * @return void
	 */
	private function migrate_1_3_2() {
		$this->load->model('setting/setting');
		$settings_model = $this->model_setting_setting;

		// Install Abandoned Cart remove event handler, if missing.
		$event = $this->db->query("SELECT * FROM " . DB_PREFIX . "event WHERE code = 'smaily_reset_empty_cart'")->row;
		if (empty($event)) {
			$this->load->model('extension/event');
			$this->model_extension_event->addEvent(
				'smaily_reset_empty_cart',
				'catalog/controller/checkout/cart/remove/after',
				'extension/smailyforopencart/order/removeWhenCartEmpty'
			);
		}

		// Register Abandoned Cart start time, if feature is enabled.
		$settings = $settings_model->getSetting('module_smaily_for_opencart');
		if (
			!array_key_exists('module_smaily_for_opencart_cart_time', $settings) &&
			(int)$settings['module_smaily_for_opencart_enable_abandoned'] === 1
		) {
			$settings['module_smaily_for_opencart_cart_time'] = date('Y-m-d H:i:s');
			$settings_model->editSetting('module_smaily_for_opencart', $settings);
		}
	}
}
