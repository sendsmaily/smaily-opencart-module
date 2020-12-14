<?php

namespace SmailyForOpenCart;

class Request {
    const HTTP_ERR_INVALID_SUBDOMAIN = 404;
    const HTTP_ERR_UNAUTHORIZED = 401;

    const API_ERR_INVALID_DATA = 203;
    const API_ERR_FORBIDDEN = 202;
    const API_ERR_WRONG_METHOD = 201;

    protected $url = null;

    protected $data = array();

    protected $subdomain = null;

    private $_username = null;

    private $_password = null;

    public function setSubdomain($subdomain) {
        $this->subdomain = trim($subdomain);
        $this->url = 'https://' . $this->subdomain . '.sendsmaily.net/api/';
        return $this;
    }

    public function setCredentials($username, $password) {
        $this->_username = trim($username);
        $this->_password = trim($password);
        return $this;
    }

    protected function compileUrl($endpoint) {
        return $this->url . trim($endpoint, '/') . '.php';
    }

    public function get($endpoint, $data = array()) {
        $url = $this->compileUrl($endpoint);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->_username}:{$this->_password}");

        $api_call = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code !== 200) {
            throw new HTTPError(curl_errno($ch), $http_code);
        }
        // Response from API call, e.g autoresponders.
        return json_decode($api_call, true);
    }

    public function post($endpoint, $data = array()) {
        $url = $this->compileUrl($endpoint);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->_username}:{$this->_password}");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $api_call = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code !== 200) {
            throw new HTTPError(curl_error($ch), $http_code);
        }

        // Validate Smaily gave us a response consisting of a code and message.
        if (!array_key_exists('code', $api_call)) {
            throw new HTTPError(curl_error($ch), $http_code);
        }
        if (isset($api_call['code']) && (int)$api_call['code'] !== 101) {
            throw new APIError($api_call['message'], $api_call['code']);
        }
        // Return Smaily API code and message.
        return $api_call;
    }

}

/**
 *
 */
class APIError extends \Exception {
    public function __construct($message, $code, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return "[SMAILY]: Error in POST method API call with code {$this->code}: {$this->message}\n";
    }
}

/**
 *
 */
class HTTPError extends \Exception {
    public function __construct($message, $code, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return "[SMAILY]: Error in HTTP call with code {$this->code}: {$this->message}\n";
    }
}
