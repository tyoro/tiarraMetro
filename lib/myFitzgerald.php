<?php
	
	include_once 'lib/adodb5/adodb.inc.php';
	include_once 'lib/adodb5/adodb-pear.inc.php';
	include_once 'lib/fitzgerald.php';

	class MyFitzgerald extends fitzgerald{
		var $db = null;

		var $settings = null;
	
		public function __construct( $options=array() ){
			$this->options = new ArrayWrapper($options);

			$db_settings = $this->prepareDatabase($options);

			$settings = array(
				'is_ssl'   => (empty($_SERVER['HTTPS']) === false && ($_SERVER['HTTPS'] !== 'off')),
				'database' => $db_settings
			);

			$this->settings = new ArrayWrapper($settings);

			parent::__construct( $options );
		}

		protected function prepareDatabase(&$options){
			$settings = array();

			if( isset($options['dao']) && count($options['dao']) ){
				$db_objects = array();

				$conn = ADONewConnection('mysqli');
				$conn->PConnect($options[ 'DATABASE_HOST'] , $options['DATABASE_ID'], $options['DATABASE_PASS'], $options['DATABASE_NAME']);
				$conn->SetCharSet( DATABASE_CHARSET );

				if( DATABASE_DEBUG ){
					$conn->debug = 1;
				}

				// DAO でやるべき？
				$tables = $conn->getArray($conn->Prepare('SHOW TABLES;'));

				foreach ($tables as $table) {
					if (empty($settings[$table[0]]) || !is_array($settings[$table[0]])) {
						$settings[$table[0]] = array();
					}

					$columns = $conn->getArray($conn->Prepare(sprintf('DESCRIBE %s;', $table[0])));

					foreach ($columns as $column) {
						$settings[$table[0]][] = $column[0];
					}
				}

				foreach( $options['dao'] as $table ){
					$class_name = 'dao_'.$table;
					$db_objects[$table] = new $class_name( $conn, $settings );
				}

				unset($options['dao']);

				$this->db = new ArrayWrapper( $db_objects );
			}

			return $settings;
		}

		protected function render($fileName, $variableArray=array(), $useHeplers=array() ) {
			$heplerList = array( 'search' => 'SearchHelper', 'html' => 'HtmlHelper', 'util' => 'myUtilHelper', 'user' => 'UserHelper');
			$useHeplers[] = 'util';
			$useHeplers[] = 'user';

			foreach( $useHeplers as $useHepler ){
				if( !isset( $variableArray[$useHepler] ) && isset($heplerList[$useHepler]) ){
					$class = $heplerList[ $useHepler ];
					$variableArray[ $useHepler ] = new $class($this->options);
				}
			}

			$variableArray['uri_base'] = $this->getURIBase();
			$variableArray['mount_point'] = $this->options->mountPoint;
			$variableArray['settings'] = $this->settings;

			return parent::render($fileName,$variableArray);
		}

		protected function getURIBase() {
			$protocol = $this->settings->is_ssl ? 'https:' : 'http:' ;
			$host = $_SERVER['HTTP_HOST'];

			return sprintf('%s//%s%s/', $protocol, $host, $this->options->mountPoint);
		}

		protected function sendJson($object=array(),$status=true){
			$this->options->layout=null;

			$json='""';

			//header("Content-type: application/x-javascript; charset=utf-8");
			header("Content-type: application/json; charset=utf-8");

			if( is_array( $object ) && count( $object ) ){
				$json = json_encode( $object );
			}   

			return $this->render('json',array('status'=>$status,'param'=>$json));
		}  
		
		public function post_and_get($url, $methodName, $conditions=array()){
			$this->post($url,$methodName,$conditions);
			$this->get($url,$methodName,$conditions);
		}
	}

	class SearchHelper{
		public function sortLink($key,$str){
			$query = $_GET;
			if( isset($query['sort']) && $query['sort'] == $key && isset($_GET['order']) ){
				$query['order'] = ( $_GET['order'] == 'asc' )?'desc':'asc';
			}else{
				$query['order'] = 'asc';
			}
			$query['sort'] = $key;
			if( isset( $_GET['page'] ) ){
				unset( $query['page'] );
			}
			print '<a href="'.$_SERVER['REDIRECT_URL'].'?'.http_build_query($query).'">'.$str.'</a>';
		}
	}

	class HtmlHelper{
		public function form($type){
		}
	}

	class UserHelper{
		public function isLogin() {
			return !is_null($this->session->user);
		}

		public function flashMessage ($key) {
			global $_SESSION;

			$message = '';

			if (isset($_SESSION[$key])) {
				$result = $_SESSION[$key];
			}

			unset($_SESSION[$key]);

			return $result;
		}

		public function flashEnabled ($key) {
			global $_SESSION;

			if (!empty($_SESSION[$key]) && is_string($_SESSION[$key])) {
				return true;
			}

			return false;
		}
	}

