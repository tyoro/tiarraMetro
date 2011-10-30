<?php

	include_once 'conf.php';
	include_once 'dao.php';
	include_once 'myFitzgerald.php';
	include_once 'lib/adodb5/adodb.inc.php';
	include_once 'lib/adodb5/adodb-pear.inc.php';

	include_once 'Net/Socket/Tiarra.php';
	
	class TiarraWEB extends MyFitzgerald {
		public static $page_title = "tiarra";
		public static $msg = '';

		public function index_main( $pivot = 'default', $default_channel_id = -1 ){
			if( !$this->isLoggedIn() ){ 
				return $this->redirect('/login');
			}
			global $pickup_words;

			$channel_list = array();
			$log_list = array();
			$default_channel = array( 'id'=>$default_channel_id );
			$max_id = 0;

			$max_id = $this->db->log->getMaxID();
			foreach( $this->db->channel->getUnreadList() as $ch ){
				$channel_list[$ch['id']] = $ch;
				$log_list[$ch['id']] = $this->db->log->getLog($ch['id']);
				if( $default_channel_id == $ch['id'] ){ $default_channel[ 'name' ] = $ch['name']; }
			}
			return $this->render('index',
				array(
					'max_id' => $max_id,
					'channels' => $channel_list,
					'pickup' => $pickup_words,
					'logs' => $log_list,
					'pivot' => $pivot,
					'default_channel' => $default_channel
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

				if( !empty($this->request->max_id) && !empty($this->request->current) ){
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
								'is_privmsg' => $log['is_privmsg']
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
			global $tiarra_socket_name;
			global $my_name;
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
							$tiarra = new Net_Socket_Tiarra($tiarra_socket_name);
							$tiarra->message($name, $this->request->post);
							//$tiarra->noticeMessage($name, "notice!!");
							$return = array( 'error' => false );

							$this->db->log->postLog( $this->request->post, $this->request->channel_id, $this->db->nick->getID( $my_name ) );

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

		//login処理
		public function login(){
			global $password_md5;
			if( $this->isLoggedIn() ){
				return  $this->redirect('/');
			}
			$error_msg = "";
			if( strlen($this->request->pass) ){
				if( md5($this->request->pass) == $password_md5 ){
					$this->session->login = 'true';
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
			
        private function isLoggedIn() {
			return !is_null($this->session->login);
		}

		//util
		private function getLog( $channel_id, $max_id= null ){
			$this->db->channel->updateReaded($channel_id);
			return $this->db->log->getLog($channel_id, $max_id);
		}
		private function getLogs( $channel_id, $max_id= null ){
			if( ctype_digit( $channel_id ) ){
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

	$app->get('/channel/:channel_name','channel_name_select',array('channel_name'=>'.*'));
	
	//api
	$app->post('/api/logs/','api_logs');
	$app->post('/api/logs/:channel_id','api_next', array('channel_id'=>'\d+' ));
	$app->post('/api/post/','api_post');
	$app->post('/api/search/','api_search');
	$app->post('/api/read/:channel_id','api_read',array('channel_id'=>'\d+'));
	
	$app->run();

	class myUtilHelper{
	}
