<?php

// system setting
ini_set("date.timezone", "Asia/Tokyo");

// debug setting
define("SYSTEM_DEBUG",false);
define("DATABASE_DEBUG",false);

// System側の設定なので、DBの内部文字コードがsjisだろうと書き変えないでください
define("DATABASE_CHARSET","utf8");

// base setting
$conf = Array(
	'mountPoint' => ''
	,'layout' => 'layout'
	,'dao' => array('channel','nick','log')
	//database
	,'DATABASE_HOST' => 'localhost'
	,'DATABASE_ID' =>  'tiarra'
	,'DATABASE_PASS' => ''
	,'DATABASE_NAME' => 'tiarra'
	//applicaiton
	,'my_name'=> 'noname'
	,'password_md5' => '4a7d1ed414474e4033ac29ccb8653d9b'
	,'tiarra_socket_name' => 'tiarrametro'
	,'style' => 'style.css'
	,'channel_list_label' => 'channels'
	,'cookie_save_time' => 7*86400
);

// javascript setting
$jsConf = Array(
	'my_name'=> 'noname'
	,'update_time' => 3
	,'pickup_word' => false
	,'pickup_channel'=> '.*'
	,'on_icon' => false
	,'on_image' => 0
	,'alias' => false
	,'log_popup_menu' => array(
		'separator' => '@'
		,'network' =>  array()
	)
);

$tig_default_popup_menu = Array(
			'match' => '\((\w+)\)',
			'menu' => Array(
				're' => Array( 'type' => 'typablemap_comment', 'label' => 'reply' ),
				'fav' => Array( 'type' => 'typablemap', 'label' => 'favorites' ),
				'rt' => Array( 'type' => 'typablemap', 'label' => 're tweet' ),
				'res' => Array( 'type' => 'typablemap', 'label' => 'reply to view' ),
			)
		);

$fig_default_popup_menu = Array(
			'match' => '\((\w+)\)',
			'menu' => Array(
				're' => Array( 'type' => 'typablemap_comment', 'label' => 'comment' ),
				'like' => Array( 'type' => 'typablemap', 'label' => 'like!' ),
			)
		);

