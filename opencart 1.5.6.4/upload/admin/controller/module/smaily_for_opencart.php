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
        // Smaily admin page model.
        $this->load->model('smailyforopencart/admin');
        // Add heading title.
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Text fields
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_edit'] = $this->language->get('text_edit');

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
        $this->data['validated'] = $this->model_smailyforopencart_admin->getSettingValue('smaily_for_opencart_validated');
        // Validate button.
        $this->data['button_validate'] = $this->language->get('button_validate');
        $this->data['validate_title'] = $this->language->get('validate_title');
        // Display validation link
        $this->data['validation_link'] = $this->language->get('validation_link');
        // Small texts.
        $this->data['small_subdomain'] = $this->language->get('small_subdomain');
        $this->data['small_password'] = $this->language->get('small_password');
        $this->data['token'] = $this->session->data['token'];

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

        /**
         * Data for fields.
         */
        // Subdomain
        if (isset($this->request->post['smaily_for_opencart_subdomain'])) {
            $this->data['subdomain'] = $this->request->post['smaily_for_opencart_subdomain'];
        } else {
            $this->data['subdomain'] = $this->config->get('smaily_for_opencart_subdomain');
        }
        // Username
        if (isset($this->request->post['smaily_for_opencart_username'])) {
            $this->data['username'] = $this->request->post['smaily_for_opencart_username'];
        } else {
            $this->data['username'] = $this->config->get('smaily_for_opencart_username');
        }
        // Password
        if (isset($this->request->post['smaily_for_opencart_password'])) {
            $this->data['password'] = $this->request->post['smaily_for_opencart_password'];
        } else {
            $this->data['password'] = $this->config->get('smaily_for_opencart_password');
        }

        // Load template
        $this->template = 'module/smaily_for_opencart.tpl';
        $this->children = array(
            'common/header', 
            'common/footer',
        );
        $this->response->setOutput($this->render());
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
            $this->load->model('setting/setting');
            $this->load->model('smailyforopencart/admin');
            $settings = $this->model_smailyforopencart_admin->getSettingValue('smaily_for_opencart');
            // Used because save button saves whole form.
            $settings['smaily_for_opencart_validated'] = 1;
            $settings['smaily_for_opencart_subdomain'] = $subdomain;
            $settings['smaily_for_opencart_username'] = $username;
            $settings['smaily_for_opencart_password'] = $password;
            // Save credentials to db.
            $this->model_setting_setting->editSetting('smaily_for_opencart', $settings);
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
