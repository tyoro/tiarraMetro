<!DOCTYPE html> 
<html lang="ja">
<head>
<meta charset="UTF-8" /> 
<meta name="viewport" content="width=480px,initial-scale=0.66,minimum-scale=0.66,maximum-scale=1.0,user-scalable=no" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<link rel="apple-touch-icon" href="./images/apple-touch-icon.png" />
<!-- link rel="apple-touch-icon" href="apple-touch-icon-precomposed.png" / -->
<base href="<?php print $uri_base; ?>">
<title><?php echo TiarraWEB::$page_title; ?></title>
<link type="text/css" rel="stylesheet" href="css/<?php $options->style; ?>" />
<link type="text/css" rel="stylesheet" href="css/jquery.metro.css" />
<link type="text/css" rel="stylesheet" href="css/jquery.jgrowl.css" />
<script type="text/javascript" src= "js/jquery.js"></script>
<script type="text/javascript" src= "js/jquery.metro.js"></script>
<script type="text/javascript" src= "js/jquery.touchwipe.min.js"></script>
<script type="text/javascript" src= "js/jquery.jgrowl_minimized.js"></script>
</head>
<body theme="dark" accent="tiarra">
	<div id='container'>
      <?php echo $content; ?>
	</div>
	<div id='preload'>
		<img src='./images/spinner_b.gif' style='display: none;' />
	</div>
</body>
</html>
