<?php
require_once("models/config.php");
$pg_title = "Login | $websiteName";

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	header("Location: $websiteUrl"); 
	exit; 
}

// Process New User Request
if(!empty($_POST['submit_new_user'])){
	$errors 		= array();
	$email 			= trim($_POST["username"]);

	// use the email as the username if configured
	$username 		= $portal_config['useEmailAsUsername'] ? $email : trim($_POST["username"]);
	$password 		= trim($_POST["password"]);
	$password_again = trim($_POST["confirmpassword"]);
	
	// Verify reCaptcha
	$reCaptcha = verifyReCaptcha();
	if ($reCaptcha['success'] != true) {
		$errors[] = "Invalid reCaptcha response - please try again.";
	}
	
	if(minMaxRange(5,50,$username)){
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,50));
	}
	
	if(minMaxRange(8,50,$password)){
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}else if($password != $password_again){
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}

	if(!isValidemail($email)){
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}


	//End data validation
	if(count($errors) == 0){
		//Construct a user auth object
		$auth = new RedcapAuth($username,NULL,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if($auth->emailExists()){
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
		}elseif($auth->usernameExists()){
			$errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
		}else{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if($auth->createNewUser($password)){
				addSessionMessage(lang('ACCOUNT_REGISTRATION_COMPLETE_TYPE2'));
				// Redirect to profile page to complete registration
				$loggedInUser = new RedcapPortalUser($auth->new_user_id);
				setSessionUser($loggedInUser);
				header("Location: survey.php?sid=1");die();
			}else{
				$errors[] = !empty($auth->error) ? $auth->error : 'Unknown error creating user';
			}
		}
	}
}
// Add alerts to session for display
foreach ($errors as $error) {
	addSessionAlert($error);
}
  
// Depeding on portal_config, make the username block
$username_block = $validation_rules = '';
include("models/inc/gl_header.php");
?>
<!-- Customization Options:                       
     body class:   "home", "nav-1", "nav-2", etc. - specifies which item in the top nav to underline
                   "site-slogan" - display a site slogan in the header signature
     logo, h1  :   "hide" - hides the logo or H1 element, eg <h1 class="hide">
 -->
<body class="site-slogan eligibility">
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

    <?php
      include("models/inc/project_header.php");
    ?>
    
    <!-- main content -->
    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
        <?php
          print getSessionMessages();
        ?>

        <!-- Main column -->
        <div id="main-content" class="col-md-9" role="main">
          <?php
          	include("models/inc/form_register.php");
          ?>
        </div>

        <div id="sidebar-second" class="col-md-3">
          <div class="well">
            <h2>Keep In Contact</h2>
            <p>Not ready to register for an account yet? Leave your email to get news and updates about our studies:</p>
            <form id="newUserForm" name="newUser" class="form-horizontal" action="eligibility.php" method="post">
              <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email Address" autofocus />
              <input type="submit" class="btn btn-default pull-right" name="view_consent" id="viewConsent" value="Submit Email">
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
  include("models/inc/project_footer.php");
?>
</body>
<?php 
  include("models/inc/gl_footer.php");
?>
