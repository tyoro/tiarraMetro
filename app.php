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

		public function index(){
			global $pickup_words;

			if( !$this->isLoggedIn() ){ 
				return $this->redirect('/login');
			}
			$channel_list = array();
			$log_list = array();

			foreach( $this->db->channel->getUnreadList() as $ch ){
				$channel_list[$ch['id']] = $ch;
				$log_list[$ch['id']] = $this->db->log->getLog($ch['id']);
			}
			return $this->render('index',
				array(
					'checktime' => date("Y-m-d H:i:s"),
					'channels' => $channel_list,
					'pickup' => $pickup_words,
					'logs' => $log_list
					)
			);
		}

		//api
		public function api_logs( ){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			else{
				$return = array( 'error'=> false, 'update'=>false,'checktime'=>date("Y-m-d H:i:s") );
				if( !empty($this->request->checktime) && !empty($this->request->current) ){
					$logs = $this->getLogs($this->request->current,$this->request->checktime);
					if( count($logs) ){
						$return['update'] = true;

						$ch_log = array();
						foreach( $logs as $log ){
							$ch_log[$log['channel_id']][] = array(
								'nick' => $log['nick'],
								'log' => $log['log'],
								'time' => $log['time']
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
							$tiarra = new Net_Socket_Tiarra('webreader');
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
		public function api_search(){
			if( !$this->isLoggedIn() ){ $return = array( 'error' => true, 'msg' => 'no login.' ); }
			$return = $this->db->log->searchLog( $this->request->keyword, $this->request->channel_id );
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
		private function getLog( $channel_id, $checktime = null ){
			$this->db->channel->updateReaded($channel_id);
			return $this->db->log->getLog($channel_id, $checktime);
		}
		private function getLogs( $channel_id, $checktime = null ){
			if( is_int( $channel_id ) ){
				$this->db->channel->updateReaded($channel_id);
			}
			return $this->db->log->getLogAll($checktime);
		}
	}

	$app = new TiarraWEB( $conf );

	//routing
	$app->get('/','index');
	$app->post_and_get('/login','login' );
	
	//api
	$app->post('/api/logs/','api_logs');
	$app->post('/api/post/','api_post');
	$app->post('/api/search/','api_search');
	$app->post('/api/read/:channel_id','api_read',array('channel_id'=>'\d+'));
	
	$app->run();

	class myUtilHelper{
	}
