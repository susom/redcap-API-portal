<?
require_once("models/config.php");
$pg_title = "Login | $websiteName";

if(isUserLoggedIn()){
	logIt('Logged in - redirect to userpage...', 'DEBUG');
	header("Location: userpage.php");
	exit;
}
?>
<!DOCTYPE html>
<!--[if IE 7]> <html lang="en" class="ie7"> <![endif]-->
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
<title><?=$pg_title?></title>
<!-- TemplateParam name="theme" type="text" value="lagunita" -->

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
<link rel="stylesheet" href="assets/lagunita/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="assets/lagunita/css/base.min.css?v=0.1" type="text/css" />
<link rel="stylesheet" href="assets/lagunita/css/custom.css?v=0.1" type="text/css"/>

<!--[if lt IE 9]>
  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if IE 8]>
  <link rel="stylesheet" type="text/css" href="assets/lagunita/css/ie/ie8.css" />
<![endif]-->
<!--[if IE 7]>
  <link rel="stylesheet" type="text/css" href="assets/lagunita/css/ie/ie7.css" />
<![endif]-->
<!-- JS and jQuery -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>

<!--[if lt IE 9]>
	<script src="assets/lagunita/js/respond.js"></script>
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

<!-- custom JS -->
<!-- <script src="assets/lagunita/js/custom.js"></script> -->

<!-- Include all compiled plugins (below), or include individual files as needed -->
<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Crimson+Text:400,600,700' rel='stylesheet' type='text/css'>
<script src="https://www.google.com/recaptcha/api.js"></script>
</head>
<!-- Customization Options:                       
     body class:   "home", "nav-1", "nav-2", etc. - specifies which item in the top nav to underline
                   "site-slogan" - display a site slogan in the header signature
     logo, h1  :   "hide" - hides the logo or H1 element, eg <h1 class="hide">
 -->
<body class="site-slogan consent">
<div id="su-wrap">
  <div id="su-content">
    <div id="brandbar">
      <div class="container"> 
        <a class="pull-left som_logo" href="http://www.stanford.edu"><img src="assets/lagunita/images/brandbar_logo_som.png" alt="Stanford University" width="176" height="23"></a> 
        
        <nav id="nosession" class="pull-right">
          <ul class="list-unstyled pull-right">
          </ul>
        </nav>
      </div>
    </div> 

    <!--=== Header ===-->
    <div id="header" class="clearfix" role="banner">
      <div class="container">
        <div class="row">
          <div class="col-md-8"> 
            <!-- Logo -->
            <div id="logo" class="clearfix"> <a href="#"><img class="img-responsive" src="assets/lagunita/images/block-s-logo@2x.png"  alt="site logo" /></a> </div>
            <!-- /logo -->
            <div id="signature">
              <div id="site-name"> <a href="/"> <span id="site-name-1">[PROJECT NAME]</span>  </a> </div>
              <div id="site-slogan"><a href="/"><span id="site-name-2">[Project Slogan or Mission]</span></a></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- main content -->
    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
      	<?php
			print getSessionMessages();
		?>

        <!-- Main column -->
        <div id="main-content" class="col-md-9 col-centered" role="main">
          <iframe frameborder="0" width="100%" height="1000" scrolling="auto" name="consentForm" src="http://redcap.stanford.edu/surveys/?s=etBtgx4h2h"></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- BEGIN footer -->
<div id="footer" class="clearfix footer" role="contentinfo"> 
  <!-- Global footer snippet start -->
  <div id="global-footer">
    <div class="container">
      <div class="row">
        <img src="https://med.stanford.edu/etc/clientlibs/sm/base/images/footer-logos.png"/>
      </div>
    </div>
  </div>
</div>
<!-- END footer -->
</body>
</html>
<script src="https://fb.me/react-0.14.1.min.js"></script>
<script src="https://fb.me/react-dom-0.14.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.8.23/browser.min.js"></script>
<script type="text/babel">
// REACT COMPONENTS HERE
	// var NavBar = React.createClass({
	// 	getInitialState : function(){
	// 		return ({
	// 			unLoggedIn: true
	// 		});
	// 	},

	// 	render: function() {
	// 		return (
	// 			<div className="container">
	// 				<a className="main_logo" href={this.props.baseurl}></a>
	// 				<div className="menu">
	// 					{(
	// 						true 
	// 						? <div><a href="login.php">Login</a> | <a href="register.php">Register</a></div>
	// 						: <div>
	// 							<a href="login.php">irvins@stanford.edu +</a> 
	// 							<ul>
	// 							<li>Account Status : Active</li>
	// 							<li>Update Password</li>
	// 							</ul>
	// 						 </div>
	// 					)}
	// 				</div>
	// 			</div>
	// 		);
	// 	}
	// });

	// ReactDOM.render(
	// 	<NavBar baseurl="http://webtools.irvins.local/portal/"/>,
	// 	document.getElementById('navbar')
	// );
</script>

