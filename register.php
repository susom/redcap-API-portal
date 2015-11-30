<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	header("Location: $websiteUrl"); 
	exit; 
}

$username_label 	= "";
$badlogin 			= "";
$eligible_zips 		= array(94022,94024,94040,94041,94043,94085,94086,94087,94089,94301,94303,94304,94305,94306,95008,95014,95020,95030,95032,95033,95035,95037,95046,95050,95051,95053,95054,95070,95101,95110,95111,95112,95113,95116,95117,95118,95119,95120,95121,95122,95123,95124,95125,95126,95127,95128,95129,95130,95131,95132,95133,95134,95135,95136,95138,95139,95140,95141,95148,95190,95191,95192,95193,95194,95196);
$eligible_cities 	= array("Alviso","Campbell","Coyote","Cupertino","Gilroy","Holy City","Los Altos","Los Gatos","Milpitas","Morgan Hill","Mount Hamilton","Mountain View","New Almaden","Redqood Estates","San Jose","San Martin","Santa Clara","Saratoga","Stanford","Sunnyvale","Unincorporated Area","None of these cities, I live outside Santa Clara County");
	
// Process New User Request
if(!empty($_POST['submit_new_user'])){
	$errors 	= array();
	$email 		= trim($_POST["username"]);
	$emailchek 	= trim($_POST["usernametoo"]);

	// use the email as the username if configured
	$username 	= $portal_config['useEmailAsUsername'] ? $email : trim($_POST["username"]);
	$password 	= md5("somelongthingsurewhynot" + $username); //put a temp pw thing for now
	
	$fname 		= (!empty($_POST["firstname"]) 	? $_POST["firstname"] : null ) ;
	$lname 		= (!empty($_POST["lastname"]) 	? $_POST["lastname"] : null) ;
	$zip 		= (!empty($_POST["zip"]) 		? intval($_POST["zip"]) :null ) ;
	$city 		= (!empty($_POST["city"]) 		? ucwords($_POST["city"]) :null ) ;
	$state 		= (isset($_POST["state"]) 		? $_POST["state"]: null) ;

	$nextyear 	= (isset($_POST["nextyear"]) 	? $_POST["nextyear"] 	:null ) ;
	$oldenough 	= (isset($_POST["oldenough"]) 	? $_POST["oldenough"] 	: null) ;
	$optin 		= (isset($_POST["optin"]) 		? $_POST["optin"] 		:null ) ;

	//VALIDATE STUFF (matching valid emails, nonnull fname, lastname, zip or city)

	// Verify reCaptcha
	// $reCaptcha = verifyReCaptcha();
	// if ($reCaptcha['success'] != true) {
	// 	$errors[] = "Invalid reCaptcha response - please try again.";
	// }

	if(is_null($fname) || is_null($lname)){
		$errors[] = lang("ACCOUNT_SPECIFY_F_L_NAME");
	}

	if($email != $emailchek){
		$errors[] = lang("ACCOUNT_EMAIL_MISMATCH");
	}elseif(!isValidemail($email)){
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}

	if(is_null($zip) && is_null($city)){
		$errors[] = lang("ACCOUNT_NEED_LOCATION");
	}

	//End data validation
	if(count($errors) == 0){
		//Construct a user auth object
		$auth = new RedcapAuth($username,NULL,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if($auth->emailExists()){
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
		}else{
			//IF THEY DONT PASS ELIGIBILITY THEN THEY GET A THANK YOU , BUT NO ACCOUNT CREATION 
			//BUT NEED TO STORE THEIR STUFF FOR CONTACT
			if($oldenough && $nextyear && $optin){
				//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
				if($auth->createNewUser($password)){
					addSessionMessage( lang("ACCOUNT_NEW_ACTIVATION_SENT"), "success");
					
					// Redirect to profile page to complete registration
					$loggedInUser = new RedcapPortalUser($auth->new_user_id);
					setSessionUser($loggedInUser);
				}else{
					$errors[] = !empty($auth->error) ? $auth->error : 'Unknown error creating user';
				}
			}else{
				addSessionMessage( lang("ACCOUNT_NOT_YET_ELIGIBLE"), "notice" );
			}

			//CLEAN UP
			unset($fname, $lname, $email, $zip, $city);
		}
	}

	// Add alerts to session for display
	foreach ($errors as $error) {
		addSessionAlert($error);
	}
}elseif(!empty($_GET['activation'])){

}

$username_validation  = $portal_config['useEmailAsUsername'] ? "required: true, email: true" : "required: true";

$pg_title 		= "Register | $websiteName";
$body_classes 	= "login register";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
	<div id="main-content" class="col-md-8 col-md-offset-2 registerAccount" role="main">
		<div class="well row">
		  <?php
		  	include("models/inc/form_register.php");
		  ?>
	  	</div>
	</div>
  </div>
</div>
<?php 
include("models/inc/gl_footer.php");
?>