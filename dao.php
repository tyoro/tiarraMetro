<?php

class dao_base{
	var $_settings = array();
	var $_conn = null;
	var $_name = null;

	public function __construct( $conn, $settings = array() ){
		$this->_conn = $conn;
		$this->_settings = $settings;

		$className = get_class($this);

		if (empty($this->_name) && strtolower($className) !== 'dao_base') {
			$this->_name = str_replace('dao_', '', $className);
		}
	}

	public function qs( $str ){
		return $this->_conn->qstr( $str );
	}

	protected function onDebug(){ $this->_conn->debug = 1; }
	protected function offDebug(){ $this->_conn->debug = 0; }

}

class dao_nick extends dao_base{

	var $_name = 'nick';

	function getID( $name ){
		$sql = "SELECT id FROM nick WHERE name = ?";
		$id = $this->_conn->GetOne(
			$this->_conn->Prepare($sql),
			array($name)
		);
		return ($id !== false) ? $id : -1;
	}

	function getName( $id ){
		$sql = "SELECT name FROM nick WHERE id = ?";

		$name = $this->_conn->GetOne(
			$this->_conn->Prepare($sql),
			array($id)
		);
		
		return ($name !== false) ? $name : null;
	}

	function searchNick( $nick_str ){
	}
}

class dao_channel extends dao_base{

	var $_name = 'channel';

	function getID( $name ){
		$sql = "SELECT id FROM channel WHERE name = ?";

		$id = $this->_conn->GetOne(
			$this->_conn->Prepare($sql),
			array($name)
		);
		
		return ($id !== false) ? $id : -1;
	}

	function getName( $id ){
		$sql = "SELECT name FROM channel WHERE id = ? ";

		$name = $this->_conn->GetOne(
			$this->_conn->Prepare($sql),
			array($id)
		);

		return ($name !== false) ? $name : null;
	}

	function getList( $server = "" ){
		
		$sql = "SELECT * FROM channel";
		$values = array();

		if( !empty($server) ){
			$sql .= " WHERE name like ?";
			$values[] = "%@".$server;
		}

		return $this->_conn->getArray($this->_conn->Prepare($sql), $values);
	}

	function getUnreadList( $server = "", $sort = 0 ){
		$sql = "SELECT 
					channel.id, 
					channel.name, 
					substring(channel.name, locate('@', channel.name) + 1) as network,
					COALESCE(log_count.cnt,0) as cnt 
				FROM channel 
					LEFT JOIN (
						SELECT 
							channel.id, 
							count(*) as cnt 
						FROM channel 
							LEFT JOIN log ON channel.id = log.channel_id 
						WHERE channel.readed_on < log.created_on 
						GROUP BY channel.id
					) as log_count ON channel.id = log_count.id 
				WHERE view = ?";
		$values = array(1);

		if( !empty($server) ){
			$sql .= " WHERE name like ? ";
			$values[] = "%@".$server;
		}

		$order = $this->detectOrder($sort);
		if (!empty($order)) {
			$sql .= $order;
		}


		return $this->_conn->getArray($this->_conn->Prepare($sql), $values);
	}

	function detectOrder($sort) {
		$order = '';

		if (is_array($sort)) {
			$order = $this->getMultipleSortOrder($sort);
		} else {
			switch( $sort ){
				case '2': case 'read':
					$order = " ORDER BY channel.readed_on DESC";
					break;
				case '0': case 'no':
					break;
				case '1': case 'name':
				default:
					$order = " ORDER BY name ASC";
					break;
			}
		}

		return $order;
	}

	function getMultipleSortOrder(array $sort){
		$data = array();

		if(ArrayUtil::isHash($sort)){
			foreach ($sort as $key => $value) {
				if (strpos($key, '.') > 0) {
					list($table, $column) = explode('.', $key);
				} else {
					$table = 'channel';
					$column = $key;
				}

				$direction = preg_match('/^D(?:ESC)?$/i', $value) ? 'DESC' : 'ASC';

				if (($table === 'channel' || $table === 'log') && in_array($column, $this->_settings[$this->_name])) {
					$data[] = sprintf('%s.%s %s', $table, $column, $direction);
				} else if (($column === 'network' || $column === 'cnt')) {
					$data[] = sprintf('%s %s', $column, $direction);
				}
			}
		} else {
			foreach (array_values($sort) as $key) {
				if (strpos($key, '.') > 0) {
					list($table, $column) = explode('.', $key);
				} else {
					$table = 'channel';
					$column = $key;
				}

				if (($table === 'channel' || $table === 'log') && in_array($column, $this->_settings[$this->_name])) {
					$data[] = sprintf('%s.%s ASC', $table, $column);
				} else if (($column === 'network' || $column === 'cnt')) {
					$data[] = sprintf('%s ASC', $column);
				}
			}
		}

		return (count($data) > 0) ? ' ORDER BY ' . implode(', ', $data) : false ;
	}

	function updateReaded( $id = null ){
		$sql = "UPDATE channel SET readed_on = NOW()";
		$values = array();

		if( !is_null( $id ) ){
			$sql .=  " WHERE id = ?";
			$values[] = $id;
		}

		return $this->_conn->Execute($this->_conn->Prepare($sql), $values);
	}
}

class dao_log extends dao_base{

	var $_name = 'log';

	function getLog( $channel_id, $log_id = null,  $num = 30, $type = "new"  ){
		$sql = "SELECT 
					log.id as id, 
					nick.name as nick, 
					log.log as log, 
					log.created_on as time, 
					log.is_notice as is_notice 
				FROM log 
					JOIN nick ON log.nick_id = nick.id 
				WHERE channel_id = ? ";
		$values = array($channel_id);

		if( !is_null( $log_id ) ){
			$sql .= " AND log.id ". ( $type!="old" ? '>' : '<' ). " ?";
			$values[] = $log_id;
		}
		
		$sql .= " ORDER BY log.created_on DESC LIMIT 0, ?";
		$values[] = $num;
		
		return $this->_conn->getArray($this->_conn->Prepare($sql), $values);
	}

	function getLogAll( $max_id ){
		if( !strlen($max_id) ){
			return null;
		}
		$sql = "SELECT 
					log.channel_id as channel_id, 
					log.id as id , 
					nick.name as nick, 
					log.log as log, 
					log.created_on as time,
					log.is_notice as is_notice 
				FROM log 
					JOIN nick ON log.nick_id = nick.id 
					JOIN channel ON log.channel_id = channel.id 
				WHERE channel.view = ? 
					AND log.id > ? 
				ORDER BY log.created_on DESC
				";

		$values = array(1, $max_id);

		return $this->_conn->getArray($this->_conn->Prepare($sql), $values);
	}

	function searchLog( $word, $channel_id = null ){
		$sql = "SELECT 
					log.id, 
					nick.name as nick, 
					channel.name as channel_name, 
					log.log as log, 
					log.created_on as time,
					log.is_notice as is_notice 
				FROM log 
					JOIN nick ON log.nick_id = nick.id 
					JOIN channel ON log.channel_id = channel.id  
				WHERE log.log like ? ";

		$values = array("%$word%");

		if( !is_null( $channel_id ) ){
			$sql .= " AND log.channel_id = ? ";
			$values[] = $channel_id;
		}
		$sql .= " ORDER BY log.created_on DESC LIMIT 0,30 ";

		return $this->_conn->getArray($this->_conn->Prepare($sql), $values);
	}

	function postLog( $message, $channel_id, $nick_id, $notice ){
		$sql = "INSERT INTO `log` 
					(`channel_id`, `nick_id`, `log`, `is_notice`, `created_on`, `updated_on`) 
				VALUES 
					(?, ?, ?, ?, NOW(), NOW() )
				";
		$values = array($channel_id, $nick_id, $message, $notice=='true'?1:0);
		
		return $this->_conn->Execute($this->_conn->Prepare($sql), $values);
	}

	function getMaxID( ){
		$sql = "SELECT max(id) AS max_id FROM log";
		return $this->_conn->GetOne($this->_conn->Prepare($sql));
	}

}
