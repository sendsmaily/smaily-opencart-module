<?php

/**
 * This is a plugin for OpenCart to handle subscribers directly
 * to your Smaily contacts, generate RSS-feed of products and send
 * abandoned cart emails with Smaily templates.
 *
 * @package smaily_for_opencart
 * @author Smaily
 * @license GPL-3.0+
 * @copyright 2019 Smaily
 *
 * Plugin Name: Smaily for OpenCart
 * Description: Integrate OpenCart with Smaily email marketing and automation platform.
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

require_once DIR_SYSTEM . 'library/smaily_for_opencart/request.php';

class ControllerExtensionModuleSmailyForOpencart extends Controller {
	private $error = array();
	private $version = '1.5.2';

	public function index() {
		$this->load->language('extension/module/smaily_for_opencart');

		// Setup module settings page.
		$this->document->addScript('view/javascript/smaily_for_opencart/admin.js');
		$this->document->setTitle($this->language->get('heading_title'));

		// Handle settings validation.
		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$old_settings = $this->getConfigModel()->get();

			// Update configration settings.
			$settings = $this->getFormModel()->sanitize($this->request->post);
			$settings = array_filter($settings, function($item) {
				return $item !== null;
			});
			$this->getConfigModel()->update($settings);

			// Validate posted settings.
			if ($this->validate($this->getConfigModel()->get()) === true) {
				// Register current time as the starting point of abandoned cart reminders.
				if (
					$old_settings['abandoned_cart_enabled'] === false &&
					$settings['abandoned_cart_enabled'] === true
				) {
					$this->getConfigModel()->set('abandoned_cart_enabled_at', date('Y-m-d H:i:s'));
				}

				// Register customer synchronization start time.
				if (empty($this->getConfigModel()->get('customer_sync_last_run_at'))) {
					$this->getConfigModel()->set('customer_sync_last_run_at', date('c', 0));
				}

				// Validation succeeded, that means API credentials are valid.
				$this->getConfigModel()->set('validated', true);

				$this->getConfigModel()->save();

				// Redirect to module settings page.
				$this->response->redirect(
					$this->getCompatModel()->linkWithUserToken(
						$this,
						'extension/module/smaily_for_opencart',
						array('success' => 'true')
					)
				);
			}
		}

		// Collect view variables.
		$data = array();

		$data['errors'] = $this->error;
		$data['settings'] = $this->getConfigModel()->get();
		$data['success'] = isset($this->request->get['success']) ? $this->language->get('error_success') : '';

		// Compile translation prases.
		$data['t'] = $this->load->language('extension/module/smaily_for_opencart');

		// Compile Customer Synchronization variables.
		$data['customer_sync'] = $this->compileCustomerSyncVariables();

		// Compile Abandoned Cart variables.
		$data['abandoned_cart'] = $this->compileAbandonedCartVariables();

		// Compile RSS variables.
		$data['rss'] = $this->compileRssVariables();

		// Compile Abandoned Carts table variables.
		$data['abandoned_carts_table'] = $this->compileAbandonedCartsTableVariables();

		// Compile breadcrumbs.
		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->getCompatModel()->linkWithUserToken($this, 'common/dashboard'),
			),
			array(
				'text' => $this->language->get('text_module'),
				'href' => $this->getCompatModel()->linkWithUserToken($this, $this->getCompatModel()->getRouteToModules()),
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->getCompatModel()->linkWithUserToken($this, $this->getCompatModel()->getRouteToMain()),
			),
		);

		// Compile admin URLs.
		$data['reset_credentials_url'] = $this->getCompatModel()
			->linkWithUserToken($this, $this->getCompatModel()->getRouteToMain() . '/resetCredentials');
		$data['action_url'] = $this->getCompatModel()
			->linkWithUserToken($this, $this->getCompatModel()->getRouteToMain());
		$data['cancel_url'] = $this->getCompatModel()
			->linkWithUserToken($this, $this->getCompatModel()->getRouteToModules());

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
		if ($this->user->hasPermission('modify', $this->getCompatModel()->getRouteToMain()) === true) {
			$this->getConfigModel()
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
			$this->getCompatModel()->linkWithUserToken(
				$this,
				$this->getCompatModel()->getRouteToMain(),
				array('success' => 'true')
			)
		);
	}

	/**
	 * Customer Synchronization CRON token reset endpoint.
	 *
	 * @return void
	 */
	public function resetCustomerSyncCronToken() {
		if ($this->user->hasPermission('modify', $this->getCompatModel()->getRouteToMain()) === true) {
			$this->getConfigModel()
				->update(array(
					'customer_sync_token' => uniqid(),
				))
				->save();
		}

		// Redirect to module settings page.
		$this->response->redirect(
			$this->getCompatModel()->linkWithUserToken(
				$this,
				$this->getCompatModel()->getRouteToMain(),
				array('success' => 'true')
			)
		);
	}

	/**
	 * Abandoned Cart CRON token reset endpoint.
	 *
	 * @return void
	 */
	public function resetAbandonedCartCronToken() {
		if ($this->user->hasPermission('modify', $this->getCompatModel()->getRouteToMain()) === true) {
			$this->getConfigModel()
				->update(array(
					'abandoned_cart_token' => uniqid(),
				))
				->save();
		}

		// Redirect to module settings page.
		$this->response->redirect(
			$this->getCompatModel()->linkWithUserToken(
				$this,
				$this->getCompatModel()->getRouteToMain(),
				array('success' => 'true')
			)
		);
	}

	/**
	 * Module install callback.
	 *
	 * @return void
	 */
	public function install() {
		// Create Abandoned Cart table.
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "smaily_abandoned_carts` (" .
			"`customer_id` int(11) NOT NULL, " .
			"`sent_time` datetime NOT NULL, " .
			"PRIMARY KEY (`customer_id`))"
		);

		// Register event listeners.
		$listeners = array(
			'admin/model/setting/modification/addModification/after' => 'extension/smaily_for_opencart/upgrade/upgrade',
			'catalog/controller/checkout/cart/remove/after' => 'extension/smaily_for_opencart/order/removeWhenCartEmpty',
			'catalog/controller/checkout/confirm/after' => 'extension/smaily_for_opencart/order/removeSent',
		);
		foreach ($listeners as $listener => $handler) {
			$this->getCompatModel()->addEvent('module_smaily_for_opencart', $listener, $handler);
		}

		// Generate default config.
		// Note! Not using getConfigModel method here to avoid running "initialize" on empty set.
		$this->load->model('extension/smaily_for_opencart/config');
		$this->model_extension_smaily_for_opencart_config
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
		// Remove tables.
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "smaily_abandoned_carts`");

		// Remove event handlers.
		$this->getCompatModel()->deleteEvent('module_smaily_for_opencart');

		// Remove plugin settings.
		$this->getConfigModel()->purge();
	}

	/**
	 * Settings update validation logic.
	 *
	 * @param array $input
	 * @return boolean
	 */
	protected function validate($input = array()) {
		// Ensure user has enough permissions to update settings.
		if (!$this->user->hasPermission('modify', $this->getCompatModel()->getRouteToMain())) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// Validate posted form data.
		$this->error = array_merge($this->error, $this->getFormModel()->validate($input));

		return !$this->error;
	}

	/**
	 * Compile variables for Customer Synchronization settings section.
	 *
	 * @return array
	 */
	protected function compileCustomerSyncVariables() {
		$data = array();

		// Collect customer synchronization field options.
		$selected_fields = $this->getConfigModel()->get('customer_sync_fields');
		$data['field_options'] = array_map(function($value) use ($selected_fields) {
			return array(
				'label' => $this->language->get('customer_sync_field_option_' . $value),
				'selected' => in_array($value, $selected_fields, true),
				'value' => $value,
			);
		}, $this->getFormModel()->getAvailableCustomerSyncFields());

		// Compile customer synchronization CRON URL.
		$data['cron_url'] = $this->getCompatModel()->linkToCatalog(
			$this,
			'extension/smaily_for_opencart/cron/customer_sync',
			array('cron_token' => $this->getConfigModel()->get('customer_sync_token'))
		);

		// Compile CRON token reset URL.
		$data['reset_token_url'] = $this->getCompatModel()
			->linkWithUserToken($this, $this->getCompatModel()->getRouteToMain() . '/resetCustomerSyncCronToken');

		return $data;
	}

	/**
	 * Compile variables for Abandoned Cart settings section.
	 *
	 * @return array
	 */
	protected function compileAbandonedCartVariables() {
		$data = array();

		// Collect abandoned cart field options.
		$selected_fields = $this->getConfigModel()->get('abandoned_cart_fields');
		$data['field_options'] = array_map(function($value) use ($selected_fields) {
			return array(
				'label' => $this->language->get('abandoned_cart_field_option_' . $value),
				'selected' => in_array($value, $selected_fields, true),
				'value' => $value,
			);
		}, $this->getFormModel()->getAvailableAbandonedCartFields());

		// Collect abandoned cart automation options.
		$automation_automations = array();

		if ($this->getConfigModel()->get('validated') === true) {
			try {
				$automation_automations = (new SmailyForOpenCart\Request)
					->setSubdomain($this->getConfigModel()->get('api_subdomain'))
					->setCredentials(
						$this->getConfigModel()->get('api_username'),
						$this->getConfigModel()->get('api_password')
					)
					->get('workflows', array('trigger_type' => 'form_submitted'));

			} catch (SmailyForOpenCart\HTTPError $error) {
				$this->log->write($error);
			}
		}

		$selected_automation = $this->getConfigModel()->get('abandoned_cart_autoresponder');
		$data['automation_options'] = array_map(function($value) use ($selected_automation) {
			return array(
				'label' => $value['title'],
				'selected' => $value['id'] === $selected_automation,
				'value' => $value['id'],
			);
		}, $automation_automations);

		// Compile abandoned cart CRON URL.
		$data['cron_url'] = $this->getCompatModel()->linkToCatalog(
			$this,
			'extension/smaily_for_opencart/cron/abandoned_cart',
			array('cron_token' => $this->getConfigModel()->get('abandoned_cart_token'))
		);

		// Compile CRON token reset URL.
		$data['reset_token_url'] = $this->getCompatModel()
			->linkWithUserToken($this, $this->getCompatModel()->getRouteToMain() . '/resetAbandonedCartCronToken');

		return $data;
	}

	/**
	 * Compile variables for RSS settings section.
	 *
	 * @return array
	 */
	protected function compileRssVariables() {
		$data = array();

		$this->load->model('catalog/category');
		$selected_category = $this->getConfigModel()->get('rss_category');
		$data['categories'] = array_map(function($value) use ($selected_category) {
			return array(
				'label' => $value['name'],
				'selected' => (int)$value['category_id'] === $selected_category,
				'value' => $value['category_id'],
			);
		}, $this->model_catalog_category->getCategories());

		// Collect RSS feed sort options.
		$selected_sort_by = $this->getConfigModel()->get('rss_sort_by');
		$data['sort_by_options'] = array_map(function($value) use ($selected_sort_by) {
			return array(
				'label' => $this->language->get('rss_sort_option_' . str_replace('.', '_', $value)),
				'selected' => $value === $selected_sort_by,
				'value' => $value,
			);
		}, $this->getFormModel()->getAvailableRssSortFields());

		// Collect RSS feed sort order options.
		$selected_sort_order = $this->getConfigModel()->get('rss_sort_order');
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
		$data['feed_url'] = $this->getCompatModel()->linkToCatalog(
			$this,
			'extension/smaily_for_opencart/rss',
			array(
				'category' => $this->getConfigModel()->get('rss_category'),
				'limit' => $this->getConfigModel()->get('rss_limit'),
				'sort_by' => $this->getConfigModel()->get('rss_sort_by'),
				'sort_order' => $this->getConfigModel()->get('rss_sort_order'),
			)
		);
		$data['feed_base_url'] = $this->getCompatModel()->linkToCatalog($this, 'extension/smaily_for_opencart/rss');

		return $data;
	}

	/**
	 * Compile variables for Abandoned Cart status table section.
	 *
	 * @return array
	 */
	protected function compileAbandonedCartsTableVariables() {
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
		$data['collection'] = $this->getAdminModel()->listAbandonedCarts(
			$sort_by,
			$sort_order,
			($current_page - 1) * $limit,
			$limit,
			$this->getConfigModel()->get('abandoned_cart_delay'),
			$this->getConfigModel()->get('abandoned_cart_enabled_at')
		);

		// Compile Abandoned Cart status table pagination.
		$total_count = $this->getAdminModel()->countAbandonedCarts(
			$this->getConfigModel()->get('abandoned_cart_delay'),
			$this->getConfigModel()->get('abandoned_cart_enabled_at')
		);

		$pagination = new Pagination();
		$pagination->total = $total_count;
		$pagination->page = $current_page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->getCompatModel()->linkWithUserToken(
			$this,
			$this->getCompatModel()->getRouteToMain(),
			array('page' => '{page}')
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
			'sort_by' => $sort_by,
			'sort_order' => isset($this->request->get['sort_order']) && $this->request->get['sort_order'] === 'asc'
				? 'desc'
				: 'asc',
			'page' => $current_page,
		);

		$data['sort_name_url'] = $this->getCompatModel()->linkWithUserToken(
			$this,
			$this->getCompatModel()->getRouteToMain(),
			array_merge($url_parameters, array('sort_by' => 'lastname'))
		) . '#abandoned-carts';

		$data['sort_email_url'] = $this->getCompatModel()->linkWithUserToken(
			$this,
			$this->getCompatModel()->getRouteToMain(),
			array_merge($url_parameters, array('sort_by' => 'email'))
		) . '#abandoned-carts';

		$data['sort_date_url'] = $this->getCompatModel()->linkWithUserToken(
			$this,
			$this->getCompatModel()->getRouteToMain(),
			array_merge($url_parameters, array('sort_by' => 'sent_time'))
		) . '#abandoned-carts';

		$data['sort_status_url'] = $this->getCompatModel()->linkWithUserToken(
			$this,
			$this->getCompatModel()->getRouteToMain(),
			array_merge($url_parameters, array('sort_by' => 'is_sent'))
		) . '#abandoned-carts';

		$catalog_url = new Url((bool)(int)$this->config->get('config_secure'));
		$data['product_base_url'] = $catalog_url->link('product/product', array('product_id' => ''), true);

		return $data;
	}

	/**
	 * Initialize and return instance of admin model.
	 *
	 * @return ModelExtensionSmailyForOpencartAdmin
	 */
	private function getAdminModel() {
		$this->load->model('extension/smaily_for_opencart/admin');
		return $this->model_extension_smaily_for_opencart_admin;
	}

	/**
	 * Initialize and return instance of compatibility model.
	 *
	 * @return ModelExtensionSmailyForOpencartCompat
	 */
	private function getCompatModel() {
		$this->load->model('extension/smaily_for_opencart/compat');
		return $this->model_extension_smaily_for_opencart_compat;
	}

	/**
	 * Initialize and return cached instance of configuration model.
	 *
	 * @return ModelExtensionSmailyForOpencartConfig
	 */
	private function getConfigModel() {
		static $config_model;

		if ($config_model === null) {
			$this->load->model('extension/smaily_for_opencart/config');
			$config_model = $this->model_extension_smaily_for_opencart_config->initialize();
		}

		return $config_model;
	}

	/**
	 * Initialize and return instance of form model.
	 *
	 * @return ModelExtensionSmailyForOpencartForm
	 */
	private function getFormModel() {
		$this->load->model('extension/smaily_for_opencart/form');
		return $this->model_extension_smaily_for_opencart_form;
	}
}
