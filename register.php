<?php
require_once("models/config.php");
$pg_title = "Login | $websiteName";

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	header("Location: $websiteUrl"); 
	exit; 
}

$username_label 	= "";
$badlogin 			= "";

// Process New User Request
if(!empty($_POST['submit_new_user'])){
$mail = new userPieMail();

	$errors 		= array();
	$email 			= trim($_POST["username"]);

	// use the email as the username if configured
	$username 		= $portal_config['useEmailAsUsername'] ? $email : trim($_POST["username"]);
	$password 		= md5("somelongthingsurewhynot" + $username);
	
	$fname 		= (isset($_POST["firstname"]) ?$_POST["firstname"] : null ) ;
	$lname 		= (isset($_POST["lastname"]) ?$_POST["lastname"] : null) ;
	$zip 		= (isset($_POST["zip"]) ?$_POST["zip"] :null ) ;
	$city 		= (isset($_POST["city"]) ?$_POST["city"] :null ) ;
	$state 		= (isset($_POST["state"]) ?$_POST["state"]: null) ;
	$nextyear 	= (isset($_POST["nextyear"]) ?$_POST["nextyear"] :null ) ;
	$oldenough 	= (isset($_POST["oldenough"]) ?$_POST["oldenough"] : null) ;
	$optin 		= (isset($_POST["optin"]) ?$_POST["optin"] :null ) ;


	if($oldenough && $nextyear && $optin){
		echo json_encode(array("pass"=>true));
		$mail->sendMail($email,"New Registration Activation");
		addSessionMessage( lang("ACCOUNT_NEW_ACTIVATION_SENT") );
	}else{

		echo json_encode(array("pass"=>false));

		addSessionMessage( "Talk to you soon!" );
	}


	// Verify reCaptcha
	$reCaptcha = verifyReCaptcha();

	// if ($reCaptcha['success'] != true) {
	// 	$errors[] = "Invalid reCaptcha response - please try again.";
	// }
	
	// if(minMaxRange(5,50,$username)){
	// 	$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,50));
	// }
	
	// if(minMaxRange(8,50,$password)){
	// 	$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	// }else if($password != $password_again){
	// 	$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	// }

	// if(!isValidemail($email)){
	// 	$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	// }


	//End data validation
	if(count($errors) == 0){
		//Construct a user auth object
		$auth = new RedcapAuth($username,NULL,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if($auth->emailExists()){
			// $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
		}elseif($auth->usernameExists()){
			// $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
		}else{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if($auth->createNewUser($password)){
				addSessionMessage(lang('ACTIVATION_MESSAGE'));
				// Redirect to profile page to complete registration
				$loggedInUser = new RedcapPortalUser($auth->new_user_id);
				// setSessionUser($loggedInUser);

			}else{
				// $errors[] = !empty($auth->error) ? $auth->error : 'Unknown error creating user';



			}
		}
	}

	// Add alerts to session for display
	foreach ($errors as $error) {
		addSessionAlert($error);
	}
	exit;
}


$username_validation  = $portal_config['useEmailAsUsername'] ? "required: true, email: true" : "required: true";
include("models/inc/gl_header.php");
?>
<body class="login">
<div id="su-wrap">
<div id="su-content">

    <div id="brandbar"></div> 

    <!-- main content -->
    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
		<a href="index.php"><img src="assets/img/Stanford_Medicine_logo-web-CS.png" id="logo"/></a>
		<?php
		  print getSessionMessages();
		?>

		<!-- Main column -->
		<div id="main-content" class="col-md-8 col-md-offset-2 registerAccount" role="main">
		  <?php
		  	include("models/inc/form_register.php");
		  ?>
		</div>
      </div>
    </div>
</div>
</div>
</body>
<?php 
include("models/inc/gl_footer.php");
?>