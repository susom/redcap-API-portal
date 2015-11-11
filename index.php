<?php
require_once("models/config.php");
$pg_title = "Login | $websiteName";

if(isUserLoggedIn()){
  include(PORTAL_INC_PATH."/learn_functions.php");

  requireActiveUserAccount(); // Only allow 'active' users that presumably have met all requirements

  $activeuser = $loggedInUser->getEmail();
  $record     = $loggedInUser->user_id;

  //seem to expect the event name and NOT an array of event ids.
  define('EVENT_1', 'enrollment_arm_1');
  define('EVENT_2', 'follow_up_1_arm_1'); 
  define('EVENT_3', 'follow_up_2_arm_1'); //235

  $learn_forms = array(
     "informed_consents"                  =>  array("label" =>"Informed consent"),
     "demographics"                       =>  array("label" =>"Demographics"),
     "healthcare_provider_information"    =>  array("label" =>"Healthcare Provider Information", "retake"=>TRUE),
     "lymphatic_diagnosis"                =>  array("label" =>"Lymphatic Diagnosis", "retake"=>TRUE),
     "tissue_bank_genetics"               =>  array("label" =>"Tissue Bank Genetics", "retake"=>TRUE),
     "medical_and_surgical_history"       =>  array("label" =>"Medical and Surgical History", "retake"=>TRUE),
     "lymphatic_signs_and_symptoms"       =>  array("label" =>"Lymphatic Signs and Symptoms", "retake"=>TRUE),
     "pregnancy_form"                     =>  array("label" =>"Pregnancy Form"),
     "quality_of_life_in_the_face_of_lymphatic_disease" =>  array("label" =>"Quality of Life in the Face of Lymphatic Disease", "retake"=>TRUE),
     "lymphatic_treatment_and_procedures" =>  array("label" =>"Lymphatic Treatment and Procedures"),
     "family_members_general"             =>  array("label" =>"Family Members General"),
     "family_members_your_generation_siblings"          =>  array("label" =>"Family Members Your Generation Siblings"),
     "family_members_children"            =>  array("label" =>"Family Members Subsequent Generations"),
     "family_members_other_affected_relatives"          =>  array("label" =>"Family Members Other Affected Relatives"),
     "feedback_survey"                    =>  array("label" =>"Feedback Survey")
  );

  $followup_forms = array(
     "follow_up_demographics"         =>  array("label" =>"Followup Demographics"),
     "follow_up_family_members"       =>  array("label" =>"Followup Family Members"),
     "follow_up_healthcare_provider"  =>  array("label" =>"Followup Healthcare Provider Information"),
     "follow_up_lymphatic_diagnosis"  =>  array("label" =>"Followup Lymphatic Diagnosis"),
     "follow_up_tissue_bank_genetics" =>  array("label" =>"Followup Tissue Bank Genetics"),
     "follow_up_medical_and_surgical_history" =>  array("label" =>"Followup Medical and Surgical History")
  );
}else{
  //IF NOT LOGGED IN
  $username_label       = $portal_config['useEmailAsUsername'] ? "Email" : "Username";
  $username_validation  = $portal_config['useEmailAsUsername'] ? "required: true, email: true" : "required: true";
  $username_block       = $validation_rules = '';
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
<title><?php echo $pg_title?></title>
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
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
 -->
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


<script>
var start_time = new Date().getTime();

$.ajax({
  url: "http://redcap.irvins.local/api/",

  cache: false,
  success: function(data) {
    var request_time = new Date().getTime() - start_time;

    console.log(request_time/1000 + " seconds", data);
  }.bind(this),
  error: function(xhr, status, err) {
    console.error("http://redcap.irvins.local/api/", status, err.toString());
  }.bind(this)
});

</script>
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
<body class="site-slogan">
<div id="su-wrap">
  <div id="su-content">
    <div id="brandbar">
      <div class="container"> 
        <a class="pull-left som_logo" href="http://www.stanford.edu"><img src="assets/lagunita/images/brandbar_logo_som.png" alt="Stanford University" width="176" height="23"></a> 
        
        <nav class="pull-right">
          <ul class="list-unstyled pull-right">
            <?php
            if(isUserLoggedIn()){
              $isactive = (isUserActive() ? "Acount is active." : "Account is inactive.");
              $emailver = ($loggedInUser->isEmailVerified() ? "Email verified on " . date('d-M-Y', strtotime($loggedInUser->email_verified_ts)) . "." : "Email not verified.");
            ?>
            <li class="nav-item pull-left">
                <a href="#" ><?php echo $activeuser ?></a> 
                <div class="nav-item-panel">
                  <aside>
                  <ul>
                  <li><?php echo $isactive?></li>
                  <li><?php echo $emailver?></li>
                  <li><a href="index.php?logout=1">Logout</a></li>
                  </ul>
                  </aside>
                </div>
            </li>
            <?php  } ?>

            <?php
            if(!isUserLoggedIn()){
            ?>
            <li class="nav-item pull-left logreg">
                <a href="#">Login</a> 
                <div class="nav-item-panel">
                  <aside class="loginForm">
                    <form id="loginForm" name="loginForm" class="form-horizontal" action="login.php" method="post" novalidate="novalidate">
                      <div class="fosrm-group">
                        <label for="username" class="control-label"><?=$username_label?></label>
                        <input type="text" class="form-control" name="username" id="username" placeholder="<?=$username_label?>" autofocus="" autocomplete="off" aria-required="true" aria-invalid="true" aria-describedby="username-error">
                        <label for="password" class="control-label">Password:</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off" >
                      </div>

                      <a class="recoverpw pull-left" href="forgot-password.php">Forgot Password?</a>      
                      <input type="submit" class="btn btn-default pull-right" name="new_login" id="newfeedform" value="Login"/>      
      
                    	<div class="footer-links">
                    		<p class="emailus">If you are trying to withdraw and are unable to reset your credentials, please email us at <a href="mailto:lymphaticregistry@stanford.edu">lymphaticregistry@stanford.edu</a> from your portal email account requesting to discontinue participation.</p>
                    	</div>
                    </form>
                  </aside>
                  <aside class="lostPass">
                    <form name="newLostPass" class="form-horizontal" action="" method="post">
                      <label for="username" class="control-label">To begin enter your account email address.</label>
                      <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email Address" autofocus />
                      <div class="g-recaptcha g-recaptcha-center" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div>
                      <div class="footer-links">
                        <a class="login pull-left" href="#">Login</a>       
                        <input type="submit" class="btn btn-default pull-right" name="new_pass_reset_request" id="newfeedform" value="Forgot Password" />
                      </div>
                    </form>
                  </aside>
                </div>
            </li>
            <li class="nav-item pull-left">
                <a href="#" >Register</a> 
                <div class="nav-item-panel">
                  <aside>

                    <h3>Registry Consent Required</h3>  
                    <p>Registration in the registry requires successful completion of our electronic consent process.</p>
                    
                    <form id="newUserForm" name="newUser" class="form-horizontal" action="eligibility.php" method="post">
                      <!-- <div class="g-recaptcha g-recaptcha-right" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div> -->
                      <div class="footer-links">
                        <input type="submit" class="btn btn-default" name="view_consent" id="viewConsent" value="Start Electronic Consent">
                      </div>
                    </form>
                  </aside>
                </div>
            </li>
            <script>
              $(document).ready(function(){
                $("nav .recoverpw, nav a.login").click(function(e){
                  // e.preventDefault();

                  $(".logreg aside").hide();

                  if($(this).hasClass("recoverpw")){
                    $(".lostPass").fadeIn("medium");
                  }else{
                    $(".loginForm").fadeIn("medium");
                  }

                  return false;
                });

                $('#loginForm').validate({
                  rules: {
                    username: {
                      <?php echo $username_validation ?>
                    },
                    password: {
                      required: true
                    }
                  },
                  highlight: function(element) {
                    $(element).closest('.form-group').addClass('has-error');
                  },
                  unhighlight: function(element) {
                    $(element).closest('.form-group').removeClass('has-error');
                  },
                  errorElement: 'span',
                  errorClass: 'help-block',
                  errorPlacement: function(error, element) {
                    if(element.parent('.input-group').length) {
                      error.insertAfter(element.parent());
                    } else {
                      error.insertAfter(element);
                    }
                  }
                });
              });
            </script>
            <?php }?>
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
              <div id="site-name"> <a href="/"> <span id="site-name-1">Well Portal</span>  </a> </div>
              <div id="site-slogan"><a href="/"><span id="site-name-2">Applying the Science of Health and Wellness</span></a></div>
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
        <div id="main-content" class="col-md-9" role="main">
          <!-- <div id="feature-banner" class="margin-bottom-30"> <img class="img-responsive" alt="" src="http://www.stanford.edu/assets/su-images/feature/ht_quad6am.jpg" />
            <div class="feature-caption">
              <h3>Fancy eh</h3>
              <p>The Modified Lagunita Theme is a Stanford-branded HTML theme that can be used for any Stanford-related website.</p>
            </div>
          </div> -->

          <section>
            <?php
            if(isUserLoggedIn()){
            ?>
            <h2 class="headline">Initial Registration</h2>
            <p>Please complete all forms listed below</p>          
            <?php
              if (!informedConsented($record)) {
                $link = getSurveyLink($record, 'consent_forms', EVENT_1);
                echo "<blockquote>Surveys are not available until you have completed the consent form.";
                echo "<a href=\"$link\" class=\"list-group-item \">Informed consent</a></blockquote>";

              } 
            }else{
            ?> 
            <h2 class="headline">Announcements</h2>
            <p>This is a sample area for latest news and announcements. Items below use a "postcard" layout which allows you to float a thumbnail image to the left of your text.</p>          
            <div class="postcard-left">
              <div class="postcard-image"><img src="assets/lagunita/images/samples/QuadArchNPalms270.jpg" alt="" /></div>
              <div class="postcard-text">
                <h3><a href="#">Example Announcement</a></h3>
                <p class="descriptor">March 21, 2014</p>
                <p>Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Aenean imperdiet lobortis libero. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean vitae tortor ligula, quis laoreet ante. Phasellus in turpis ac elit consectetur viverra. Praesent nec massa vitae dui facilisis venenatis et at nisi. Proin fringilla vulputate velit, vel fermentum velit viverra nec.</p>
              </div>
            </div>
            <div class="postcard-left">
              <div class="postcard-image"><img src="assets/lagunita/images/samples/QuadArchNPalms270.jpg" alt="" /></div>
              <div class="postcard-text">
                <h3><a href="#">Example Announcement</a></h3>
                <p class="descriptor">March 1, 2014</p>
                <p>Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Aenean imperdiet lobortis libero. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean vitae tortor ligula, quis laoreet ante. Phasellus in turpis ac elit consectetur viverra. Praesent nec massa vitae dui facilisis venenatis et at nisi. Proin fringilla vulputate velit, vel fermentum velit viverra nec. <a href="#">Download the document</a></p>
              </div>
            </div>
            <?php } ?>
          </section>
        </div>
        <div id="sidebar-second" class="col-md-3">
          <?php if(isUserLoggedIn()){ ?>
            <div class="well">
              <h2>Followup Surveys</h2>
              <p>Please check back in approximately six months to update your records about any new developments in your medical condition that may have occurred.</p> 
              <div class="footer-links">
                <p><a class="more-link" href="#"><i class="glyphicon glyphicon-ban-circle"></i> <span>Withdraw From Registry</span></a></p>
              </div>
            </div>
            <div class="well">
              <h2>Downloads</h2>
              <div class="footer-links">
                <p><a class="more-link" href="#"><i class="glyphicon glyphicon-download-alt"></i> <span>Consent Forms</span></a></p>
              </div>
            </div>
          <?php }else{ ?>
            <div class="well">
              <h2>About Stanford Lagunita</h2>
              <p>Stanford Lagunita is available both as HTML / Dreamweaver templates and as a WordPress theme. It can be used for any Stanford-related website and is ready to use without any additional styling.</p>
              <div class="footer-links">
                <p><a class="more-link" href="http://wordpressthemes.stanford.edu/"><i class="fa fa-chevron-circle-right"></i> <span>See demo WordPress site</span></a></p>
                <p><a class="more-link" href="https://stanford.box.com/lagunita-theme"><i class="fa fa-chevron-circle-right"></i> <span>Download HTML Theme</span></a></p>
              </div>
            </div>
            <div class="well">
              <h2>In the Spotlight</h2>
              <img src="assets/lagunita/images/samples/LemonsNTower1170.jpg" alt="" />
              <p class="caption">This is an optional image caption.</p>
              <p>This is a display element called a "well".  It can be used in either sidebar to highlight content in a shaded box.</p>
              <p>A <em>well</em> is perfect for featuring an event or calling out highlighted information.</p>
                
              <div class="footer-links">
                <p><a class="more-link" href="#"><i class="fa fa-chevron-circle-right"></i> <span>More information</span></a></p>
              </div>
            </div>
          <?php } 


          ?>
        </div>
      </div>
    </div>
  </div>
</div>

<form id="resumeSurveyForm" method="post" action="">
<input type="hidden" id="__code" name="__code" />
</form>

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
<script>
$(".nav-item > a").click(function(){
  $(".nav-item").removeClass("hot");
  if($(this).parent("li").hasClass("hot")){
    $(this).parent("li").removeClass("hot");
  }else{
    $(this).parent("li").addClass("hot");
  }
  return false; 
});

$(document).on('click', function(event) {
  if (!$(event.target).closest('#brandbar nav').length) {
    $(".nav-item").removeClass("hot");
  }
});

function doRedirect(caller) {
  var redirectHash1   = caller.attr('redirect');
  var surveyHash    = caller.attr('hash');
  var link      = caller.attr('link');

  // console.log("THIS IS THE redirectHash" +redirectHash + " and surveyHash" +surveyHash +" and link is " +link);
  $("#__code").val(redirectHash);
  $("#resumeSurveyForm").attr("action", link);
  $("#resumeSurveyForm").submit();

  return;
}
</script>
<!-- <script src="https://fb.me/react-0.14.1.min.js"></script>
<script src="https://fb.me/react-dom-0.14.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.8.23/browser.min.js"></script> -->
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
<?php

$end_time = microtime(true) - $start_time;
print_r($end_time . " seconds");
exit;
?>

