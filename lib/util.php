<?php

//http://h19e.jugem.jp/?eid=33
/*
class Cookie
{
	static $sessionName = 'fitzgerald_session';
	static $endExpire = 0;

	//Cookieをセットするメソッド
	static public function set($name,$value,$pass,$time = 0, $path = '/')
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		//暗号化するためのキーを生成
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	   
		//closeKeyには、オリジナルの文字列を登録してください。
		$closeKey = $pass;
	   
		$encryptValue = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $closeKey, $value, MCRYPT_MODE_CBC, $iv);
		
		//ワンタイムパスワードを書き込む
		try{
			$fp = fopen('/tmp/tM_'.md5($path).'.tmp','w');
			fwrite($fp,base64_encode($iv));
			fclose($fp);
		}catch( Exception $e ){
			return;
		}
	  
		//暗号化された値をCookieに書き込みます。
		//DOMAIN_FOR_COOKIEはCookieを使用するサイトのドメインです。
		setcookie($name,base64_encode($encryptValue),$time , $path, $_SERVER['SERVER_NAME']);
	}

	//Cookieを取得するためのメソッド
	static public function get($name,$pass)
	{
		$closeKey = $pass;

		if (is_file('/tmp/tM_'.md5($path).'.tmp') && isset($_COOKIE[$name])) {
			//ワンタイムパスワードを読み込む
			try{
				$fp = fopen('/tmp/tM_'.md5($path).'.tmp','r');
				$iv = base64_decode(fgets($fp));
				fclose($fp);
			}catch(Exception $e ){
				return false;
			}
			
			$encryptValue = base64_decode($_COOKIE[$name]);
		   
			//Cookieに保存されていたキーを使用してCookieの値を複合します。
			$value = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$closeKey,$encryptValue,MCRYPT_MODE_CBC,$iv);
			return str_replace("\0","",$value);
		}
		return false;
	}

	//Cookieを削除する為のメソッド
	static public function delete($name,$path){
		if (is_file('/tmp/tM_'.md5($path).'.tmp') && isset($_COOKIE[$name])) {
			unlink( '/tmp/tM_'.md5($path).'.tmp' );
			setcookie( $name, null, Cookie::$endExpire, $path, $_SERVER['SERVER_NAME'] );
		}
	}

}
*/
class Cookie
{
	static $sessionName = 'fitzgerald_session';
	static $endExpire = 0;

	//Cookieをセットするメソッド
	static public function set($name,$value,$pass,$time = 0, $path = '/')
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		//暗号化するためのキーを生成
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		//closeKeyには、オリジナルの文字列を登録してください。
		$closeKey = $pass;

		$encryptValue = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $closeKey, $value, MCRYPT_MODE_CBC, $iv);

		//暗号化された値をCookieに書き込みます。
		//DOMAIN_FOR_COOKIEはCookieを使用するサイトのドメインです。
		setcookie($name,base64_encode($encryptValue),$time , $path, $_SERVER['SERVER_NAME']);

		//暗号化された値を解読するためのキーをCookieに書き込みます。
		setcookie("iv_" . $name ,base64_encode($iv),$time , $path, $_SERVER['SERVER_NAME'] );
	}

	//Cookieを取得するためのメソッド
	static public function get($name,$pass)
	{
		$closeKey = $pass;

		if (isset($_COOKIE['iv_' . $name]) && isset($_COOKIE[$name])) {
			$iv = base64_decode($_COOKIE['iv_' . $name]);
			$encryptValue = base64_decode($_COOKIE[$name]);

			//Cookieに保存されていたキーを使用してCookieの値を複合します。
			$value = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$closeKey,$encryptValue,MCRYPT_MODE_CBC,$iv);
			return str_replace("\0","",$value);
		}
		return false;
	}

	//Cookieを削除する為のメソッド
	static public function delete($name,$path){
		if (isset($_COOKIE['iv_' . $name]) && isset($_COOKIE[$name])) {
			setcookie( $name, null, Cookie::$endExpire, $path, $_SERVER['SERVER_NAME'] );
			setcookie( 'iv_'.$name, null, Cookie::$endExpire, $path, $_SERVER['SERVER_NAME'] );
		}
	}

}

	class ArrayUtil {
		static public function isHash( $target ){
			if (!is_array($target)) return false;

			$result = true;

			foreach ($target as $key => $value){
				if(is_numeric($key)) {
					$result = false;
					break;
				}
			}

			return $result;
		}

		static public function getWithKey($from, $key = 0){
			$result = array();

			foreach ((array)$from as $value){
				if (is_array($value) && !empty($value[$key])) {
					$result[] = $value[$key];
				}
			}

			return $result;
		}
	}
?>

