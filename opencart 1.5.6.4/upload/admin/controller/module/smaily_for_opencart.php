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
        // Settings model.
        $this->load->model('setting/setting');
        // Smaily admin page model.
        $this->load->model('smailyforopencart/admin');
        // Add heading title.
        $this->document->setTitle($this->language->get('heading_title'));
        
            // When save is pressed.
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            // Get validate field from db.
            $validated = $this->model_smailyforopencart_admin->getSettingValue('smaily_for_opencart_validated');

            // Form data.
            $data = $this->request->post;
            // Append validated status to form data.
            if ($validated) {
                $this->data['smaily_for_opencart_validated'] = $validated;
            }
            // Get credentials.
            $data['smaily_for_opencart_subdomain'] = $this->model_smailyforopencart_admin->getSettingValue('smaily_for_opencart_subdomain');
            $data['smaily_for_opencart_username'] = $this->model_smailyforopencart_admin->getSettingValue('smaily_for_opencart_username');
            $data['smaily_for_opencart_password'] = $this->model_smailyforopencart_admin->getSettingValue('smaily_for_opencart_password');
            // Save credential settings
            $this->model_setting_setting->editSetting('smaily_for_opencart', $data);
            // Success after pressing save
            $this->session->data['success'] = $this->language->get('text_success');
            // Redirect to module settings page.
            $this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        // Form sections.
        $this->data['sections'] = array(
            array(
                'section_id' => 1,
                'name' => $this->language->get('section_general'),
            ),
            array(
                'section_id' => 2,
                'name' => $this->language->get('section_customer'),
            ),
            array(
                'section_id' => 3,
                'name' => $this->language->get('section_abandoned'),
            ),
        );

        // Text fields
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_edit'] = $this->language->get('text_edit');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_banner'] = $this->language->get('entry_banner');
        $this->data['entry_width'] = $this->language->get('entry_width');
        $this->data['entry_height'] = $this->language->get('entry_height');
        $this->data['entry_status'] = $this->language->get('entry_status');

        // Enable module.
        $this->data['entry_enable_module_title'] = $this->language->get('entry_enable_module_title');
        $this->data['entry_enable_subscriber_title'] = $this->language->get('entry_enable_subscriber_title');
        $this->data['entry_enable_abandoned_title'] = $this->language->get('entry_enable_abandoned_title');
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
        // Autoresponder title.
        $this->data['entry_autoresponder_title'] = $this->language->get('entry_autoresponder_title');
        // Rss feed.
        $this->data['rss_feed_title'] = $this->language->get('rss_feed_title');
        $this->data['rss_feed_text'] = $this->language->get('rss_feed_text');

        // Subscriber sync fields.
        $this->data['entry_customer_sync_fields_title'] = $this->language->get('entry_customer_sync_fields_title');
        $this->data['sync_token_title'] = $this->language->get('sync_token_title');
        $this->data['sync_token_placeholder'] = $this->language->get('sync_token_placeholder');
        $this->data['sync_customer_url_title'] = $this->language->get('sync_customer_url_title');
        $this->data['customer_cron_url'] = $this->config->get('config_url') . 'index.php?route=smailyforopencart/cron_customers&token=[token]';
        $this->data['customer_cron_text'] = $this->language->get('customer_cron_text');
        // Customer sync option fields text.
        $this->data['firstname'] = $this->language->get('firstname');
        $this->data['lastname'] = $this->language->get('lastname');
        $this->data['telephone'] = $this->language->get('telephone');
        $this->data['date_added'] = $this->language->get('date_added');

        // Abandoned cart fields.
        $this->data['abandoned_sync_fields_title'] = $this->language->get('abandoned_sync_fields_title');
        $this->data['delay_title'] = $this->language->get('delay_title');
        $this->data['abandoned_minutes'] = $this->language->get('abandoned_minutes');
        $this->data['cart_token_title'] = $this->language->get('cart_token_title');
        $this->data['cart_token_placeholder'] = $this->language->get('cart_token_placeholder');
        $this->data['sync_cart_url_title'] = $this->language->get('sync_cart_url_title');
        $this->data['cart_cron_url'] = $this->config->get('config_url') . 'index.php?route=smailyforopencart/cron_cart&token=[token]';
        $this->data['cart_cron_text'] = $this->language->get('cart_cron_text');
        // Abandoned cart option fields text.
        $this->data['product_name'] = $this->language->get('product_name');
        $this->data['product_description'] = $this->language->get('product_description');
        $this->data['product_quantity'] = $this->language->get('product_quantity');
        $this->data['product_price'] = $this->language->get('product_price');

        // Small texts.
        $this->data['small_subdomain'] = $this->language->get('small_subdomain');
        $this->data['smaily_rss_url'] = $this->config->get('config_url') . 'index.php?route=smailyforopencart/rss';
        $this->data['small_password'] = $this->language->get('small_password');
        $this->data['small_sync_additional'] = $this->language->get('small_sync_additional');
        $this->data['small_cart_additional'] = $this->language->get('small_cart_additional');
        $this->data['small_cart_delay']      = $this->language->get('small_cart_delay');
        $this->data['small_token'] = $this->language->get('small_token');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['token'] = $this->session->data['token'];
        //Layout text and buttons
        $this->data['entry_layout'] = $this->language->get('entry_layout');
        $this->data['entry_position'] = $this->language->get('entry_position');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $this->data['text_content_top'] = $this->language->get('text_content_top');
        $this->data['text_content_bottom'] = $this->language->get('text_content_bottom');       
        $this->data['text_column_left'] = $this->language->get('text_column_left');
        $this->data['text_column_right'] = $this->language->get('text_column_right');
        $this->data['button_module'] = $this->language->get('button_module');
        $this->data['button_remove'] = $this->language->get('button_remove');
        //Get layouts, modules
        $this->load->model('design/layout');
        $this->data['layouts'] = $this->model_design_layout->getLayouts();
        $this->data['modules'] = array();
        //Fetch modules
        if (isset($this->request->post['smaily_for_opencart_module'])) {
            $this->data['modules'] = $this->request->post['smaily_for_opencart_module'];
        } elseif ($this->config->get('smaily_for_opencart_module')) { 
            $this->data['modules'] = $this->config->get('smaily_for_opencart_module');
        }   


        // Display success message after save.
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->language->get('text_success');
        } else {
            $this->data['success'] = '';
        }

        // Display warning.
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }
        // Subdomain error.
        if (isset($this->error['subdomain'])) {
            $this->data['error_subdomain'] = $this->error['subdomain'];
        } else {
            $this->data['error_subdomain'] = '';
        }
        // Username error.
        if (isset($this->error['username'])) {
            $this->data['error_username'] = $this->error['username'];
        } else {
            $this->data['error_username'] = '';
        }
        // Password error.
        if (isset($this->error['password'])) {
            $this->data['error_password'] = $this->error['password'];
        } else {
            $this->data['error_password'] = '';
        }
        // Validate error.
        if (isset($this->error['validate'])) {
            $this->data['error_validate'] = $this->error['validate'];
        } else {
            $this->data['error_validate'] = '';
        }
        // Abandoned cart autoresponder error.
        if (isset($this->error['autoresponder'])) {
            $this->data['error_autoresponder'] = $this->error['autoresponder'];
        } else {
            $this->data['error_autoresponder'] = '';
        }
        // Abandoned cart delay error.
        if (isset($this->error['cart_delay'])) {
            $this->data['error_delay'] = $this->error['cart_delay'];
        } else {
            $this->data['error_delay'] = '';
        }

        // BreadCrumb
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url
                ->link('common/dashboard', 'token=' . $this->session->data['token'], true),
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

        // Module enabled.
        if (isset($this->request->post['smaily_for_opencart_status'])) {
            $this->data['module_status'] = $this->request->post['smaily_for_opencart_status'];
        } else {
            $this->data['module_status'] = $this->config->get('smaily_for_opencart_status');
        }
        // Subscriber sync enabled.
        if (isset($this->request->post['smaily_for_opencart_enable_subscribe'])) {
            $this->data['subscribe_status'] = $this->request->post['smaily_for_opencart_enable_subscribe'];
        } else {
            $this->data['subscribe_status'] = $this->config->get('smaily_for_opencart_enable_subscribe');
        }
        // Abandoned cart enabled.
        if (isset($this->request->post['smaily_for_opencart_enable_abandoned'])) {
            $this->data['abandoned_status'] = $this->request->post['smaily_for_opencart_enable_abandoned'];
        } else {
            $this->data['abandoned_status'] = $this->config->get('smaily_for_opencart_enable_abandoned');
        }

        // Customer sync additional fields.
        if (isset($this->request->post['smaily_for_opencart_syncronize_additional'])) {
            $this->data['syncronize_additional'] = $this->request->post['smaily_for_opencart_syncronize_additional'];
        } else {
            $this->data['syncronize_additional'] = $this->config->get('smaily_for_opencart_syncronize_additional');
        }
        // Customer sync token.
        if (! empty($this->request->post['smaily_for_opencart_sync_token'])) {
            // Get sync token if user adds custom one.
            $this->data['sync_token'] = $this->request->post['smaily_for_opencart_sync_token'];
        } else {
            if (! empty($this->config->get('smaily_for_opencart_sync_token'))) {
                $this->data['sync_token'] = $this->config->get('smaily_for_opencart_sync_token');
            } else {
                // Generate random token if not saved in db.
                $this->data['sync_token'] = uniqid();
            }
        }

        // Abandoned cart autoresponder.
        if (isset($this->request->post['smaily_for_opencart_abandoned_autoresponder'])) {
            $this->data['abandoned_autoresponder'] = json_decode(html_entity_decode($this->request->post['smaily_for_opencart_abandoned_autoresponder']), true);
        } else {
            $this->data['abandoned_autoresponder'] = json_decode(html_entity_decode($this->config->get('smaily_for_opencart_abandoned_autoresponder')), true);
        }
        // Abandoned cart additional fields.
        if (isset($this->request->post['smaily_for_opencart_abandoned_additional'])) {
            $this->data['abandoned_additional'] = $this->request->post['smaily_for_opencart_abandoned_additional'];
        } else {
            $this->data['abandoned_additional'] = $this->config->get('smaily_for_opencart_abandoned_additional');
        }
        // Abandoned cart delay.
        if (isset($this->request->post['smaily_for_opencart_cart_delay'])) {
            $this->data['cart_delay'] = $this->request->post['smaily_for_opencart_cart_delay'];
        } else {
            $this->data['cart_delay'] = $this->config->get('smaily_for_opencart_cart_delay');
        }
        // Abandoned cart token.
        if (! empty($this->request->post['smaily_for_opencart_cart_token'])) {
            // Get sync token if user adds custom one.
            $this->data['cart_token'] = $this->request->post['smaily_for_opencart_cart_token'];
        } else {
            if (! empty($this->config->get('smaily_for_opencart_cart_token'))) {
                $this->data['cart_token'] = $this->config->get('smaily_for_opencart_cart_token');
            } else {
                // Generate random token if not saved in db.
                $this->data['cart_token'] = uniqid();
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

    protected function validate() {
        // Permission.
        if (!$this->user->hasPermission('modify', 'module/smaily_for_opencart')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        // Subdomain filled.
        if (empty(trim($this->request->post['smaily_for_opencart_subdomain']))) {
            $this->error['subdomain'] = $this->language->get('error_subdomain');
        }
        // Username filled.
        if (empty(trim($this->request->post['smaily_for_opencart_username']))) {
            $this->error['username'] = $this->language->get('error_username');
        }
        // Password filled.
        if (empty(trim($this->request->post['smaily_for_opencart_password']))) {
            $this->error['password'] = $this->language->get('error_password');
        }

        // Validate abanconed cart.
        if (isset($this->request->post['smaily_for_opencart_enable_abandoned'])
            && (int) $this->request->post['smaily_for_opencart_enable_abandoned'] === 1
            ) {
            // Autoresponder.
            if (empty($this->request->post['smaily_for_opencart_abandoned_autoresponder'])) {
                $this->error['autoresponder'] = $this->language->get('abandoned_autoresponder_error');
            }
            // Delay time.
            if ($this->request->post['smaily_for_opencart_cart_delay'] < 15) {
                $this->error['cart_delay'] = $this->language->get('cart_delay_error');
            }
        }
        // Check if credentials are validated.
        $this->validated = $this->config->get('smaily_for_opencart_validated');
        if (! $this->validated) {
            // Error message.
            $this->error['validate'] = $this->language->get('error_validate');
        }

        return !$this->error;
    }

    /**
     * When validate button is pressed on admin screen.
     *
     * @return void
     */
    public function ajaxValidateCredentials() {

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
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
                if ($this->user->hasPermission('modify', 'module/smaily_for_opencart')) {
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
                }
            } elseif (array_key_exists('error', $validate)) {
                $response['error'] = $validate['error'];
            } else {
                $response['error'] = $this->language->get('validated_error');
            }
            // Return to ajax call.
            echo json_encode($response);
        }
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


    /**
     * Get autoresponders when user has validated and credentials are saved.
     *
     * @return void
     */
    public function ajaxGetAutoresponders() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $list = [];
            // Load models.
            $this->load->model('setting/setting');
            $this->load->model('smailyforopencart/admin');
            // Language for resonse.
            $this->load->language('module/smaily_for_opencart');
            // Check if autoresponders are validated.
            if ((int) $this->model_smailyforopencart_admin->getSettingValue('smaily_for_opencart_validated') === 1) {
                $autoresponders = $this->model_smailyforopencart_admin->apiCall(
                    'workflows',
                    ['trigger_type' => 'form_submitted'],
                    'GET'
                );
                // Return autoresponders
                if (!empty($autoresponders)) {
                    foreach ($autoresponders as $autoresponder) {
                        if (!empty($autoresponder['id']) && !empty($autoresponder['title'])) {
                            $list[$autoresponder['id']] = trim($autoresponder['title']);
                        }
                    }
                }
            }
            echo json_encode($list);
        }
    }
    // Replaced with VQmod, 1.5.6.4 does not support addEvent
    /**
     * Creates abandoned carts table and action hook for module.
     *
     * @return void
     */
    /*public function install() {
        //$this->load->model('extension/event');
        $this->load->model('smailyforopencart/admin');
        // Create database.
        $this->model_smailyforopencart_admin->install();
        // Add event listener for order.
        $this->model_extension_event->addEvent('smaily_order', 'catalog/controller/checkout/confirm/after', 'smailyforopencart/order/removeSent');
    }*/

    /**
     * Removes action hook and deletes abandoned cart table.
     *
     * @return void
     */
    /*public function uninstall() {
        //$this->load->model('extension/event');
        $this->load->model('smailyforopencart/admin');
        // Remove smaily table
        $this->model_smailyforopencart_admin->uninstall();
        // Remove event handler.
        $this->model_extension_event->deleteEvent('smaily_order');
    }*/
}
