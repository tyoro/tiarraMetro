<?php
	include_once 'conf/load.php';

	include_once 'myFitzgerald.php';
	include_once 'dao.php';
	include_once 'lib/util.php';
	include_once 'lib/Net/Socket/Tiarra.php';

	class TiarraWEB extends MyFitzgerald {
		public static $page_title = "tiarraMetro";
		public static $msg = '';

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
				$log_list[$ch['id']] = $this->db->log->getLog($ch['id']);
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

							$this->db->log->postLog( $this->request->post, $this->request->channel_id, $this->db->nick->getID( $this->options->my_name ), $this->request->notice );

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
				$return['logs'] = $this->db->log->getLog($channel_id,$prev_id,100,'old'); 

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
					$error_msg = "no match password!!";
				}
			}
			return $this->render('login',array( 'error_msg' => $error_msg ));
		}

		//logout
		public function logout(){
			if( !$this->isLoggedIn() ){ return  $this->redirect('/'); }
			$this->session->login = null;
			Cookie::delete('UniqueId',$this->options->mountPoint.'/' );
			$this->redirect('/');
		}
			
        private function isLoggedIn() {
			if( empty($this->options->password_md5) ){ return true; }
			if( Cookie::get('UniqueId', $this->options->password_md5 ) == $this->options->my_name ){ return true; }
			return !is_null($this->session->login);
		}

		//util
		private function getLog( $channel_id, $max_id= null ){
			$this->db->channel->updateReaded($channel_id);
			return $this->db->log->getLog($channel_id, $max_id);
		}
		private function getLogs( $channel_id, $max_id= null ){
			if( !empty($channel_id) && ctype_digit( $channel_id ) ){
				$this->db->channel->updateReaded($channel_id);
			}
			return $this->db->log->getLogAll($max_id);
		}
	}

	$app = new TiarraWEB( $conf );

	//routing
	$app->get('/','index');
	$app->get('/search/','search_select');
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
	
	$app->run();

	class myUtilHelper{
	}
