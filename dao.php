<?php
//setlocale(LC_ALL, "ja_JP.UTF-8");

class dao_base{
	var $_conn = null;

	public function __construct( $conn ){
		$this->_conn = $conn;
	}

	public function qs( $str ){
		return $this->_conn->qstr( $str );
	}

	protected function onDebug(){ $this->_conn->debug = 1; }
	protected function offDebug(){ $this->_conn->debug = 0; }

}

class dao_nick extends dao_base{

	function getID( $name ){
		$sql = "SELECT id FROM nick WHERE name = ".$this->qs($name);
		$id = $this->_conn->GetOne($sql);
		if( $id !== false ){
			return $id;
		}
		return -1;
	}

	function getName( $id ){
		$sql = "SELECT name FROM nick WHERE id = ".$this->qs($id);
		$name = $this->_conn->GetOne($sql);
		if( $name !== false ){
			return $name;
		}
	}

	function searchNick( $nick_str ){
	}
}

class dao_channel extends dao_base{
	function getID( $name ){
		$sql = "SELECT id FROM channel WHERE name = ".$this->qs($name);
		$id = $this->_conn->GetOne($sql);
		if( $id !== false ){
			return $id;
		}
		return -1;
	}

	function getName( $id ){
		$sql = "SELECT name FROM channel WHERE id = ".$this->qs($id);
		$name = $this->_conn->GetOne($sql);
		if( $name !== false ){
			return $name;
		}
	}

	function getList( $server = "" ){
		
		$sql = "SELECT * FROM channel";

		if( !empty($server) ){
			$sql .= " WHERE name like \"%@".$server."\"";
		}
		return $this->_conn->getArray($sql);
	}

	function getUnreadList( $server = "" ){
		$sql = "SELECT channel.id,channel.name,COALESCE(log_count.cnt,0) as cnt FROM channel LEFT JOIN (SELECT channel.id, count(*) as cnt FROM channel LEFT JOIN log ON channel.id = log.channel_id WHERE channel.readed_on < log.created_on GROUP BY channel.id) as log_count ON channel.id = log_count.id";

		if( !empty($server) ){
			$sql .= " WHERE name like \"%@".$server."\"";
		}

		//$sql .= " ORDER BY cnt DESC";

		return $this->_conn->getArray($sql);
	}
	
	function updateReaded( $id ){
		$sql = "UPDATE channel SET readed_on = NOW() WHERE id = $id";
		return $this->_conn->Execute($sql);
	}
}

class dao_log extends dao_base{
	function getLog( $channel_id, $time = null,  $num = 30, $start = 0  ){
		$sql = "SELECT nick.name as nick, log.log as log, log.created_on as time, log.is_privmsg as is_privmsg FROM log JOIN nick ON log.nick_id = nick.id WHERE channel_id = $channel_id";

		if( !is_null( $time ) ){
			$sql .= " AND log.created_on > ".$this->qs($time);
		}
		
		$sql .= " ORDER BY log.created_on DESC LIMIT $start, $num  ";
		return $this->_conn->getArray($sql);
	}

	function getLogAll( $time ){
		if( !strlen($time) ){
			return null;
		}
		$sql = "SELECT log.channel_id as channel_id, nick.name as nick, log.log as log, log.created_on as time,log.is_privmsg as is_privmsg FROM log JOIN nick ON log.nick_id = nick.id WHERE log.created_on > ".$this->qs($time);
		$sql .= " ORDER BY log.created_on DESC";
		return $this->_conn->getArray($sql);
	}

	function searchLog( $word, $channel_id = null ){
		$sql = "SELECT nick.name as nick, channel.name as channel_name, log.log as log, log.created_on as time,log.is_privmsg as is_privmsg FROM log JOIN nick ON log.nick_id = nick.id JOIN channel ON log.channel_id = channel.id  WHERE log.log like ".$this->qs("%$word%")." ";
		
		if( !is_null( $channel_id ) ){
			$sql .= " AND log.channel_id = $channel_id ";
		}
		$sql .= " ORDER BY log.created_on DESC LIMIT 0,30 ";
		return $this->_conn->getArray($sql);
	}

	function postLog( $message, $channel_id, $nick_id ){
		$sql = "insert into log(channel_id,nick_id,log,is_privmsg,created_on,updated_on) values($channel_id,$nick_id,".$this->qs($message).",1,NOW(),NOW() )";
		return $this->_conn->Execute($sql);
	}
}
