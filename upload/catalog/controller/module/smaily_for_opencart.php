<?php

/**
 * Front end newsletter widget.
 */
class ControllerModuleSmailyForOpencart extends Controller {

    public function index() {
        // Load Smaily settings.
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('smaily_for_opencart');
        if (array_key_exists('smaily_for_opencart_status', $settings) &&
            (int) $settings['smaily_for_opencart_status'] === 1) {
            // Get language template.
            $this->load->language('module/smaily_for_opencart');
            // Form element translations.
            $data['newsletter_title'] = $this->language->get('newsletter_title');
            $data['subscribe_button'] = $this->language->get('subscribe_button');
            $data['email_placeholder'] = $this->language->get('email_placeholder');
            $data['name_placeholder'] = $this->language->get('name_placeholder');

            // Form settings.
            $data['subdomain'] = $settings['smaily_for_opencart_subdomain'];
            // Get current url.
            $request_scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $url = $request_scheme .'://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ;
            $data['current_url'] =  $url;
            // Get smaily response from url.
            $response_code = isset($this->request->get['code']) ? (int) $this->request->get['code'] : null;
            if ($response_code && $response_code === 101) {
                $data['success_message'] = $this->language->get('newsletter_success_response');
            }
            if ($response_code && $response_code !== 101) {
                $data['error_message'] = $this->language->get('newsletter_error_response');
            }
            // Add template
            return $this->load->view('module/smaily_for_opencart', $data);
        }
    }
}
