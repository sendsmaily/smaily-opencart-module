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
 * Version: 1.5.2
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

require_once DIR_SYSTEM . 'library/smailyforopencart/request.php';

class ControllerExtensionModuleSmailyForOpencart extends Controller {
	private $error = array();
	private $version = '1.5.2';

	/**
	 * Module settings page entrypoint.
	 *
	 * @return void
	 */
	public function index() {
		$this->load->language('extension/module/smaily_for_opencart');

		// Setup module settings page.
		$this->document->addScript('view/javascript/smailyforopencart/smaily_admin.js');
		$this->document->setTitle($this->language->get('heading_title'));

		// Initialize Smaily for OpenCart models.
		$this->load->model('extension/smailyforopencart/config');
		$config_model = $this->model_extension_smailyforopencart_config->initialize();

		$this->load->model('extension/smailyforopencart/form');
		$form_model = $this->model_extension_smailyforopencart_form;

		// Handle settings validation.
		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$old_settings = $config_model->get();

			// Update configration settings.
			$settings = $form_model->sanitize($this->request->post);
			$settings = array_filter($settings, function($item) {
				return $item !== null;
			});
			$config_model->update($settings);

			// Validate posted settings.
			if ($this->validate($config_model->get()) === true) {
				// Register current time as the starting point of abandoned cart reminders.
				if (
					$old_settings['abandoned_cart_enabled'] === false &&
					$settings['abandoned_cart_enabled'] === true
				) {
					$config_model->set('abandoned_cart_enabled_at', date('Y-m-d H:i:s'));
				}

				// Register customer synchronization start time.
				if (empty($config_model->get('customer_sync_last_run_at'))) {
					$config_model->set('customer_sync_last_run_at', date('c', 0));
				}

				// Validation succeeded, that means API credentials are valid.
				$config_model->set('validated', true);

				$config_model->save();

				// Redirect to module settings page.
				$this->response->redirect(
					$this->url->link(
						'extension/module/smaily_for_opencart',
						array(
							'success' => 'true',
							'token' => $this->session->data['token'],
						),
						true
					)
				);
			}
		}

		$data = array();

		$data['settings'] = $config_model->get();
		$data['success'] = isset($this->request->get['success']) ? $this->language->get('error_success') : '';
		$data['errors'] = $this->error;

		// Compile translation prases.
		$data['t'] = $this->compileTranslationsPhrases();

		// Compile Customer Synchronization variables.
		$data['customer_sync'] = $this->compileCustomerSyncVariables($config_model, $form_model);

		// Compile Abandoned Cart variables.
		$data['abandoned_cart'] = $this->compileAbandonedCartVariables($config_model, $form_model);

		// Compile RSS variables.
		$data['rss'] = $this->compileRssVariables($config_model, $form_model);

		// Compile Abandoned Carts table variables.
		$data['abandoned_carts_table'] = $this->compileAbandonedCartsTableVariables($config_model, $form_model);

		// Compile breadcrumbs.
		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', array('token' => $this->session->data['token']), true)
			),
			array(
				'text' => $this->language->get('text_module'),
				'href' => $this->url->link(
					'marketplace/extension',
					array('token' => $this->session->data['token']),
					true
				),
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link(
					'extension/module/smaily_for_opencart',
					array('token' => $this->session->data['token']),
					true
				),
			),
		);

		// Compile admin URLs.
		$data['reset_credentials_url'] = $this->url->link(
			'extension/module/smaily_for_opencart/resetCredentials',
			array('token' => $this->session->data['token']),
			true
		);
		$data['action_url'] = $this->url->link(
			'extension/module/smaily_for_opencart',
			array('token' => $this->session->data['token']),
			true
		);
		$data['cancel_url'] = $this->url->link(
			'marketplace/extension',
			array('token' => $this->session->data['token']),
			true
		);

		// Render settings page.
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/smaily_for_opencart', $data));
	}

	/**
	 * API credentials reset endpoint.
	 *
	 * @return void
	 */
	public function resetCredentials() {
		$this->load->model('extension/smailyforopencart/config');

		if ($this->user->hasPermission('modify', 'extension/module/smaily_for_opencart') === true) {
			$this->model_extension_smailyforopencart_config
				->initialize()
				->update(array(
					'abandoned_cart_enabled' => false,
					'api_password' => '',
					'api_subdomain' => '',
					'api_username' => '',
					'customer_sync_enabled' => false,
					'status' => false,
					'validated' => false,
				))
				->save();
		}

		// Redirect to module settings page.
		$this->response->redirect(
			$this->url->link(
				'extension/module/smaily_for_opencart',
				array('token' => $this->session->data['token'], 'success' => 'true'),
				true
			)
		);
	}

	/**
	 * Customer Synchronization CRON token reset endpoint.
	 *
	 * @return void
	 */
	public function resetCustomerSyncCronToken() {
		$this->load->model('extension/smailyforopencart/config');

		if ($this->user->hasPermission('modify', 'extension/module/smaily_for_opencart') === true) {
			$this->model_extension_smailyforopencart_config
				->initialize()
				->update(array(
					'customer_sync_token' => uniqid(),
				))
				->save();
		}

		// Redirect to module settings page.
		$this->response->redirect(
			$this->url->link(
				'extension/module/smaily_for_opencart',
				array('token' => $this->session->data['token'], 'success' => 'true'),
				true
			)
		);
	}

	/**
	 * Abandoned Cart CRON token reset endpoint.
	 *
	 * @return void
	 */
	public function resetAbandonedCartCronToken() {
		$this->load->model('extension/smailyforopencart/config');

		if ($this->user->hasPermission('modify', 'extension/module/smaily_for_opencart') === true) {
			$this->model_extension_smailyforopencart_config
				->initialize()
				->update(array(
					'abandoned_cart_token' => uniqid(),
				))
				->save();
		}

		// Redirect to module settings page.
		$this->response->redirect(
			$this->url->link(
				'extension/module/smaily_for_opencart',
				array('token' => $this->session->data['token'], 'success' => 'true'),
				true
			)
		);
	}

	/**
	 * Module install callback.
	 *
	 * @return void
	 */
	public function install() {
		$db_prefix = DB_PREFIX;

		// Create Abandoned Cart table.
		$this->db->query(<<<EOT
			CREATE TABLE IF NOT EXISTS ${db_prefix}smaily_abandoned_carts (
				customer_id int(11) NOT NULL,
				sent_time datetime NOT NULL,
				PRIMARY KEY (customer_id)
			)
		EOT);

		// Register event listeners.
		$this->load->model('extension_event');

		$this->model_extension_event->addEvent(
			'smaily_order',
			'catalog/controller/checkout/confirm/after',
			'extension/smailyforopencart/order/removeSent'
		);
		$this->model_extension_event->addEvent(
			'smaily_upgrade',
			'admin/model/setting/modification/addModification/after',
			'extension/smailyforopencart/upgrade/upgrade'
		);
		$this->model_extension_event->addEvent(
			'smaily_reset_empty_cart',
			'catalog/controller/checkout/cart/remove/after',
			'extension/smailyforopencart/order/removeWhenCartEmpty'
		);

		// Generate default config.
		$this->load->model('extension/smailyforopencart/config');
		$this->model_extension_smailyforopencart_config
			->update(array(
				'abandoned_cart_autoresponder' => 0,
				'abandoned_cart_delay' => 60,
				'abandoned_cart_enabled_at' => '',
				'abandoned_cart_enabled' => 0,
				'abandoned_cart_fields' => array(),
				'abandoned_cart_token' => uniqid(),
				'api_password' => '',
				'api_subdomain' => '',
				'api_username' => '',
				'customer_sync_enabled' => 0,
				'customer_sync_fields' => array(),
				'customer_sync_last_run_at' => '',
				'customer_sync_token' => uniqid(),
				'db_version' => $this->version,
				'rss_category' => 0,
				'rss_limit' => 50,
				'rss_sort_by' => 'pd.name',
				'rss_sort_order' => 'asc',
				'status' => 0,
				'validated' => 0,
			))
			->save();
	}

	/**
	 * Module uninstall callback.
	 *
	 * @return void
	 */
	public function uninstall() {
		$this->load->model('extension_event');
		$this->load->model('setting/setting');

		// Remove tables.
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "smaily_abandoned_carts");

		// Remove event handlers.
		$this->model_extension_event->deleteEvent('smaily_order');
		$this->model_extension_event->deleteEvent('smaily_upgrade');
		$this->model_extension_event->deleteEvent('smaily_reset_empty_cart');

		// Remove plugin settings.
		$this->model_setting_setting->deleteSetting('module_smaily_for_opencart');
	}

	/**
	 * Settings update validation logic.
	 *
	 * @param array $input
	 * @return boolean
	 */
	protected function validate($input=array()) {
		$this->load->model('extension/smailyforopencart/form');
		$form_model = $this->model_extension_smailyforopencart_form;

		// Ensure user has enough permissions to update settings.
		if (!$this->user->hasPermission('modify', 'extension/module/smaily_for_opencart')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// Validate posted form data.
		$this->error = array_merge($this->error, $form_model->validate($input));

		return !$this->error;
	}

	/**
	 * Compile translation phrases.
	 *
	 * @return array
	 */
	protected function compileTranslationsPhrases() {
		$phrases = array(
			'button_reset_credentials',
			'error_abandoned_cart_autoresponder_not_selected',
			'error_abandoned_cart_delay_minimum',
			'error_api_notfound',
			'error_api_password_empty',
			'error_api_subdomain_empty',
			'error_api_unauthorized',
			'error_api_unknown',
			'error_api_username_empty',
			'error_permission',
			'error_rss_limit_exceeded',
			'error_success',
			'heading_abandoned_cart',
			'heading_abandoned_carts',
			'heading_connection_status',
			'heading_customer_sync',
			'heading_edit',
			'heading_rss',
			'heading_title',
			'help_abandoned_cart_autoresponder',
			'help_abandoned_cart_cron_url',
			'help_abandoned_cart_delay',
			'help_abandoned_cart_fields',
			'help_api_password',
			'help_connected',
			'help_customer_sync_cron_url',
			'help_customer_sync_fields',
			'help_disconnected',
			'help_rss_feed_url',
			'help_rss_limit',
			'label_abandoned_cart_autoresponder',
			'label_abandoned_cart_cron_url',
			'label_abandoned_cart_delay',
			'label_abandoned_cart_fields',
			'label_abandoned_cart_table_cart',
			'label_abandoned_cart_table_date',
			'label_abandoned_cart_table_email',
			'label_abandoned_cart_table_id',
			'label_abandoned_cart_table_name',
			'label_abandoned_cart_table_status',
			'label_api_password',
			'label_api_subdomain',
			'label_api_username',
			'label_customer_sync_cron_url',
			'label_customer_sync_fields',
			'label_enabled',
			'label_rss_category',
			'label_rss_feed_url',
			'label_rss_limit',
			'label_rss_sort_by',
			'label_rss_sort_order',
			'placeholder_api_password',
			'placeholder_api_subdomain',
			'placeholder_api_username',
			'text_all_products',
			'text_ascending',
			'text_connected',
			'text_descending',
			'text_disconnected',
			'text_minutes',
			'text_missing_automation_workflows',
			'text_module',
			'text_never',
			'text_no',
			'text_pending',
			'text_products',
			'text_reset_abandoned_cart_cron_token',
			'text_reset_customer_sync_cron_token',
			'text_sent',
			'text_yes',
		);

		$dictionary = array();
		foreach ($phrases as $phrase) {
			$dictionary[$phrase] = $this->language->get($phrase);
		}

		return $dictionary;
	}

	/**
	 * Compile variables for Customer Synchronization settings section.
	 *
	 * @param ModelExtensionSmailyForOpencartConfig $config_model
	 * @param ModelExtensionSmailyForOpencartForm $form_model
	 * @return array
	 */
	protected function compileCustomerSyncVariables($config_model, $form_model) {
		$data = array();

		// Collect customer synchronization field options.
		$selected_fields = $config_model->get('customer_sync_fields');
		$data['field_options'] = array_map(function($value) use ($selected_fields) {
			return array(
				'label' => $this->language->get('customer_sync_field_option_' . $value),
				'selected' => in_array($value, $selected_fields, true),
				'value' => $value,
			);
		}, $form_model->getAvailableCustomerSyncFields());

		// Compile customer synchronization CRON URL.
		$catalog_url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTPS_CATALOG : '');
		$data['cron_url'] = $catalog_url->link(
			'extension/smailyforopencart/cron_customers',
			array('token' => $config_model->get('customer_sync_token')),
			true
		);

		// Compile CRON token reset URL.
		$data['reset_token_url'] = $this->url->link(
			'extension/module/smaily_for_opencart/resetCustomerSyncCronToken',
			array('token' => $this->session->data['token']),
			true
		);

		return $data;
	}

	/**
	 * Compile variables for Abandoned Cart settings section.
	 *
	 * @param ModelExtensionSmailyForOpencartConfig $config_model
	 * @param ModelExtensionSmailyForOpencartForm $form_model
	 * @return array
	 */
	protected function compileAbandonedCartVariables($config_model, $form_model) {
		$data = array();

		// Collect abandoned cart field options.
		$selected_fields = $config_model->get('abandoned_cart_fields');
		$data['field_options'] = array_map(function($value) use ($selected_fields) {
			return array(
				'label' => $this->language->get('abandoned_cart_field_option_' . $value),
				'selected' => in_array($value, $selected_fields, true),
				'value' => $value,
			);
		}, $form_model->getAvailableAbandonedCartFields());

		// Collect abandoned cart automation options.
		$automation_automations = array();

		if ($config_model->get('validated') === true) {
			try {
				$automation_automations = (new SmailyForOpenCart\Request)
					->setSubdomain($config_model->get('api_subdomain'))
					->setCredentials($config_model->get('api_username'), $config_model->get('api_password'))
					->get('workflows', array('trigger_type' => 'form_submitted'));

			} catch (SmailyForOpenCart\HTTPError $error) {
				$this->log->write($error);
			}
		}

		$selected_automation = $config_model->get('abandoned_cart_autoresponder');
		$data['automation_options'] = array_map(function($value) use ($selected_automation) {
			return array(
				'label' => $value['title'],
				'selected' => $value['id'] === $selected_automation,
				'value' => $value['id'],
			);
		}, $automation_automations);

		// Compile abandoned cart CRON URL.
		$catalog_url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTPS_CATALOG : '');
		$data['cron_url'] = $catalog_url->link(
			'extension/smailyforopencart/cron_cart',
			array('token' => $config_model->get('abandoned_cart_token')),
			true
		);

		// Compile CRON token reset URL.
		$data['reset_token_url'] = $this->url->link(
			'extension/module/smaily_for_opencart/resetAbandonedCartCronToken',
			array('token' => $this->session->data['token']),
			true
		);

		return $data;
	}

	/**
	 * Compile variables for RSS settings section.
	 *
	 * @param ModelExtensionSmailyForOpencartConfig $config_model
	 * @param ModelExtensionSmailyForOpencartForm $form_model
	 * @return array
	 */
	protected function compileRssVariables($config_model, $form_model) {
		$data = array();

		$this->load->model('catalog/category');
		$selected_category = $config_model->get('rss_category');
		$data['categories'] = array_map(function($value) use ($selected_category) {
			return array(
				'label' => $value['name'],
				'selected' => (int)$value['category_id'] === $selected_category,
				'value' => $value['category_id'],
			);
		}, $this->model_catalog_category->getCategories());

		// Collect RSS feed sort options.
		$selected_sort_by = $config_model->get('rss_sort_by');
		$data['sort_by_options'] = array_map(function($value) use ($selected_sort_by) {
			return array(
				'label' => $this->language->get('rss_sort_option_' . str_replace('.', '_', $value)),
				'selected' => $value === $selected_sort_by,
				'value' => $value,
			);
		}, $form_model->getAvailableRssSortFields());

		// Collect RSS feed sort order options.
		$selected_sort_order = $config_model->get('rss_sort_order');
		$data['sort_order_options'] = array(
			array(
				'label' => $this->language->get('text_ascending'),
				'selected' => $selected_sort_order === 'asc',
				'value' => 'asc',
			),
			array(
				'label' => $this->language->get('text_descending'),
				'selected' => $selected_sort_order === 'desc',
				'value' => 'desc',
			),
		);

		// Compile RSS feed URL.
		$catalog_url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTPS_CATALOG : '');
		$data['feed_url'] = $catalog_url->link(
			'extension/smailyforopencart/rss',
			array(
				'category' => $config_model->get('rss_category'),
				'limit' => $config_model->get('rss_limit'),
				'sort_by' => $config_model->get('rss_sort_by'),
				'sort_order' => $config_model->get('rss_sort_order'),
			),
			true
		);
		$data['feed_base_url'] = $catalog_url->link('extension/smailyforopencart/rss', '', true);

		return $data;
	}

	/**
	 * Compile variables for Abandoned Cart status table section.
	 *
	 * @param ModelExtensionSmailyForOpencartConfig $config_model
	 * @param ModelExtensionSmailyForOpencartForm $form_model
	 * @return array
	 */
	protected function compileAbandonedCartsTableVariables($config_model, $form_model) {
		$data = array();

		// Determine Abandoned Cart status table sorting settings.
		$available_sorting_options = array(
			'customer_id',
			'email',
			'is_sent',
			'lastname',
			'sent_time',
		);
		$sort_by = isset($this->request->get['sort_by']) ? trim($this->request->get['sort_by']) : 'customer_id';
		if (!in_array($sort_by, $available_sorting_options, true)) {
			$sort_by = 'customer_id';
		}
		$sort_order = isset($this->request->get['sort_order']) ? trim($this->request->get['sort_order']) : 'asc';
		if (!in_array($sort_order, array('asc', 'desc'), true)) {
			$sort_order = 'asc';
		}
		$current_page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
		$current_page = max(0, $current_page);

		$limit = (int)$this->config->get('config_limit_admin');

		$data['current_sort_by'] = $sort_by;
		$data['current_sort_order'] = $sort_order;

		// Collect Abandoned Cart status table records.
		$this->load->model('extension/smailyforopencart/admin');
		$admin_model = $this->model_extension_smailyforopencart_admin;

		$data['collection'] = $admin_model->listAbandonedCarts(
			$sort_by,
			$sort_order,
			($current_page - 1) * $limit,
			$limit,
			$config_model->get('abandoned_cart_delay'),
			$config_model->get('abandoned_cart_enabled_at')
		);

		// Compile Abandoned Cart status table pagination.
		$total_count = $admin_model->countAbandonedCarts(
			$config_model->get('abandoned_cart_delay'),
			$config_model->get('abandoned_cart_enabled_at')
		);

		$pagination = new Pagination();
		$pagination->total = $total_count;
		$pagination->page = $current_page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link(
			'extension/module/smaily_for_opencart',
			array(
				'token' => $this->session->data['token'],
				'page' => '{page}',
			),
			true
		);
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf(
			$this->language->get('text_pagination'),
			// Offset.
			($total_count) ? (($current_page - 1) * $limit) + 1 : 0,
			// Limit.
			((($current_page - 1) * $limit) > ($total_count - $limit))
				? $total_count
				: ((($current_page - 1) * $limit) + $limit),
			// Total.
			$total_count,
			// Number of pages.
			ceil($total_count / $limit)
		);

		// Compile Abandoned Cart status table URLs.
		$url_parameters = array(
			'token' => $this->session->data['token'],
			'sort_by' => $sort_by,
			'sort_order' => isset($this->request->get['sort_order']) && $this->request->get['sort_order'] === 'asc'
				? 'desc'
				: 'asc',
			'page' => $current_page,
		);

		$data['sort_name_url'] = $this->url->link(
			'extension/module/smaily_for_opencart',
			array_merge($url_parameters, array('sort_by' => 'lastname')),
			true
		) . '#abandoned-carts';
		$data['sort_email_url'] = $this->url->link(
			'extension/module/smaily_for_opencart',
			array_merge($url_parameters, array('sort_by' => 'email')),
			true
		) . '#abandoned-carts';
		$data['sort_date_url'] = $this->url->link(
			'extension/module/smaily_for_opencart',
			array_merge($url_parameters, array('sort_by' => 'sent_time')),
			true
		) . '#abandoned-carts';
		$data['sort_status_url'] = $this->url->link(
			'extension/module/smaily_for_opencart',
			array_merge($url_parameters, array('sort_by' => 'is_sent')),
			true
		) . '#abandoned-carts';

		$catalog_url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTPS_CATALOG : '');
		$data['product_base_url'] = $catalog_url->link('product/product', array('product_id' => ''), true);

		return $data;
	}
}
