<?php

/**
 * Smaily subscribers synchronization.
 */
class ControllerSmailyForOpencartCronCustomers extends Controller {

    public function index() {
        // Customer model.
        $this->load->model('account/customer');
        // Load Smaily settings.
        $this->load->model('setting/setting');
        // Load Smaily helper.
        $this->load->model('smailyforopencart/helper');

        // Validate token.

        $settings = $this->model_setting_setting->getSetting('smaily')['smaily_customer_sync'];
        $settings = empty($settings) ? ['enabled' => 0, 'token' => ''] : $settings;

        $settings_token = isset($settings['token']) ? $settings['token'] : '';
        $request_token = isset($this->request->get['token']) ? trim($this->request->get['token']) : '';
        if (empty($settings_token) || empty($request_token) || $settings_token !== $request_token) {
            die('Unauthorized');
        }
        if ($settings['enabled'] == 1) {
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
            
            $offset_sub = 0;
            while (true) {
                $subscribers = $this->model_smailyforopencart_helper->getSubscribedCustomers($offset_sub);
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
                if (isset($response['code']) && $reponse['code']) {
                    die('Error with request to Smaily API, try again later.');
                }
            }
            $this->log->write('smaily subscriber sync finished: ' . json_encode($response));
            echo 'Smaily subscriber sync finished.';
        }
    }
}
