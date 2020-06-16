<?php
/**
 * This is a plugin for OpenCart to handle subscribers directly
 * to your Smaily contacts, generate rss-feed of products and send
 * abandoned cart emails with Smaily templates.
 *
 * @package smaily_for_opencart
 * @author Smaily
 * @license GPL-3.0+
 * @copyright 2019 Smaily
 *
 * Plugin Name: Smaily for OpenCart
 * Description: Smaily email marketing and automation extension plugin for OpenCart.
 * Version: 1.1.4
 * License: GPL3
 * Author: Smaily
 * Author URI: https://smaily.com/
 *
 * Smaily for OpenCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Smaily for OpenCart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Smaily for OpenCart. If not, see <http://www.gnu.org/licenses/>.
 */
class ControllerModuleSmailyForOpencart extends Controller {
    private $error = array();

    public function index() {
        // Add language file.
        $this->load->language('module/smaily_for_opencart');
        // Settings model.
        $this->load->model('setting/setting');
        // Load smaily settings.
        $settings = $this->load->model_setting_setting->getSetting('smaily');
        // Add heading title.
        $this->document->setTitle($this->language->get('heading_title'));
        // When save is pressed.
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // Save optin form settings.
            $this->handleLayoutSaving();
            // Save Customer Sync settings and redirect page.
            $this->handleCustomerSync();
            // Code execution stops here.
            return;
        }

        // Text fields.
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_edit'] = $this->language->get('text_edit');
        // Section tab titles.
        $this->data['tab_general'] = $this->language->get('tab_general');
        $this->data['tab_sync'] = $this->language->get('tab_sync');
        $this->data['tab_rss'] = $this->language->get('tab_rss');
        // Save and cancel button.
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
        $this->data['validated'] = !empty($settings);
        // Validate button.
        $this->data['button_validate'] = $this->language->get('button_validate');
        $this->data['validate_title'] = $this->language->get('validate_title');
        // Display validation link.
        $this->data['validation_link'] = $this->language->get('validation_link');
        // Small texts.
        $this->data['small_subdomain'] = $this->language->get('small_subdomain');
        $this->data['small_password'] = $this->language->get('small_password');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['token'] = $this->session->data['token'];

        // Subscriber sync fields.
        $this->data['customer_sync_enable_title'] = $this->language->get('customer_sync_enable_title');
        $this->data['customer_sync_fields_title'] = $this->language->get('customer_sync_fields_title');
        $this->data['customer_sync_fields_help'] = $this->language->get('customer_sync_fields_help');
        // Subscriber sync cron fields.
        $this->data['customer_sync_cron_token_title'] = $this->language->get('customer_sync_cron_token_title');
        $this->data['customer_sync_cron_token_placeholder'] = $this->language->get('customer_sync_cron_token_placeholder');
        $this->data['customer_sync_cron_token_help'] = $this->language->get('customer_sync_cron_token_help');
        $this->data['customer_sync_cron_url'] = $this->config->get('config_url') . 'index.php?route=smailyforopencart/cron_customers&token=[token]';
        $this->data['customer_sync_cron_url_title'] = $this->language->get('customer_sync_cron_url_title');
        $this->data['customer_sync_cron_url_help'] = $this->language->get('customer_sync_cron_url_help');

        // RSS feed text.
        $this->data['rss_feed_title'] = $this->language->get('rss_feed_title');
        $this->data['rss_feed_text'] = $this->language->get('rss_feed_text');
        $this->data['rss_feed_url'] = $this->config->get('config_url') . 'index.php?route=smailyforopencart/rss';

        // Optin form settings text.
        $this->data['table_header_layout_text'] = $this->language->get('table_header_layout_text');
        $this->data['table_header_position_text'] = $this->language->get('table_header_position_text');
        $this->data['table_header_status_text'] = $this->language->get('table_header_status_text');
        $this->data['table_header_sort_order_text'] = $this->language->get('table_header_sort_order_text');
        $this->data['optin_form_position_top_text'] = $this->language->get('optin_form_position_top_text');
        $this->data['optin_form_position_bottom_text'] = $this->language->get('optin_form_position_bottom_text');
        $this->data['optin_form_position_left_text'] = $this->language->get('optin_form_position_left_text');
        $this->data['optin_form_position_right_text'] = $this->language->get('optin_form_position_right_text');
        $this->data['button_add_module_text'] = $this->language->get('button_add_module_text');
        $this->data['button_remove'] = $this->language->get('button_remove_module_text');

        // Fetch all available layouts.
        $this->load->model('design/layout');
        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        // Fetch optin form settings.
        $this->data['optin_form_settings'] = array();
        if (isset($this->request->post['smaily_optin_form_settings']) && is_array($this->request->post['smaily_optin_form_settings'])) {
            $this->data['optin_form_settings'] = $this->request->post['smaily_optin_form_settings'];
        } elseif ($this->config->get('smaily_for_opencart_module') && is_array($this->config->get('smaily_for_opencart_module'))) {
            $this->data['optin_form_settings'] = $this->config->get('smaily_for_opencart_module');
        }

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
        $api_credentials = isset($settings['smaily_api_credentials']) ? $settings['smaily_api_credentials'] : array();
        $sync_settings = isset($settings['smaily_customer_sync']) ? $settings['smaily_customer_sync'] : array();

        $this->data['subdomain'] = isset($api_credentials['subdomain']) ? $api_credentials['subdomain'] : '';
        $this->data['username'] = isset($api_credentials['username']) ? $api_credentials['username'] : '';
        $this->data['password'] = isset($api_credentials['password']) ? $api_credentials['password'] : '';

        // Customer sync enabled.
        if (isset($this->request->post['smaily_for_opencart_enable_subscribe'])) {
            $this->data['sync_enabled'] = $this->request->post['smaily_for_opencart_enable_subscribe'];
        } else {
            $this->data['sync_enabled'] = isset($sync_settings['enabled']) ? $sync_settings['enabled'] : '';
        }
        // Customer sync additional fields.
        if (isset($this->request->post['smaily_for_opencart_sync_fields'])) {
            $this->data['sync_fields'] = $this->request->post['smaily_for_opencart_sync_fields'];
        } else {
            $this->data['sync_fields'] = isset($sync_settings['fields']) ? $sync_settings['fields'] : array();
        }
        // Customer sync token.
        if (! empty($this->request->post['smaily_for_opencart_sync_token'])) {
            // Get sync token if user adds custom one.
            $this->data['sync_token'] = $this->request->post['smaily_for_opencart_sync_token'];
        } else {
            // Read sync token from db, if it's not in there, create one.
            $this->data['sync_token'] = isset($sync_settings['token']) ? $sync_settings['token'] : uniqid();
        }

        // Display chosen customer sync field as selected.
        $this->data['sync_fields_selected'] = array(
          'firstname' => array(
            'label' => $this->language->get('firstname'),
            'selected' => in_array('firstname', $this->data['sync_fields']),
          ),
          'lastname' => array(
            'label' => $this->language->get('lastname'),
            'selected' => in_array('lastname', $this->data['sync_fields']),
          ),
          'telephone' => array(
            'label' => $this->language->get('telephone'),
            'selected' => in_array('telephone', $this->data['sync_fields']),
          ),
          'date_added' => array(
            'label' => $this->language->get('date_added'),
            'selected' => in_array('date_added', $this->data['sync_fields']),
          ),
        );

        // Display selected layout module position as such.
        $this->data['optin_form_position_options'] = array(
          'content_top' => array(
            'label' => $this->language->get('optin_form_position_top_text'),
          ),
          'content_bottom' => array(
            'label' => $this->language->get('optin_form_position_bottom_text'),
          ),
          'column_left' => array(
            'label' => $this->language->get('optin_form_position_left_text'),
          ),
          'column_right' => array(
            'label' => $this->language->get('optin_form_position_right_text'),
          ),
        );

        // Load template
        $this->template = 'module/smaily_for_opencart.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }

    protected function handleLayoutSaving() {
        if (!$this->user->hasPermission('modify', 'module/smaily_for_opencart')) {
            return;
        }
        $optin_form_settings = [];
        // Load Smaily admin model for saving settings.
        $this->load->model('smailyforopencart/admin');
        // Check if optin form settings are in POST & that they're an array.
        if (!empty($this->request->post['smaily_optin_form_settings']) && is_array($this->request->post['smaily_optin_form_settings'])) {
            // Declare optin form settings in POST.
            $optin_form_settings = $this->request->post['smaily_optin_form_settings'];
            // Loop over each array, validating and sanitizing values.
            foreach ($optin_form_settings as &$optin_form_setting) {
                // Check if layout_id is number, if not default to '0'.
                $optin_form_setting['layout_id'] = (isset($optin_form_setting['layout_id'])
                    && is_numeric($optin_form_setting['layout_id'])) ? $optin_form_setting['layout_id'] : '0';
                // Check if position is one out of 4 available, if not default to content_bottom
                $optin_form_setting['position'] = (isset($optin_form_setting['position'])
                    && in_array($optin_form_setting['position'], array('content_top', 'content_bottom', 'column_left', 'column_right'), true)
                    ) ? $optin_form_setting['position'] : 'content_bottom';
                // Check if status is '1' or '0', if none default to '0'.
                $optin_form_setting['status'] = (isset($optin_form_setting['status'])
                    && $optin_form_setting['status'] === '1'
                    || $optin_form_setting['status'] === '0') ? $optin_form_setting['status'] : '0';
                // Check if sort order is a number, if not default to '1'.
                $optin_form_setting['sort_order'] = (isset($optin_form_setting['sort_order'])
                    && is_numeric($optin_form_setting['sort_order'])) ? $optin_form_setting['sort_order'] : '1';
            }
        }
        // Save layout settings. OC requires '_module' key scheme for it.
        $this->model_smailyforopencart_admin->editSettingValue('smaily', 'smaily_for_opencart_module', $optin_form_settings);
    }

    protected function handleCustomerSync() {
        if (!$this->user->hasPermission('modify', 'module/smaily_for_opencart')) {
            return;
        }
        // Load Smaily admin model for saving settings.
        $this->load->model('smailyforopencart/admin');
        // Declare customer sync objects.
        $customer_sync_enabled = $this->request->post['smaily_for_opencart_sync_enabled'];
        // For if no customer sync fields are selected.
        $customer_sync_fields = array();
        if (!empty($this->request->post['smaily_for_opencart_sync_fields']) && is_array($this->request->post['smaily_for_opencart_sync_fields'])) {
            $customer_sync_fields = $this->request->post['smaily_for_opencart_sync_fields'];
        }
        $customer_sync_token = $this->request->post['smaily_for_opencart_sync_token'];
        // Data validation.
        $customer_sync_enabled = ($customer_sync_enabled == 0 || $customer_sync_enabled == 1) ? $customer_sync_enabled : 0;
        $customer_sync_fields = array_intersect(['firstname', 'lastname', 'telephone', 'date_added'], $customer_sync_fields);
        // Remove all non-alphanumeric characters and spaces from token.
        $customer_sync_token = preg_replace('/[^a-zA-Z0-9]+/', '', $customer_sync_token);
        // Cron token field validation and sanitization.
        $customer_sync_token = !empty($customer_sync_token) ? $this->db->escape($customer_sync_token) : uniqid();

        // Add declared objects to array.
        $settings = [
          'enabled' => $customer_sync_enabled,
          'fields' => $customer_sync_fields,
          'token' => $customer_sync_token,
        ];
        // Save customer sync settings to db.
        $this->model_smailyforopencart_admin->editSettingValue('smaily', 'smaily_customer_sync', $settings);
        $this->session->data['success'] = $this->language->get('text_success');
        $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
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
        // Language for response.
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
              'password' => $password,
              'subdomain' => $subdomain,
              'username' => $username,
            ];
            // Save credentials to db.
            $this->model_smailyforopencart_admin->editSettingValue('smaily', 'smaily_api_credentials', $settings);
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
