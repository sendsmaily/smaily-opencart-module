<?php

/**
 * Smaily subscribers synchronization.
 */
require_once(DIR_SYSTEM . 'library/smaily_for_opencart_request.php');
class ControllerSmailyForOpencartCronCustomers extends Controller {

    public function index() {
        // Customer model.
        $this->load->model('account/customer');
        // Load Smaily settings.
        $this->load->model('setting/setting');
        // Load Smaily helper.
        $this->load->model('smailyforopencart/helper');

        // Load saved settings from DB.
        $settings = $this->model_setting_setting->getSetting('smaily');
        $api_credentials = $settings['smaily_api_credentials'];

        $sync_settings = $settings['smaily_customer_sync'];
        $sync_settings = empty($sync_settings) ? ['enabled' => 0, 'token' => ''] : $sync_settings;

        // Validate token.
        $settings_token = isset($sync_settings['token']) ? $sync_settings['token'] : '';
        $request_token = isset($this->request->get['token']) ? trim($this->request->get['token']) : '';
        if (empty($settings_token) || empty($request_token) || $settings_token !== $request_token) {
            die('Unauthorized');
        }

        if ((int)$sync_settings['enabled'] !== 1) {
            die('Enable Customer Sync to continue');
        }

        $subdomain = $api_credentials['subdomain'];
        $username = $api_credentials['username'];
        $password = $api_credentials['password'];

        $offset_unsub = 0;
        $unsubscribers = array();
        while (true) {
            $query = array(
                'list' => 2,
                'offset' => $offset_unsub,
                'limit' => 2500,
            );

            try {
                $unsubscribers = (new \SmailyForOpenCart\Request)
                    ->setSubdomain($subdomain)
                    ->setCredentials($username, $password)
                    ->get('contact', $query);
            } catch (SmailyForOpenCart\HTTPError $error) {
                $this->log->write($error);
                echo($error);
                die(1);
            } catch (SmailyForOpenCart\APIError $error) {
                $this->log->write($error);
                echo($error);
                die(1);
            }

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
            try {
                $response = (new \SmailyForOpenCart\Request)
                    ->setSubdomain($subdomain)
                    ->setCredentials($username, $password)
                    ->post('contact', $list);
            } catch (SmailyForOpenCart\HTTPError $error) {
                $this->log->write($error);
                echo($error);
                die(1);
            } catch (SmailyForOpenCart\APIError $error) {
                $this->log->write($error);
                // Stop code execution and display error unless an invalid email was in query.
                // Smaily subscribes all valid emails and discards the rest.
                if ($error->getCode() !== SmailyForOpenCart\Request::API_ERR_INVALID_DATA) {
                    echo($error);
                    die(1);
                }
            }
        }
        $this->log->write('smaily subscriber sync finished: ' . json_encode($response));
        echo 'Smaily subscriber sync finished.';
    }
}
