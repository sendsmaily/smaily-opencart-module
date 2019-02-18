<?php

/**
 * Samaly subscribers synchronization.
 */
class ControllerExtensionSmailyForOpencartCronCustomers extends Controller {

    public function index(){
        // Customer model.
        $this->load->model('account/customer');
        // Load Smaily settings.
        $this->load->model('setting/setting');
        // Load Smaily helper.
        $this->load->model('extension/smailyforopencart/helper');

        $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');

        // Validate cron token.
        if (!array_key_exists('smaily_for_opencart_sync_token', $settings) ||
            empty($this->request->get['token']) ||
            $settings['smaily_for_opencart_sync_token'] !== $this->request->get['token']
        ) {
            die('Unathorized');
        }

        if (array_key_exists('smaily_for_opencart_enable_subscribe', $settings) &&
            (int) $settings['smaily_for_opencart_enable_subscribe'] === 1) {
                // Get unsubscribers from smaily.
                $unsubscribers_filter = array(
                    'list' => 2,
                );
                $unsubscribers = $this->model_extension_smailyforopencart_helper->apiCall('contact', $unsubscribers_filter);
                // Collect unsubscriber emails.
                $unsubscribers_emails = [];
                foreach ($unsubscribers as $unsubscriber) {
                    array_push($unsubscribers_emails, $unsubscriber['email']);
                }
                // Remove subscribed status from unsubscribed customers.
                $subscribers = $this->model_extension_smailyforopencart_helper->getSubscribedCustomers();
                foreach ($subscribers as $subscriber) {
                    if (in_array($subscriber['email'], $unsubscribers_emails)) {
                        $this->model_extension_smailyforopencart_helper->unsubscribeCustomer($subscriber['customer_id']);
                    }
                }

                // Get all users with subscribed status.
                $subscribers = $this->model_extension_smailyforopencart_helper->getSubscribedCustomers();
                if (empty($subscribers)){
                    $this->log->write('Smaily subscriber sync without customers.');
                    echo 'No subscribers to sync.';
                } else {
                    // Prepare list for smaily.
                    $list = [];
                    foreach ($subscribers as $subscriber) {
                        // Get customer info based of selected fields from admin.
                        $sync_fields = $this->model_extension_smailyforopencart_helper->getSyncFields();
                        $customer = [];
                        foreach ($sync_fields as $field) {
                            $customer[$field] = $subscriber[$field];
                        }
                        $customer['is_unsubscribed'] = "0";
                        array_push($list, $customer);
                    }
                    // Send subscribers to smaily.
                    $response = $this->model_extension_smailyforopencart_helper->apiCall('contact', $list, 'POST');
                    $this->log->write('Smaily customer sync : ' . json_encode($response));
                    echo 'Subscribers synchronized';
                }
            }
    }
}