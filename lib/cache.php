<?php
	include_once '../lib/shortenUrl.php';

	class cacheUrl {
		public $is_enabled = false;
		public $link = false;
		public $query = '';
		public $error = '';
		public $result = false;
		public $database = '../data/cache.sqlitedb';

		public $tables = array(
			'url' => 'id INTEGER AUTOINCREMENT, url TEXT NOT NULL, shorten TEXT, created DATETIME DEFAULT CURRENT_TIMESTAMP, updated DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id)',
			// t.co や htn.to などはurlとshortenが対でないのでこれはダメ: 'UNIQUE(url)'
			'icon' => 'id INTEGER AUTOINCREMENT, twitter_id TEXT NOT NULL, url TEXT, data TEXT, created DATETIME DEFAULT CURRENT_TIMESTAMP, updated DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id), UNIQUE(twitter_id)',
		);

		function __construct () {
			if(extension_loaded('sqlite')) {
				$this->link = sqlite_open($this->database, 0666, $this->error);

				if ($this->link) {
					if ($this->checkTables()) {
						$this->is_enabled = true;
					} else {
						$this->is_enabled = false;
						if ($this->createTables()) {
							$this->is_enabled = true;
						}
					}

					if (!$this->is_enabled) {
						$sqlite_close($this->link);
					}
				} else {
					$this->is_enabled = false;
				}
			} else {
				$this->is_enabled = false;
			}
		}
		function __destruct () {
			if ($this->is_enabled) {
				sqlite_close($this->link);
			}
		}

		function checkTables () {
			if ($this->link) {
				$this->query = "SELECT name FROM sqlite_master WHERE type='table' UNION ALL SELECT name FROM sqlite_temp_master WHERE type='table' ORDER BY name";
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						while (($data = sqlite_fetch_array($this->result))) {
							if (array_key_exists($data['name'], $this->tables) === false) {
								return false;
							}
						}
						// TODO: $this->tables[] にあって $data['name'] になかったもののチェック
						return true;
					}
				}
			}
			return false;
		}
		function createTables () {
			if ($this->link) {
				$this->query = array();
				foreach ($this->tables as $key => $value) {
					array_push($this->query, "CREATE TABLE $key ($value);");
				}
				foreach ($this->query as $query) {
					$this->result = sqlite_query($this->link, $query, SQLITE_BOTH, $this->error);
					if (!$this->result) {
						return false;
					}
				}
				return true;
			}

			return false;
		}
/*
		function deleteTable ($name = '') {
			if (empty($name)) {
				return false;
			}
			return true;
		}
*/

		function is_exist_url ($url) {
			if (!empty($url) && $this->is_enabled && $this->link) {
				$this->query = sprintf("SELECT url FROM url WHERE url='%s'", sqlite_escape_string($url));
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						return true;
					}
				}
			}
			return false;
		}
		function is_exist_shorten ($shorten) {
			if (!empty($shorten) && $this->is_enabled && $this->link) {
				$this->query = sprintf("SELECT shorten FROM url WHERE shorten='%s'", sqlite_escape_string($shorten));
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						return true;
					}
				}
			}
			return false;
		}
		function is_exist_twitter_id ($twitter_id) {
			if (!empty($twitter_id) && $this->is_enabled && $this->link) {
				$this->query = sprintf("SELECT twitter_id FROM icon WHERE twitter_id='%s'", sqlite_escape_string($twitter_id));
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						return true;
					}
				}
			}
			return false;
		}
		function is_exist_icon ($twitter_id) {
			if (!empty($twitter_id) && $this->is_enabled && $this->link) {
				$this->query = sprintf("SELECT url FROM icon WHERE twitter_id='%s'", sqlite_escape_string($twitter_id));
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						$row = sqlite_fetch_array($this->result, SQLITE_ASSOC);
						if (empty($row['url'])) {
							return false;
						}
						return true;
					}
				}
			}
			return false;
		}
		function is_exist_icon_data ($twitter_id) {
			if (!empty($twitter_id) && $this->is_enabled && $this->link) {
				$this->query = sprintf("SELECT data FROM icon WHERE twitter_id='%s'", sqlite_escape_string($twitter_id));
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						$row = sqlite_fetch_array($this->result, SQLITE_ASSOC);
						if (empty($row['data'])) {
							return false;
						}
						return true;
					}
				}
			}
			return false;
		}

		function get_shorten ($url) {
			$uri = '';
			if (!empty($url) && $this->is_enabled && $this->link) {
				$this->query = sprintf("SELECT shorten FROM url WHERE url='%s'", sqlite_escape_string($url));
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						$row = sqlite_fetch_array($this->result, SQLITE_ASSOC);
						if (!empty($row['shorten'])) {
							$uri = trim($row['shorten']);
						}
					}
				}
	
				if ($uri == '') {
					$short_url = new shortenUrl();
					$uri = $short_url->shorten($url);
					if (!empty($uri)) {
						$this->add_url($url, $uri);
					}
				}
			}
			return (trim($uri)=='' ? trim($url):trim($uri));
		}
		function get_url ($shorten) {
			$uri = '';
			if (!empty($shorten) && $this->is_enabled && $this->link) {
				$this->query = sprintf("SELECT url FROM url WHERE shorten='%s'", sqlite_escape_string($shorten));
				$this->result = sqlite_query($this->link, $this->query, SQLITE_BOTH, $this->error);
				if ($this->result) {
					if (sqlite_num_rows($this->result) > 0) {
						$row = sqlite_fetch_array($this->result, SQLITE_ASSOC);
						if (!empty($row['url'])) {
							$uri = trim($row['url']);
						}
					}
				}

				if ($uri == '') {
					$short_url = new shortenUrl();
					$uri = $short_url->expand($shorten);
					if (!empty($uri)) {
						$this->add_url($uri, $shorten);
					}
				}
			}
			return (trim($uri)=='' ? trim($shorten):trim($uri));
		}
		function get_icon ($twitter_id) {
		}
		function get_icon_url ($twitter_id) {
		}
		function get_icon_data ($twitter_id) {
		}

		function add_url ($url, $shorten) {
			if (!empty($url) && !empty($shorten) && (trim($url) != trim($shorten)) && $this->is_enabled && $this->link) {
				$this->query = sprintf("INSERT INTO url(url, shorten) VALUES('%s', '%s')", sqlite_escape_string($url), sqlite_escape_string($shorten));
				$result = sqlite_exec($this->link, $this->query, $this->error);
				if ($this->result) {
					if (sqlite_changes($this->link) > 0) {
						return true;
					}
				}
			}
			return false;
		}
		function add_icon ($twitter_id, $url, $data) {
			if (!empty($twitter_id) && !empty($url) && $this->is_enabled && $this->link) {
				$this->query = sprintf("INSERT INTO icon(twitter_id, url, data) VALUES('%s', '%s', '%s')", sqlite_escape_string($twitter_id), sqlite_escape_string($url), sqlite_escape_string($data));
				$result = sqlite_exec($this->link, $this->query, $this->error);
				if ($this->result) {
					if (sqlite_changes($this->link) > 0) {
						return true;
					}
				}
			}
			return false;
		}

		function update_icon_url_data ($twitter_id, $url, $data) {
			if (!empty($twitter_id) && !empty($url) && $this->is_enabled && $this->link) {
				$this->query = sprintf("UPDATE icon SET url='%s', data='%s' WHERE twitter_id'%s'", sqlite_escape_string($url), sqlite_escape_string($data), sqlite_escape_string($twitter_id));
				$result = sqlite_exec($this->link, $this->query, $this->error);
				if ($this->result) {
					if (sqlite_changes($this->link) > 0) {
						return true;
					}
				}
			}
			return false;
		}
	}

