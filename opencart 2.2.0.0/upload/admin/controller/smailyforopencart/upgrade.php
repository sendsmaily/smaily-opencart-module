<?php

class ControllerSmailyForOpencartUpgrade extends Controller {

    /**
     * Upgrade script for version upgrades.
     * Event handler for admin/model/extension/modification/addModification/after
     *
     * @return void
     */
    public function upgrade($route, $args, $output) {
        // Check if our plugin is being updated.
        if (! array_key_exists('code', $output) ||
            $output['code'] !== 'smaily_for_opencart_extension'
        ) {
            return;
        }

        // Load models.
        $this->load->model('extension/modification');
        $this->load->model('setting/setting');
        // Module modification settings.
        $smaily_module = $this->model_extension_modification->getModificationByCode('smaily_for_opencart_extension');

        // Stop if module hasn't been installed with adding it as a modification.
        if (empty($smaily_module)) {
            return;
        }

        $version = $smaily_module['version'];

        // Version 1.2.0 - standardize abandoned cart fields.
        if (version_compare($version, '1.2.0', '=')) {
            $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');

            $cart_additional = [];
            if (array_key_exists('smaily_for_opencart_abandoned_additional', $settings)) {
                $cart_additional = $settings['smaily_for_opencart_abandoned_additional'];
            }

            // Add fist_name and last_name if abandoned cart was active. Previous default values.
            $enabled = $this->config->get('smaily_for_opencart_enable_abandoned');
            if ((int) $enabled === 1) {
                // If there was no additional fields before add defaults.
                if (empty($cart_additional)) {
                    $settings['smaily_for_opencart_abandoned_additional'] = [
                        'first_name',
                        'last_name'
                    ];
                    $this->model_setting_setting->editSetting('smaily_for_opencart', $settings);
                } else {
                    // Add new fields to the list.
                    if (!in_array('first_name', $cart_additional)) {
                        array_push($cart_additional, 'first_name');
                    }

                    if (!in_array('last_name', $cart_additional)) {
                        array_push($cart_additional, 'last_name');
                    }

                    $this->model_setting_setting->editSettingValue(
                        'smaily_for_opencart',
                        'smaily_for_opencart_abandoned_additional',
                        $cart_additional
                    );
                }
            }
        }
    }
}
