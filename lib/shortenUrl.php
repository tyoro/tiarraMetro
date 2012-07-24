<?php
	define('HTTP_STATUS_OK', 200);
	
	class shortenUrl {
		public $is_enabled = false;
		public $api_key = '';
		public $uri = '';
		public $request = '';
		public $response = '';
		public $header = '';
		public $body = '';
		public $data = '';
		public $json = '';
		public $shorten = array(
			'goo.gl',
			'bit.ly',
			'j.mp',
			'ow.ly',
			't.co',
			'fb.me',
			'tmblr.co',
			'youtu.be',
			'ustre.am',
			'nico.ms',
			'htn.to',
		);

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
				try {
					$this->uri = parse_url($url);
					if ($this->uri['scheme'] == 'http' || $this->uri['scheme'] == 'https') {
						if (!in_array($this->uri['host'], $this->shorten)) {
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

						# if (trim($this->json->id) != '') {
							return empty($this->json->id)?$url:$this->json->id;
						# }
					}
				} catch (Exception $e) {
					// 
				}
			}
			return $url;
		}
		public function expand ($url='') {
			if ($this->is_enabled) {
				try {
					$this->uri = parse_url($url);
					if ($this->uri['scheme'] == 'http' || $this->uri['scheme'] == 'https') {
						if (in_array($this->uri['host'], $this->shorten)) {
							if ($this->uri['host'] == 'goo.gl') {
								$this->uri = 'https://www.googleapis.com/urlshortener/v1/url?shortUrl=' . $url . '&key=' . $this->api_key;
								$this->request = new HTTP_Request2(null);
								$this->request->setUrl($this->uri);
								$this->request->setConfig('ssl_verify_peer', false);
								$this->request->setMethod(HTTP_Request2::METHOD_GET);
								$this->response = $this->request->send();

								if ($this->response->getStatus() == HTTP_STATUS_OK) {
									$this->json = json_decode($this->response->getBody());
								}
	
								# if (trim($this->json->longUrl) != '') {
									return empty($this->json->longUrl)?$url:$this->json->longUrl;
								# }
							} else {
								$this->request = new HTTP_Request2(null);
								$this->request->setUrl($url);
								$this->request->setConfig('ssl_verify_peer', false);
								$this->request->setMethod(HTTP_Request2::METHOD_HEAD);
								$this->response = $this->request->send();

								if ($this->response->getStatus() == 301 || $this->response->getStatus() == 302) {
									$this->header = $this->response->getHeader();
								}

								return empty($this->header['location'])?$url:$this->header['location'];
							}
						}
					}
				} catch (Exception $e) {
					//
				}
			}
			return $url;
		}
	}
?>

