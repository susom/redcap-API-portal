<?php
require_once("models/config.php");
$pg_title = "Login | $websiteName";

if(isUserLoggedIn()){
  include(PORTAL_INC_PATH."/learn_functions.php");

  // requireActiveUserAccount(); // Only allow 'active' users that presumably have met all requirements

  $activeuser = $loggedInUser->getEmail();
  $record     = $loggedInUser->user_id;
}

$username_label       = $portal_config['useEmailAsUsername'] ? "Email" : "Username";
$username_validation  = $portal_config['useEmailAsUsername'] ? "required: true, email: true" : "required: true";
$username_block       = $validation_rules = '';
$bad_login            = (!empty($_SESSION[SESSION_NAME]['new_username']) ? $_SESSION[SESSION_NAME]['new_username'] : null);

include("models/inc/gl_header.php");
?>
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
      </div>
    </div> 
    
    <!-- main content -->
    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
        <div id="main-content" class="col-md-4 col-md-offset-4" role="main">
          <img src="assets/img/Stanford_Medicine_logo-web-CS.png" id="logo"/>
          <div class="well signinup">
            <h2>Well Registry Flows</h2>
            <p>
              <a href="login.php" class="btn btn-primary">Login Page</a>  <a href="register.php" class="btn btn-info">Register Page</a>
            </p>
          </div>  
        </div>
        
      </div>
    </div>
  </div>
</div>
</body>
<?php 
  include("models/inc/gl_footer.php");
?>

