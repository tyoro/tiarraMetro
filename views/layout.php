<!DOCTYPE html> 
<html lang="ja">
<head>
<meta charset="UTF-8" /> 
<meta name=" robots" content="noindex,nofollow,nocache,noarchive">
<meta name="format-detection" content="telephone=no" />
<meta name="viewport" content="width=320,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<!-- meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" / -->
<link rel="apple-touch-icon" href="./images/apple-touch-icon.png" />
<!-- link rel="apple-touch-icon" href="apple-touch-icon-precomposed.png" / -->
<base href="<?php print $uri_base; ?>">
<title><?php !empty($options->page_title) ? print(htmlspecialchars($options->page_title)):print(htmlspecialchars(TiarraWEB::$page_title)); ?></title>
<link type="text/css" rel="stylesheet" href="css/jquery.metro.css" />
<link type="text/css" rel="stylesheet" href="css/jquery.jgrowl.css" />
<link type="text/css" rel="stylesheet" href="css/shadowbox.css" />
<link type="text/css" rel="stylesheet" href="css/metro.notifier.css" />
<link type="text/css" rel="stylesheet" href="<?php print ((strpos( $options->style, 'http' )!==false)?'':'css/').$options->style; ?>" />
<script type="text/javascript" src= "js/jquery.js"></script>
<script type="text/javascript" src= "js/jquery.metro.js"></script>
<script type="text/javascript" src= "js/jquery.hammer.min.js"></script>
<script type="text/javascript" src= "js/jquery.jgrowl_minimized.js"></script>
<?php if( !empty( $jsConf['keymapping'] )){ ?>
<script type="text/javascript" src= "js/jquery.hotkeys.js"></script>
<?php } ?>
<script type="text/javascript" src= "js/shadowbox.js"></script>
<script type="text/javascript" src= "js/metroNotifier.js"></script>
<?php if( $options->single_mode ){ ?>
<script type="text/javascript" src= "js/tiarraMetro.single.js"></script>
<?php }else{ ?>
<script type="text/javascript" src= "js/tiarraMetro.js"></script>
<?php } ?>
<script src="//twemoji.maxcdn.com/twemoji.min.js"></script>
</head>
<body theme="<?php print $options->theme; ?>" accent="<?php print $options->accent; ?>">
<?php if( !empty( $options->wallparper )){ ?>
  <div id='wallparper' style='background-image: url("../images/<?php print $options->wallparper; ?>");' >
<?php } ?>
	<div id='container' class='theme-bg'>
      <?php echo $content; ?>
	</div>
	<div id='preload'>
		<img src='./images/spinner_b.gif' style='display: none;' />
	</div>
<?php if( !empty( $options->wallparper )){ ?>
  </div>
<?php } ?>
</body>
</html>

