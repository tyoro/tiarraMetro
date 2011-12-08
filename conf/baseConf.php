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
	//applicaiton
	,'style' => 'style.css'
	,'channel_list_label' => 'channels'
	,'cookie_save_time' => 7*86400
);

// javascript setting
$jsConf = Array(
	'update_time' => 3
	,'pickup_word' => array()
	,'pickup_channel'=> '.*'
	,'my_name'=> 'noname'
	,'on_icon' => false
	,'on_image' => 0
	,'click_menu' => array()
);


