<!doctype html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8">
<meta name="google" content="notranslate">
<meta http-equiv="Content-Language" content="en">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title><?php echo $pageTitle ?></title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/css/normalize.min.css">
<link rel="stylesheet" href="assets/css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="assets/css/animate.css" type="text/css" />
<link rel="stylesheet" href="assets/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="assets/css/icon.css" type="text/css" />
<link rel="stylesheet" href="assets/css/font.css" type="text/css" />
<link rel="stylesheet" href="assets/css/roundslider.css" />
<link rel="stylesheet" href="assets/css/main.css">
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
</head>
<body class="<?php echo $bodyClass ?>">
<?php
  print getSessionMessages();
?>
<div id="outter_rim">
<div id="inner_rim">
	<?php include("gl_headernav.php"); ?>