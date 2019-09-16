<?php
/**
 * This is a plugin for Opencart to handle subscribers directly
 * to your Smaily contacts, generate rss-feed of products and send
 * abandoned cart emails with Smaily templates.
 *
 * @package smaily_for_opencart
 * @author Smaily
 * @license GPL-3.0+
 * @copyright 2019 Smaily
 *
 * Plugin Name: Smaily for Opencart
 * Description: Smaily email marketing and automation extension plugin for Opencart.
 * Version: 1.1.3
 * License: GPL3
 * Author: Smaily
 * Author URI: https://smaily.com/
 *
 * Smaily for Opencart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Smaily for Opencart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Smaily for Opencart. If not, see <http://www.gnu.org/licenses/>.
 */
class ControllerModuleSmailyForOpencart extends Controller {
    private $error = array();

    public function index() {
        // Add language file.
        $this->load->language('module/smaily_for_opencart');
        // Settings model
        $this->load->model('setting/setting');
        // Smaily admin model
        $this->load->model('smailyforopencart/admin');
        // Load smaily settings
        $settings = $this->load->model_setting_setting->getSetting('smaily'); 
        // Add heading title.
        $this->document->setTitle($this->language->get('heading_title'));
        // When save is pressed.
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            // Save Customer Sync settings.
            $this->handleCustomerSync();
            // Display Success after pressing save.
            $this->session->data['success'] = $this->language->get('text_success');
            // Redirect to module settings page.
            $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        // Text fields
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_edit'] = $this->language->get('text_edit');
        // Section tab titles
        $this->data['tab_general'] = $this->language->get('tab_general');
        $this->data['tab_sync'] = $this->language->get('tab_sync');
        // Save and cancel button
        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        // Subdomain.
        $this->data['subdomain_title'] = $this->language->get('entry_subdomain_title');
        $this->data['subdomain_placeholder'] = $this->language->get('placeholder_subdomain');
        // Username.
        $this->data['username_title'] = $this->language->get('entry_username_title');
        $this->data['username_placeholder'] = $this->language->get('placeholder_username');
        // Password.
        $this->data['password_title'] = $this->language->get('entry_password_title');
        $this->data['password_placeholder'] = $this->language->get('placeholder_password');
        // Credentials validated status.
        $this->data['validated'] = !empty($settings['smaily_api_credentials']);
        // Validate button.
        $this->data['button_validate'] = $this->language->get('button_validate');
        $this->data['validate_title'] = $this->language->get('validate_title');
        // Display validation link
        $this->data['validation_link'] = $this->language->get('validation_link');
        // Small texts.
        $this->data['small_subdomain'] = $this->language->get('small_subdomain');
        $this->data['small_password'] = $this->language->get('small_password');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['token'] = $this->session->data['token'];

        // Subscriber sync fields.
        $this->data['entry_enable_subscriber_title'] = $this->language->get('entry_enable_subscriber_title');
        $this->data['entry_customer_sync_fields_title'] = $this->language->get('entry_customer_sync_fields_title');
        $this->data['sync_token_title'] = $this->language->get('sync_token_title');
        $this->data['sync_token_placeholder'] = $this->language->get('sync_token_placeholder');
        $this->data['sync_customer_url_title'] = $this->language->get('sync_customer_url_title');
        $this->data['customer_cron_url'] = $this->config->get('config_url') . 'index.php?route=smailyforopencart/cron_customers&token=[token]';
        $this->data['customer_cron_text'] = $this->language->get('customer_cron_text');
        // Subscriber sync additional fields
        $this->data['firstname'] = $this->language->get('firstname');
        $this->data['lastname'] = $this->language->get('lastname');
        $this->data['telephone'] = $this->language->get('telephone');
        $this->data['date_added'] = $this->language->get('date_added');
        $this->data['small_sync_additional'] = $this->language->get('small_sync_additional');
        $this->data['small_token'] = $this->language->get('small_token');


        // Validate error.
        if (isset($this->error['validate'])) {
            $this->data['error_validate'] = $this->error['validate'];
        } else {
            $this->data['error_validate'] = '';
        }

        // BreadCrumb
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url
                ->link('common/home', 'token=' . $this->session->data['token'], true),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url
                ->link('extension/module', 'token=' . $this->session->data['token'], true),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url
                ->link('module/smaily_for_opencart', 'token=' . $this->session->data['token'], true),
            'separator' => ' :: '
        );
        // Save and cancel button href-s.
        $this->data['action'] = $this->url->link('module/smaily_for_opencart', 'token=' . $this->session->data['token'], true);
        $this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], true);
        /**
         * Data for fields.
         */
        // Subdomain
        if (isset($this->request->post['smaily_for_opencart_subdomain'])) {
            $this->data['subdomain'] = $this->request->post['smaily_for_opencart_subdomain'];
        } else {
            $this->data['subdomain'] = isset($settings['smaily_api_credentials']['subdomain']) ? $settings['smaily_api_credentials']['subdomain'] : '';
        }
        // Username
        if (isset($this->request->post['smaily_for_opencart_username'])) {
            $this->data['username'] = $this->request->post['smaily_for_opencart_username'];
        } else {
            $this->data['username'] = isset($settings['smaily_api_credentials']['username']) ? $settings['smaily_api_credentials']['username'] : '';
        }
        // Password
        if (isset($this->request->post['smaily_for_opencart_password'])) {
            $this->data['password'] = $this->request->post['smaily_for_opencart_password'];
        } else {
            $this->data['password'] = isset($settings['smaily_api_credentials']['password']) ? $settings['smaily_api_credentials']['password'] : '';
        }

        // Subscriber sync enabled.
        if (isset($this->request->post['smaily_for_opencart_enable_subscribe'])) {
            $this->data['subscribe_status'] = $this->request->post['smaily_for_opencart_enable_subscribe'];
        } else {
            $this->data['subscribe_status'] = isset($settings['smaily_customer_sync']['subscribe_status']) ? $settings['smaily_customer_sync']['subscribe_status'] : '';
        }
        // Customer sync additional fields.
        if (isset($this->request->post['smaily_for_opencart_syncronize_additional'])) {
            $this->data['subscribe_additional'] = $this->request->post['smaily_for_opencart_syncronize_additional'];
        } else {
            $this->data['subscribe_additional'] = isset($settings['smaily_customer_sync']['subscribe_additional']) ? $settings['smaily_customer_sync']['subscribe_additional'] : '';
        }
        // Customer sync token.
        if (! empty($this->request->post['smaily_for_opencart_sync_token'])) {
            // Get sync token if user adds custom one.
            $this->data['sync_token'] = $this->request->post['smaily_for_opencart_sync_token'];
        } else {
            if (! empty($this->config->get('smaily_for_opencart_sync_token'))) {
                $this->data['sync_token'] = isset($settings['sync_token']) ? $settings['sync_token'] : '';
            } else {
                // Generate random token if not saved in db.
                $this->data['sync_token'] = uniqid();
            }
        }

        // Load template
        $this->template = 'module/smaily_for_opencart.tpl';
        $this->children = array(
            'common/header', 
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }
    protected function handleCustomerSync() {
        // Load Smaily admin model for saving settings.
        $this->load->model('smailyforopencart/admin');
        // Declare customer sync field objects.
        $subscribe_status = $this->request->post['smaily_for_opencart_enable_subscribe'];
        $subscribe_sync_token = $this->request->post['smaily_for_opencart_sync_token'];
        // If no fields are selected in customer sync list save it to db as blank.
        if (isset($this->request->post['smaily_for_opencart_syncronize_additional'])) {
            $subscribe_additional = $this->request->post['smaily_for_opencart_syncronize_additional'];
        } else {
            $subscribe_additional = '';
        }
        // Add declared objects to array.
        $settings['smaily_customer_sync']['subscribe_status'] = $subscribe_status;
        $settings['smaily_customer_sync']['subscribe_additional'] = $subscribe_additional;
        $settings['smaily_customer_sync']['subscribe_sync_token'] = $subscribe_sync_token;
        // Save customer sync settings to db.
        $this->model_smailyforopencart_admin->editSettingValue('smaily', 'smaily_customer_sync', $settings['smaily_customer_sync']);
        return;    
    }
    /**
     * When validate button is pressed on admin screen.
     *
     * @return void
     */
    public function ajaxValidateCredentials() {
        $isRequestMethodPost = $this->request->server['REQUEST_METHOD'] === 'POST';
        $hasPermissionsToModify = $this->user->hasPermission('modify', 'module/smaily_for_opencart');
        if (!$isRequestMethodPost || !$hasPermissionsToModify) {
            return;
        }
        $response = [];
        // Language for resonse.
        $this->load->language('module/smaily_for_opencart');
        // Check if all fields are set.
        if (empty($this->request->post['subdomain']) ||
            empty($this->request->post['username']) ||
            empty($this->request->post['password'])) {
                // Show empty field error message.
                $response['error'] = $this->language->get('error_validate_empty');
                echo json_encode($response);
                return;
        }

        $subdomain = $this->request->post['subdomain'];
        // Normalize subdomain.
        // First, try to parse as full URL. If that fails, try to parse as subdomain.sendsmaily.net, and
        // if all else fails, then clean up subdomain and pass as is.
        if (filter_var($subdomain, FILTER_VALIDATE_URL)) {
            $url = parse_url($subdomain);
            $parts = explode('.', $url['host']);
            $subdomain = count($parts) >= 3 ? $parts[0] : '';
        } elseif (preg_match('/^[^\.]+\.sendsmaily\.net$/', $subdomain)) {
            $parts = explode('.', $subdomain);
            $subdomain = $parts[0];
        }
        $subdomain = preg_replace('/[^a-zA-Z0-9]+/', '', $subdomain);
        $subdomain = $this->db->escape($subdomain);
        $username =  $this->db->escape($this->request->post['username']);
        $password = $this->db->escape($this->request->post['password']);
        // Validate credentials with a call to Smaily.
        $validate = $this->validateSmailyCredentials($subdomain, $username, $password);

        // If validated, save validated status to db.
        if (array_key_exists('success', $validate)) {
            // Load Smaily admin model.
            $this->load->model('smailyforopencart/admin');
            // Used because save button saves whole form.
            $settings = [
            'smaily_api_credentials' => [
                'password' => $password,
                'subdomain' => $subdomain,
                'username' => $username,     
                ]    
            ];
            // Save credentials to db.
            $this->model_smailyforopencart_admin->editSettingValue('smaily', 'smaily_api_credentials', $settings['smaily_api_credentials']);
            $response['success'] = $validate['success'];
        } elseif (array_key_exists('error', $validate)) {
            $response['error'] = $validate['error'];
        } else {
            $response['error'] = $this->language->get('validated_error');
        }
        // Return to ajax call.
        echo json_encode($response);        
    }

    /**
     * Runs when validate button is pressed.
     *
     * @param string $subdomain
     * @param string $username
     * @param string $password
     * @return void
     */
    public function validateSmailyCredentials($subdomain, $username, $password) {
        // Response.
        $response = [];
        // cUrl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $subdomain . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $output = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:  # OK
                    $response['success'] = $this->language->get('validated_success');
                    break;
                case 401:
                    $response['error'] = $this->language->get('validated_unauthorized');
                    break;
                case 404:
                    $response['error'] = $this->language->get('validated_subdomain_error');
                    break;
                default:
                    $response['error'] = $this->language->get('validated_error');
            }
        }
        curl_close($ch);
        // Response from API call.
        return $response;
    }
}
