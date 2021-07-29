<?php

class ControllerExtensionModuleSmailyForOpencart extends Controller {
	public function index($setting) {
		$this->load->model('extension/smaily_for_opencart/config');
		$config_model = $this->model_extension_smaily_for_opencart_config->initialize();

		// Output nothing if module is disabled.
		if ($config_model->get('status') === false) {
			return;
		}

		// Compile current URL.
		$scheme = isset($this->request->server['HTTPS']) && $this->request->server['HTTPS'] === 'on' ? "https" : "http";
		$current_url = $scheme . '://' . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];

		// Collect template variables.
		$data = array(
			'current_url' => $current_url,
			'language' => $this->session->data['language'],
			'subdomain' => $config_model->get('api_subdomain'),
			't' => $this->load->language('extension/module/smaily_for_opencart'),
		);

		// Get smaily response from url.
		$response_code = isset($this->request->get['code']) ? (int)$this->request->get['code'] : null;
		if ($response_code && $response_code === 101) {
			$data['success_message'] = $this->language->get('newsletter_success_response');
		} elseif ($response_code && $response_code !== 101) {
			$data['error_message'] = $this->language->get('newsletter_error_response');
		}

		return $this->load->view('extension/module/smaily_for_opencart', $data);
	}
}
