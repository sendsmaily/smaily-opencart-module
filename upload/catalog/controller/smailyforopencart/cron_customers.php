<?php

/**
 * Samaly subscribers synchronization.
 */
class ControllerSmailyForOpencartCronCustomers extends Controller {

    public function index() {
        // Customer model.
        $this->load->model('account/customer');
        // Load Smaily settings.
        $this->load->model('setting/setting');
        // Load Smaily helper.
        $this->load->model('smailyforopencart/helper');

        $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');

        // Validate cron token.
        if (!array_key_exists('smaily_for_opencart_sync_token', $settings) ||
            empty($this->request->get['token']) ||
            $settings['smaily_for_opencart_sync_token'] !== $this->request->get['token']
        ) {
            die('Unauthorized');
        }

        if (array_key_exists('smaily_for_opencart_enable_subscribe', $settings) &&
            (int) $settings['smaily_for_opencart_enable_subscribe'] === 1) {
            $offset_unsub = 0;
            while (true) {
                $unsubscribers = $this->model_smailyforopencart_helper->apiCall('contact', [
                    'list' => 2,
                    'offset' => $offset_unsub,
                    'limit' => 2500,
                ]);
                // Exit while loop if api returns no unsubscribers.
                if (empty($unsubscribers)) {
                    break;
                }
                // Collect unsubscriber emails.
                $unsubscribers_emails = [];
                foreach ($unsubscribers as $unsubscriber) {
                    array_push($unsubscribers_emails, $unsubscriber['email']);
                }
                // unsubscribeCustomers method would compile a single update query.
                $this->model_smailyforopencart_helper->unsubscribeCustomers($unsubscribers_emails);
                $offset_unsub += 1;
            }

            $response = 'No customers to sync in OpenCart database';
            $offset_sub = 0;
            $last_sync = $this->model_smailyforopencart_helper->getSyncTime();
            $sync_time = date('c');
            while (true) {
                $subscribers = $this->model_smailyforopencart_helper->getSubscribedCustomers($offset_sub, $last_sync);
                if (empty($subscribers)) {
                    break;
                }
                $list = [];
                foreach ($subscribers as $subscriber) {
                    // Get customer info based of selected fields from admin.
                    $sync_fields = $this->model_smailyforopencart_helper->getSyncFields();
                    $customer = [];
                    foreach ($sync_fields as $field) {
                        $customer[$field] = $subscriber[$field];
                    }
                    $offset_sub = $subscriber['customer_id'];
                    $customer['is_unsubscribed'] = "0";
                    array_push($list, $customer);
                }
                // Send subscribers to smaily.
                $response = $this->model_smailyforopencart_helper->apiCall('contact', $list, 'POST');
                // Error handling for apiCall POST.
                if (isset($response['code']) && $response['code'] != "101") {
                    die('Error with request to Smaily API, try again later.');
                }
            }
            $this->model_smailyforopencart_helper->editSettingValue(
                'smaily_for_opencart',
                'smaily_for_opencart_sync_time',
                $sync_time
            );

            $this->log->write('smaily subscriber sync finished: ' . json_encode($response));
            echo 'Smaily subscriber sync finished.';
        }
    }
}
