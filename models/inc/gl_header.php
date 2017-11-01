<!DOCTYPE html>
<!--[if IE 7]> <html lang="en" class="ie7"> <![endif]-->
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
<title><?php echo $pg_title?></title>
<!-- Meta -->
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="Site / page description" />
<meta name="author" content="Stanford | Medicine" />
<!-- These meta tags are used when someone shares a link to this page on Facebook,
     Twitter or other social media sites. All tags are optional, but including them
     and customizing the content for specific sites can help the visibility of your
     content.
<meta property="og:type" content="website" />
<meta property="og:title" content="Title when shared to social media sites" />
<meta property="og:description" content="Snippet for social media sites." />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@TwitterHandle" />
<meta name="twitter:title" content="Title for Twitter" />
<meta name="twitter:description" content="Snippet when tweet is expanded." />
<meta name="twitter:image" content="http://stanford.edu/about/images/intro_about.jpg" />
<link rel="publisher" href="https://plus.google.com/id# of Google+ entity associated with your department or group" />
-->

<!-- Apple Icons - look into http://cubiq.org/add-to-home-screen -->
<link rel="apple-touch-icon" sizes="57x57" href="assets/img/apple-icon-57x57.png" />
<link rel="apple-touch-icon" sizes="72x72" href="assets/img/apple-icon-72x72.png" />
<link rel="apple-touch-icon" sizes="114x114" href="assets/img/apple-icon-114x114.png" />
<link rel="apple-touch-icon" sizes="144x144" href="assets/img/apple-icon-144x144.png" />
<link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png" />
<link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png" />
<link rel="shortcut icon" href="assets/img/favicon.ico?v=2" />

<!-- CSS -->
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="assets/css/base.min.css?v=0.1" type="text/css" />
<link rel="stylesheet" href="assets/css/custom.css?v=0.1" type="text/css"/>

<!--[if lt IE 9]>
  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if IE 8]>
  <link rel="stylesheet" type="text/css" href="assets/css/ie/ie8.css" />
<![endif]-->
<!--[if IE 7]>
  <link rel="stylesheet" type="text/css" href="assets/css/ie/ie7.css" />
<![endif]-->
<!-- JS and jQuery -->
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
 -->
<!--[if lt IE 9]>
	<script src="assets/js/respond.js"></script>
<![endif]-->

<!-- PLACING JSCRIPT IN HEAD OUT OF SIMPLICITY - http://stackoverflow.com/questions/10994335/javascript-head-body-or-jquery -->
<!-- Latest compiled and minified JavaScript -->
<!--
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
-->
<!-- Local version for development here -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.validate.min.js"></script>
<!-- <script src="assets/js/bootstrap.min.js"></script> -->

<!-- custom JS -->
<!-- <script src="assets/js/custom.js"></script> -->

<!-- Include all compiled plugins (below), or include individual files as needed -->
<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Crimson+Text:400,600,700' rel='stylesheet' type='text/css'>
<script src="https://www.google.com/recaptcha/api.js"></script>
</head>
<?php
if($portal_test){
	echo "<div id='testserver'>Test Server</div>";
}
?>
<body class="<?php echo $body_classes ?>">
<?php
  print getSessionMessages();
?>
<div id="su-wrap">
<div id="su-content">