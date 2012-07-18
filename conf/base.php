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
	,'page_title' => 'tiarraMetro'
	,'tiarra_socket_name' => 'tiarrametro'
	,'theme' => 'dark'
	,'accent' => 'tiarra'
	,'style' => 'style.css'
	,'channel_list_label' => 'channels'
	,'channel_list_sort' => 'name'
	,'cookie_save_time' => 7*86400
	,'on_image' => 0
	,'wallparper' => ''
	,'base_uri' => 'absolute' //absolute,relative 
);

// javascript setting
$jsConf = Array(
	'my_name'=> 'noname'
	,'update_time' => 3
	,'pickup_word' => false
	,'pickup_channel'=> '.*'
	,'disable_swipe' => false
	,'on_icon' => false
	,'on_image' => 0
	,'on_twitter_link' => 1
	,'template' => 'table'
	,'icon_server_uri' => 'http://img.tweetimag.es/i/'
	,'channel_filter' => false
	,'alias' => false
	,'log_popup_menu' => array(
		'separator' => '@'
		,'network' =>  array()
	)
	,'quickpost_auto_close' => true
	,'patrol_channel' => false
	,'keymapping' => false
	,'auto_tail_delete' => false
	,'side_channel_list' => false
	,'single_mode' => false
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

$quickpost_only_popup_menu = Array(
			'match' => '.*',
			'menu' => Array(
			)
		);

$quickpost_only_popup_menu_no_close = Array(
			'match' => '.*',
			'auto_close' => false,
			'menu' => Array(
			)
		);

