<?php
/**
 * Samaly subscribers synchronization.
 */
class ControllerExtensionSmailyForOpencartCronCustomers extends Controller {

    public function index() {
        // Customer model.
        $this->load->model('account/customer');
        // Load Smaily settings.
        $this->load->model('setting/setting');
        // Load Smaily helper.
        $this->load->model('extension/smailyforopencart/helper');

        $settings = $this->model_setting_setting->getSetting('module_smaily_for_opencart');

        // Validate cron token.
        if (!array_key_exists('module_smaily_for_opencart_sync_token', $settings) ||
            empty($this->request->get['token']) ||
            $settings['module_smaily_for_opencart_sync_token'] !== $this->request->get['token']
        ) {
            die('Unauthorized');
        }

        if (array_key_exists('module_smaily_for_opencart_enable_subscribe', $settings) &&
            (int)$settings['module_smaily_for_opencart_enable_subscribe'] !== 1
        ) {
            die('Enable Customer Sync to continue');
        }
        $offset_unsub = 0;
        $unsubscribers = array();
        // Fetch credentials from DB.
        $subdomain = $settings['module_smaily_for_opencart_subdomain'];
        $username = $settings['module_smaily_for_opencart_username'];
        $password = $settings['module_smaily_for_opencart_password'];
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
                $msg = $error->getMessage();
                $this->log->write($msg);
                echo($msg);
                die(1);
            } catch (SmailyForOpenCart\APIError $error) {
                $msg = $error->getMessage();
                $this->log->write($msg);
                echo($msg);
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

            $offset_unsub += 1;
        }

        $response = 'No customers to sync in OpenCart database';
        $offset_sub = 0;
        $last_sync = $this->model_extension_smailyforopencart_helper->getSyncTime();
        $sync_time = date('c');
        while (true) {
            $subscribers = $this->model_extension_smailyforopencart_helper->getSubscribedCustomers($offset_sub, $last_sync);
            if (empty($subscribers)) {
                break;
            }
            $list = [];
            foreach ($subscribers as $subscriber) {
                // Get customer info based of selected fields from admin.
                $sync_fields = $this->model_extension_smailyforopencart_helper->getSyncFields();
                $customer = [];
                foreach ($sync_fields as $from_field) {
                    $to_field = $from_field;
                    if ($from_field === 'firstname') {
                        $to_field = 'first_name';
                    } elseif ($from_field === 'lastname') {
                        $to_field = 'last_name';
                    }
                    $customer[$to_field] = $subscriber[$from_field];
                }
                $offset_sub = $subscriber['customer_id'];
                $customer['is_unsubscribed'] = "0";
                array_push($list, $customer);
            }
            // Send subscribers to smaily.
            try {
                $response = $this->model_extension_smailyforopencart_helper->syncSubscribers($list);
            } catch (SmailyForOpenCart\HTTPError $error) {
                $msg = $error->getMessage();
                $this->log->write($msg);
                echo($msg);
                die(1);
            } catch (SmailyForOpenCart\APIError $error) {
                $msg = $error->getMessage();
                $this->log->write($msg);
                // Stop code execution and display error unless an invalid email was in query.
                // Smaily subscribes all valid emails and discards the rest.
                if ($error->getCode() !== SmailyForOpenCart\Request::API_ERR_INVALID_DATA) {
                    echo($msg);
                    die(1);
                }
            }
        }
        $this->model_extension_smailyforopencart_helper->editSettingValue(
            'module_smaily_for_opencart',
            'module_smaily_for_opencart_sync_time',
            $sync_time
        );

        $this->log->write('smaily subscriber sync finished: ' . json_encode($response));
        echo 'Smaily subscriber sync finished.';
    }
}
