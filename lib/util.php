<?php

//http://h19e.jugem.jp/?eid=33
class Cookie
{
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
			setcookie( $name, null, time() - 3600, $path );
			setcookie( 'iv_'.$name, null, time() - 3600, $path );
		}
	}

}
