<?php
	define('HTTP_STATUS_OK', 200);
	
	class shortenUrl {
		public $is_enabled = false;
		public $api_key = '';
		public $uri = '';
		public $request = '';
		public $response = '';
		public $data = '';
		public $json = '';

		function __construct() {
			if (file_exists_ex('HTTP/Request2.php')) {
				include_once 'HTTP/Request2.php';
				$this->is_enabled = true;
			}
			$this->api_key = empty($jsConf['shorten_url_api_key']) ? 'AIzaSyBVLynl28hXhPoaH2Uk5l7Se8oK9batcyY':trim($jsConf['shorten_url_api_key']);
		}
		function __destruct() {
		}
	
		public function shorten ($url='') {
			if ($this->is_enabled) {
				$this->uri = parse_url($url);
				if ($this->uri['scheme'] == 'http' || $this->uri['scheme'] == 'https') {
					if ($this->uri['host'] != 'goo.gl') {
						$this->uri = 'https://www.googleapis.com/urlshortener/v1/url?key=' . $this->api_key;
	
						$this->data = json_encode(array('longUrl' => $url));
						$this->request = new HTTP_Request2(null);
						$this->request->setUrl($this->uri);
						$this->request->setConfig('ssl_verify_peer', false);
						$this->request->setMethod(HTTP_Request2::METHOD_POST);
						$this->request->setHeader('Content-Type', 'application/json');
						$this->request->setBody($this->data);
						$this->response = $this->request->send();

						if ($this->response->getStatus() == HTTP_STATUS_OK) {	
							$this->json = json_decode($this->response->getBody());
						}
					}

					if (trim($this->json->id) != '') {
						return $this->json->id;
					}
				}
			}
			return $url;
		}
		public function expand ($url='') {
			if ($this->is_enabled) {
				$this->uri = parse_url($url);
				if ($this->uri['scheme'] == 'http' || $this->uri['scheme'] == 'https') {
					if ($this->uri['host'] == 'goo.gl') {
						$this->uri = 'https://www.googleapis.com/urlshortener/v1/url?shortUrl=' . $url . '&key=' . $this->api_key;
						$this->request = new HTTP_Request2(null);
						$this->request->setUrl($this->uri);
						$this->request->setConfig('ssl_verify_peer', false);
						$this->request->setMethod(HTTP_Request2::METHOD_GET);
						$this->reponse = $this->request->send();

						if ($this->response->getStatus() == HTTP_STATUS_OK) {
							$this->json = json_decode($this->response->getBody());
						}
					}
	
					if (trim($this->json->longUrl) != '') {
						return $this->json->longUrl;
					}
				}
			}
			return $url;
		}
	}
?>

