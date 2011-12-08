<?php
	
	include_once 'lib/adodb5/adodb.inc.php';
	include_once 'lib/adodb5/adodb-pear.inc.php';
    include_once 'lib/fitzgerald.php';

	class MyFitzgerald extends fitzgerald{
		var $db = null;
        
		public function __construct( $options=array() ){
            $this->options = new ArrayWrapper($options);
			if( isset($options['dao']) && count($options['dao']) ){
				$db_list = array();
				$conn = ADONewConnection('mysqli');
				$conn->PConnect(DATABASE_HOST, DATABASE_ID, DATABASE_PASS, DATABASE_NAME);
				$conn->SetCharSet( DATABASE_CHARSET );
				if( DATABASE_DEBUG ){
					$conn->debug = 1;
				}
			
				foreach( $options['dao'] as $db ){
					$class_name = 'dao_'.$db;
					$db_list[$db] = new $class_name( $conn );
				}
				unset($options['dao']);
				$this->db = new ArrayWrapper( $db_list );
			}
			parent::__construct( $options );
		}

		protected function render($fileName, $variableArray=array(), $useHeplers=array() ) {
			$heplerList = array( 'search' => 'SearchHelper', 'html' => 'HtmlHelper', 'util' => 'myUtilHelper' );
			$useHeplers[]='util';
			foreach( $useHeplers as $useHepler ){
				if( !isset( $variableArray[$useHepler] ) && isset($heplerList[$useHepler]) ){
					$class = $heplerList[ $useHepler ];
					$variableArray[ $useHepler ] = new $class();
				}
			}
			$variableArray['uri_base'] = 'http://'.$_SERVER['SERVER_NAME'].$this->options->mountPoint.'/';
			$variableArray['mount_point'] = $this->options->mountPoint;

			return parent::render($fileName,$variableArray);
		}

        protected function sendJson($object=array(),$status=true){
            $this->options->layout=null;
            $json='""';
            header("Content-type: application/json; charset=utf-8");
            if( is_array( $object ) && count( $object ) ){
                $json = json_encode( $object );
            }   
            return $this->render('json',array('status'=>$status,'param'=>$json));
            //header("Content-type: application/x-javascript; charset=utf-8");
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

	class USerHelper{
		public function isLogin() {
			return !is_null($this->session->user);
		}
	}
