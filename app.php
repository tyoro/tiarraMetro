<?php
	set_include_path(dirname(__FILE__).'/lib:'.get_include_path());
	set_include_path(dirname(__FILE__).'/conf:'.get_include_path());

	include_once 'conf/load.php';

	include_once 'dao.php';
	include_once 'lib/myFitzgerald.php';
	include_once 'lib/util.php';
	include_once 'lib/imageURLParser.php';

	include_once 'lib/Net/Socket/Tiarra.php';

	class TiarraWEB extends MyFitzgerald {
		public static $page_title = "tiarraMetro";
		public static $msg = '';
		public static $debug_msg = "";

		public function index_main( $pivot = 'default', $default_channel_id = -1 ){
			if( !$this->isLoggedIn() ){ 
				return $this->redirect('/login');
			}
			global $jsConf;

			$channel_list = array();
			$log_list = array();
			$default_channel = array( 'id'=>$default_channel_id );
			$max_id = 0;

			$max_id = $this->db->log->getMaxID();
			foreach( $this->db->channel->getUnreadList( '', $this->options->channel_list_sort ) as $ch ){
				$channel_list[$ch['id']] = $ch;
				$log_list[$ch['id']] = $this->logFilter( $this->db->log->getLog($ch['id']) );
				if( $default_channel_id == $ch['id'] ){ $default_channel[ 'name' ] = $ch['name']; }
			}
			switch( $jsConf[ 'template' ] ){
				case 'table':
				default:
					$template = 'index';
					break;
				case 'limechat':
					$template = 'index.limechat';
					break;
			}
			return $this->render( $template,
				array(
					'max_id' => $max_id,
					'channels' => $channel_list,
					'all_channels' => $this->db->channel->getList(),
					'logs' => $log_list,
					'pivot' => $pivot,
					'default_channel' => $default_channel,
					'jsConf' => $jsConf
					)
			);
		}

		public function index(){
			return $this->index_main( );
		}
		public function search_select( ){
			return $this->index_main( 'search' );
		}
		public function setting_select( ){
			return $this->index_main( 'setting' );
		}
		public function channel_select( $channel_id ){
			return $this->index_main( 'channel', $channel_id );
		}

		public function channel_name_select( $channel_name ){
			return $this->channel_select( $this->db->channel->getID( "#".$channel_name ) );
		}

		//api
		public function api_logs( ){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				$return = array( 'error'=> false, 'update'=>false,'checktime'=>date("Y-m-d H:i:s") );

				if( !empty($this->request->max_id) ){
					$logs = $this->getLogs($this->request->current,$this->request->max_id);
					if( count($logs) ){
						$return['update'] = true;
						$return['max_id'] = $logs[0]['id'];

						$ch_log = array();
						foreach( $logs as $log ){
							$ch_log[$log['channel_id']][ ] = array(
								'id' => $log['id'],
								'nick' => $log['nick'],
								'log' => $log['log'],
								'time' => $log['time'],
								'is_notice' => $log['is_notice']
							);
						}
						$return['logs'] = $ch_log;
					}
				}else{
					$return = array( 'error' => true, 'msg' => 'parameter not found.' );
				}
			}

			if( strlen(TiarraWEB::$debug_msg) )
			{
				$return['debug'] = TiarraWEB::$debug_msg;
				TiarraWEB::$debug_msg = '';
			}

			return json_encode($return);
		}
		public function api_post(){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else
			{
				if( !strlen($this->request->post) || !strlen($this->request->channel_id) ){
					$return = array( 'error' => true, 'msg' => 'parameter not found.' );
				}else{
					$name = $this->db->channel->getName( $this->request->channel_id );
					if( !$name ){
						$return = array( 'error' => true, 'msg' => 'channel not found.' );
					}else{
						try{
							$tiarra = new Net_Socket_Tiarra($this->options->tiarra_socket_name);
							if( $this->request->notice != 'true' ){
								$tiarra->message($name, $this->request->post);
							}else{
								$tiarra->noticeMessage($name, $this->request->post);
							}
							$return = array( 'error' => false );

							$ok = $this->db->log->postLog( $this->request->post, $this->request->channel_id, $this->db->nick->getID( $this->options->my_name ), $this->request->notice );
							if( $ok !== true ){
								$return = array( 'error' => true, 'msg' => $ok );
							}

						} catch (Net_Socket_Tiarra_Exception $e) {
							$return = array( 'error' => true, 'msg' => $e->getMessage() );
						}
					}
				}
			}
			return json_encode($return);
		}
		public function api_next( $channel_id){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				$return = array( 'error' => false );
				$prev_id= null;
				if( strlen($this->request->prev_id ) ){ $prev_id = $this->request->prev_id; }
				$return['logs'] = $this->logFilter($this->db->log->getLog($channel_id,$prev_id,100,'old')); 

			}
			return json_encode($return);
			
		}
		public function api_search(){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{ $return = $this->db->log->searchLog( $this->request->keyword, $this->request->channel_id ); }
			return json_encode($return);
		}
		public function api_read($channel_id){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				$this->db->channel->updateReaded($channel_id);
				$return = array( 'error' => false  );
			}
			return json_encode($return);
		}
		public function api_reset_unread(){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				$this->db->channel->updateReaded();	
				$return = array( 'error' => false  );
			}
			return json_encode($return);
		}
		public function api_set_view($channel_id){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				if( !strlen($this->request->value) ){
					$return = array( 'error' => true, 'msg' => 'parameter not found.' );
				}else{
					$this->db->channel->setView($channel_id,$this->request->value=='on');	
					$return = array( 'error' => false  );
				}
			}
			return json_encode($return);
		}
		public function api_get_channel_name( $channel_id ){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				$return = array( 'error' => false, 'id' => $channel_id , 'name' => $this->db->channel->getName($channel_id) );
			}
			return json_encode($return);
		}

		//api template
		public function api_template(){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				$return = array( 'error' => false  );
			}
			return json_encode($return);
		}

		//login処理
		public function login(){
			if( $this->isLoggedIn() ){
				return  $this->redirect('/');
			}

			$error_msg = "";

			if(strtolower($_SERVER['REQUEST_METHOD']) === 'post'){
				if( strlen($this->request->pass) ){
					if( md5($this->request->pass) == $this->options->password_md5 ){
						$this->session->login = 'true';

						//set cookie
						if( $this->request->cookie == 'true' )
							Cookie::set('UniqueId',$this->options->my_name,$this->options->password_md5,time()+$this->options->cookie_save_time, $this->options->mountPoint.'/' );

						//セッションにログイン前ページが記憶されてるかどうかを判定
						if( isset($this->session->befor) && strlen($this->session->befor) ){
							$this->redirect( $this->session->befor );
							unset( $this->session->befor );
						}else{
							$this->redirect('/');
						}
					}else{
						// パスワード間違ってるとき
						$error_msg = "not matched password";
					}
				} else {
					// パスワードないとき
					$error_msg = "invalid password";
				}
			}

			if (!empty($error_msg)) {
				$this->session->authMessage = $error_msg;
			} else {
				unset($this->session->authMessage);
			}

			return $this->render('login',array( 'error_msg' => $error_msg ));
		}

		//logout
		public function logout(){
			if($this->isLoggedIn()){
				$this->session->login = null;
				Cookie::delete('UniqueId',$this->options->mountPoint.'/' );
			}

			$this->redirect('/');

			exit;
		}
			
 		private function isLoggedIn() {
			if( empty($this->options->password_md5) ){ return true; }
			if( Cookie::get('UniqueId', $this->options->password_md5 ) == $this->options->my_name ){
				Cookie::set('UniqueId',$this->options->my_name,$this->options->password_md5,time()+$this->options->cookie_save_time, $this->options->mountPoint.'/' );
				return true;
			}
			return !is_null($this->session->login);
		}

		//util
		private function getLog( $channel_id, $max_id= null ){
			$this->db->channel->updateReaded($channel_id);
			return $this->logFilter( $this->db->log->getLog($channel_id, $max_id) );
		}
		private function getLogs( $channel_id, $max_id= null ){
			if( !empty($channel_id) && ctype_digit( $channel_id ) ){
				$this->db->channel->updateReaded($channel_id);
			}
			return $this->logFilter( $this->db->log->getLogAll($max_id) );
		}
		private function logFilter($logs){
			$on_image = $this->options->on_image;
			$link_class = $on_image === 2 ? 'boxviewimage' : 'inlineimage';

			$logs = array_map( function($log) use ($on_image,$link_class) {
				$after = "";
				
				$log[ 'log' ] = htmlspecialchars( $log[ 'log' ] );

				if (preg_match_all('/\\x03([0-9]+)([^\\x03]+)(\\x03)?/', $log['log'], $m)) {
					if ($m[0]) {
						foreach ($m[0] as $k=>$v) {
              				$cc = sprintf("%02d", $m[1][$k]);
							$log['log'] = str_replace($m[0][$k], "<span class='colorcode{$cc}'>{$m[2][$k]}</span>", $log['log']);
						}
					}
				}

				$log[ 'log' ] = preg_replace_callback( "/(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/", function($url) use ($on_image,$link_class,&$after){
					$url = $url[0];
					if( ImageURLParser::isImageFileURL( $url ) ){
						$after .= '<br><a href="'.$url.'" target="_blank" class="'.$link_class.'" data-player="img"><img src="'.$url.'"></a>';
					}else if( $resutlt = ImageURLParser::getServiceImageURL( $url ) ){
						if( empty( $resutlt[0] ) ){
							$after .= '<br><span href="'.$url.'" class="'.$link_class.'"><img src="'.$resutlt[1].'"></span>';
						}else{
							$after .= '<br><a href="'.$resutlt[0].'" target="_blank" class="'.$link_class.'" data-player="'.$resutlt[2].'"><img src="'.$resutlt[1].'"></a>';
						}
					}

					return '<a href="'.$url.'"  target="_blank">'.$url.'</a>';
				}, $log['log'] ).$after;
				
				return $log;
			}, $logs);

			$combined_logs = array();
			$prev_log = null;
			foreach ($logs as $log) {
				if ($prev_log &&
					$prev_log['nick'] == $log['nick'] &&
					$prev_log['time'] == $log['time'] &&
					( !is_set( $prev_log['channel_id'] ) || $prev_log['channel_id'] == $log['channel_id']) )
				{
					$prev_log['log'] = $log['log'] . '<br>' . $prev_log['log'];
				}
				else {
					if ($prev_log) array_push($combined_logs, $prev_log);
					$prev_log = $log;
				}
			}
			if ($prev_log) array_push($combined_logs, $prev_log);

			return $combined_logs;
		}
	}

	$app = new TiarraWEB( $conf );

	//routing
	$app->get('/','index');
	$app->get('/search/','search_select');
	$app->get('/setting/','setting_select');
	$app->get('/channel/:channel_id','channel_select',array('channel_id'=>'\d+'));
	$app->post_and_get('/login','login' );
	$app->get('/logout','logout' );

	$app->get('/channel/:channel_name','channel_name_select',array('channel_name'=>'.*'));
	
	//api
	$app->post('/api/logs/','api_logs');
	$app->post('/api/logs/:channel_id','api_next', array('channel_id'=>'\d+' ));
	$app->post('/api/post/','api_post');
	$app->post('/api/search/','api_search');
	$app->post('/api/read/:channel_id','api_read',array('channel_id'=>'\d+'));
	$app->post('/api/reset/unread','api_reset_unread');
	$app->post('/api/setting/view/:channel_id','api_set_view',array('channel_id'=>'\d+'));
	$app->post('/api/channel/name/:channel_id','api_get_channel_name',array('channel_id'=>'\d+'));
	
	$app->run();

	class myUtilHelper{
	}
