<?php

class ModelExtensionSmailyForOpencartCompat extends Model {
	/**
	 * Return link to catalog controller.
	 *
	 * @param mixed $context
	 * @param string $route
	 * @param array $args
	 * @param boolean $secure
	 * @return string
	 */
	public function linkToCatalog($context, $route, array $args = array(), $secure = true) {
		if ($this->isVersionTwoTwo()) {
			$url = new Url((bool)(int)$context->config->get('config_secure'));
			$compiled_url = $url->link($route, $args, $secure);

			if (stripos($compiled_url, $secure === true ? HTTPS_SERVER : HTTP_SERVER) === 0) {
				$compiled_url = substr($compiled_url, strlen($secure === true ? HTTPS_SERVER : HTTP_SERVER));
				$compiled_url = $context->config->get('config_url') . $compiled_url;
			}

			return $compiled_url;
		}

		$url = new Url(HTTP_CATALOG, HTTPS_CATALOG);
		return $url->link($route, $args, $secure);
	}

	/**
	 * Return URL with user token.
	 *
	 * @param mixed $context
	 * @param string $route
	 * @param array $args
	 * @param boolean $secure
	 * @return string
	 */
	public function linkWithUserToken($context, $route, array $args = array(), $secure = true) {
		$token_name = 'user_token';
		if ($this->isVersionTwoTwo() || $this->isVersionTwoThree()) {
			$token_name = 'token';
		}

		$args = array_merge($args, array(
			$token_name => $context->session->data[$token_name],
		));

		return $context->url->link($route, $args, $secure);
	}

	/**
	 * Return OpenCart version dependent route to modules.
	 *
	 * @return string
	 */
	public function getRouteToModules() {
		if ($this->isVersionTwoTwo()) {
			return 'extension/module';
		}

		return 'marketplace/extension';
	}

	/**
	 * Return OpenCart version dependent route to Smaily for OpenCart settings page.
	 *
	 * @return string
	 */
	public function getRouteToMain() {
		if ($this->isVersionTwoTwo()) {
			return 'module/smaily_for_opencart';
		}

		return 'extension/module/smaily_for_opencart';
	}

	/**
	 * Register OpenCart event handler.
	 *
	 * @param string $code
	 * @param string $trigger
	 * @param string $action
	 * @return int
	 */
	public function addEvent($code, $trigger, $action) {
		$sql = sprintf(
			"INSERT INTO `" . DB_PREFIX . "event` SET `code` = '%s', `trigger` = '%s', `action` = '%s'",
			$this->db->escape($code),
			$this->db->escape($trigger),
			$this->db->escape($action)
		);

		$this->db->query($sql);
		return $this->db->getLastId();
	}

	/**
	 * Unregister OpenCart event handler by code.
	 *
	 * @param string $code
	 * @return void
	 */
	public function deleteEvent($code) {
		$sql = "DELETE FROM `" . DB_PREFIX . "event` WHERE `code`= '" . $this->db->escape($code) . "'";
		$this->db->query($sql);
	}

	/**
	 * Create layout module.
	 *
	 * @param string $code
	 * @param array $data
	 */
	public function addModule($code, array $data) {
		$sql = sprintf(
			"INSERT INTO `" . DB_PREFIX . "module` SET `name` = '%s', `code` = '%s', `setting` = '%s'",
			$this->db->escape($data['name']),
			$this->db->escape($code),
			$this->db->escape(json_encode($data))
		);
		$this->db->query($sql);
	}

	/**
	 * Delete layout modules.
	 *
	 * @param string
	 */
	public function deleteModulesByCode($code) {
		$escaped_code = $this->db->escape($code);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $escaped_code . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_module` WHERE `code` LIKE '" . $escaped_code . "' OR `code` LIKE '" . $this->db->escape($code . '.%') . "'");
	}

	/**
	 * Check if current installed OpenCart version is from the 2.2.x line.
	 *
	 * @return boolean
	 */
	public function isVersionTwoTwo() {
		return strpos($this->getPlatformVersion(), '2.2.') === 0;
	}

	/**
	 * Check if current installed OpenCart version is from the 2.3.x line.
	 *
	 * @return boolean
	 */
	public function isVersionTwoThree() {
		return strpos($this->getPlatformVersion(), '2.3.') === 0;
	}

	/**
	 * Return normalized installed OpenCart version.
	 *
	 * @return string
	 */
	protected function getPlatformVersion() {
		$version = explode('.', VERSION);
		$version = array_pad($version, 4, '0');
		return implode('.', $version);
	}
}
