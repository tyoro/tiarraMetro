<!DOCTYPE html> 
<html lang="ja">
<head>
<meta charset="UTF-8"> 
<meta name="viewport" content="width=480;initial-scale=1.0;maximum-scale=1.0;user-scalable=0;" />
<base href="<?php print $uri_base; ?>">
<title><?php echo TiarraWEB::$page_title; ?></title>
<link rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="css/jquery.metro.css" />
<script src= "./js/jquery.js"></script>
<script src= "./js/jquery.metro.js"></script>
</head>
<body>
      <?php echo $content; ?>
</body>
</html>
