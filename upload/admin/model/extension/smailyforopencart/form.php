<?php

require_once DIR_SYSTEM . 'library/smailyforopencart/request.php';

class ModelExtensionSmailyForOpencartForm extends Model {
	// Note! Order of fields is important.
	protected $abandoned_cart_fields = array(
		'first_name',
		'last_name',
		'base_price',
		'description',
		'name',
		'price',
		'quantity',
		'sku',
	);

		// Note! Order of fields is important.
	protected $customer_sync_fields = array(
		'date_added',
		'firstname',
		'lastname',
		'telephone',
	);

	// Note! Order of fields is important.
	protected $rss_sort_fields = array(
		'pd.name',
		'p.model',
		'p.price',
		'p.sort_order',
		'p.status',
	);

	public function getAvailableCustomerSyncFields() {
		return $this->customer_sync_fields;
	}

	public function getAvailableAbandonedCartFields() {
		return $this->abandoned_cart_fields;
	}

	public function getAvailableRssSortFields() {
		return $this->rss_sort_fields;
	}

	public function sanitize($input) {
		$sanitized = array();

		// Sanitize module settings.
		$sanitized['status'] = isset($input['status']) ? (bool)(int)$input['status'] : null;

		// Sanitize API credentials.
		$sanitized['api_subdomain'] = isset($input['api_subdomain']) ? trim($input['api_subdomain']) : null;

		if ($sanitized['api_subdomain'] !== null) {
			// First, try to parse as full URL. If that fails, try to parse as subdomain.sendsmaily.net.
			// As a last resort clean up subdomain and pass it as is.
			if (filter_var($sanitized['api_subdomain'], FILTER_VALIDATE_URL)) {
				$url = parse_url($sanitized['api_subdomain']);
				$parts = explode('.', $url['host']);
				$sanitized['api_subdomain'] = (count($parts) >= 3) ? $parts[0] : '';
			} elseif (preg_match('/^[^\.]+\.sendsmaily\.net$/', $sanitized['api_subdomain'])) {
				$parts = explode('.', $sanitized['api_subdomain']);
				$sanitized['api_subdomain'] = $parts[0];
			}
			$sanitized['api_subdomain'] = preg_replace('/[^a-zA-Z0-9]+/', '', $sanitized['api_subdomain']);
		}

		$sanitized['api_username'] = isset($input['api_username']) ? trim($input['api_username']) : null;
		$sanitized['api_password'] = isset($input['api_password']) ? trim($input['api_password']) : null;

		// Sanitize customer synchronization settings.
		$sanitized['customer_sync_enabled'] = isset($input['customer_sync_enabled'])
			? (bool)(int)$input['customer_sync_enabled']
			: null;

		$sanitized['customer_sync_fields'] = (isset($input['customer_sync_fields']) and is_array($input['customer_sync_fields']))
			? array_values(array_intersect($this->getAvailableCustomerSyncFields(), $input['customer_sync_fields']))
			: null;

		$sanitized['customer_sync_token'] = isset($input['customer_sync_token'])
			? trim($input['customer_sync_token'])
			: null;

		// Sanitize abandoned cart settings.
		$sanitized['abandoned_cart_enabled'] = isset($input['abandoned_cart_enabled'])
			? (bool)(int)$input['abandoned_cart_enabled']
			: null;

		$sanitized['abandoned_cart_autoresponder'] = isset($input['abandoned_cart_autoresponder'])
			? (int)$input['abandoned_cart_autoresponder']
			: null;

		$sanitized['abandoned_cart_delay'] = isset($input['abandoned_cart_delay'])
			? (int)$input['abandoned_cart_delay']
			: null;

		$sanitized['abandoned_cart_fields'] = (isset($input['abandoned_cart_fields']) and is_array($input['abandoned_cart_fields']))
			? array_values(array_intersect($this->getAvailableAbandonedCartFields(), $input['abandoned_cart_fields']))
			: null;

		$sanitized['abandoned_cart_token'] = isset($input['abandoned_cart_token'])
			? trim($input['abandoned_cart_token'])
			: null;

		// Sanitize RSS settings.
		$sanitized['rss_category'] = isset($input['rss_category'])
			? trim($input['rss_category'])
			: null;

		$sanitized['rss_sort_by'] = (isset($input['rss_sort_by']) and in_array($input['rss_sort_by'], $this->getAvailableRssSortFields(), true))
			? $input['rss_sort_by']
			: null;

		$sanitized['rss_sort_order'] = (isset($input['rss_sort_order']) and in_array($input['rss_sort_order'], array('asc', 'desc'), true))
			? $input['rss_sort_order']
			: null;

		$sanitized['rss_limit'] = isset($input['rss_limit']) ? (int)$input['rss_limit'] : null;

		return $sanitized;
	}

	public function validate($sanitized) {
		$errors = array();

		$this->load->language('extension/module/smaily_for_opencart');

		// Ensure API subdomain is not empty.
		if (empty($sanitized['api_subdomain'])) {
			$errors['api_subdomain'] = $this->language->get('error_api_subdomain_empty');
		}

		// Ensure API username is not empty.
		if (empty($sanitized['api_username'])) {
			$errors['api_username'] = $this->language->get('error_api_username_empty');
		}

		// Ensure API password is not empty.
		if (empty($sanitized['api_password'])) {
			$errors['api_password'] = $this->language->get('error_api_password_empty');
		}

		// Ensure API credentials are actually working.
		if (
			!empty($sanitized['api_subdomain']) and
			!empty($sanitized['api_username']) and
			!empty($sanitized['api_password'])
		) {
			try {
				(new SmailyForOpenCart\Request)
					->setSubdomain($sanitized['api_subdomain'])
					->setCredentials($sanitized['api_username'], $sanitized['api_password'])
					->get('workflows', array('trigger_type' => 'form_submitted'));
			} catch (SmailyForOpenCart\HTTPError $error) {
				switch ($error->getCode()) {
					case SmailyForOpenCart\Request::HTTP_ERR_UNAUTHORIZED:
						$errors['validated'] = $this->language->get('error_api_unauthorized');
						break;

					case SmailyForOpenCart\Request::HTTP_ERR_INVALID_SUBDOMAIN:
						$errors['validated'] = $this->language->get('error_api_notfound');
						break;

					default:
						$errors['validated'] = $this->language->get('error_api_unknown');
				}
			}
		}

		// Validate abandoned cart settings.
		if ($sanitized['abandoned_cart_enabled'] === true) {
			// Ensure abandoned cart automation is selected.
			if (empty($sanitized['abandoned_cart_autoresponder'])) {
				$errors['abandoned_cart_autoresponder'] = $this->language->get('error_abandoned_cart_autoresponder_not_selected');
			}

			// Ensure abandoned cart delay time is valid.
			if ($sanitized['abandoned_cart_delay'] < 15) {
				$errors['abandoned_cart_delay'] = $this->language->get('error_abandoned_cart_delay_minimum');
			}
		}

		// Ensure RSS limit is between 1 and 250.
		if ($sanitized['rss_limit'] < 1 || $sanitized['rss_limit'] > 250) {
			$errors['rss_limit'] = $this->language->get('error_rss_limit_exceeded');
		}

		return $errors;
	}
}
