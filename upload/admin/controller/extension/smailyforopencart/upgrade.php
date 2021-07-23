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
		$db_prefix = DB_PREFIX;

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

		$this->load->model('extension/smailyforopencart/config');
		$config_model = $this->model_extension_smailyforopencart_config->initialize();

		$module_version = $module['version'];
		$db_version = $config_model->get('db_version');

		// No need to run migrations if module and database version matches.
		if ($module_version === $db_version) {
			return;
		}

		$migrations = array(
			// Example: '1.0.0' => array($this, 'migrate_1_0_0'),
		);

		foreach ($migrations as $migration_version => $migration_callback) {
			if (version_compare($db_version, $migration_version, '>=')) {
				continue;
			}

			if (is_callable($migration_callback)) {
				$migration_callback();
			}
		}

		$config_model
			->set('db_version', $module_version)
			->save();
	}
}
