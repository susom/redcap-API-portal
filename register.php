<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	$destination = (isUserActive() ? $websiteUrl . "dashboard/index.php" : $websiteUrl . "consent.php");
	header("Location: " . $destination);
	exit; 
}

//ELIGIBLE CITY/ZIP COMBINATIONS
include("models/inc/city_zips.php");
$username_label 	= "";
$badlogin 			= "";
$eligible_zips 		= array();
foreach($city_zips as $city => $zips){
	$eligible_zips 	= array_merge($eligible_zips, $zips);
}
$eligible_map		= json_encode($city_zips);

// PROCESS NEW USER
if(!empty($_POST['submit_new_user'])){
	$errors 	= array();
	$email 		= trim($_POST["username"]);
	$emailchek 	= trim($_POST["usernametoo"]);

	// use the email as the username if configured
	$username 	= $portal_config['useEmailAsUsername'] ? $email : trim($_POST["username"]);
	$password 	= md5("somelongthingsurewhynot" + $username); //USE A TEMP PASSWORD FOR NOW
	
	$fname 		= (!empty($_POST["firstname"]) 	? $_POST["firstname"] : null ) ;
	$lname 		= (!empty($_POST["lastname"]) 	? $_POST["lastname"] : null) ;
	$zip 		= (!empty($_POST["zip"]) 		? intval($_POST["zip"]) :null ) ;
	$city 		= (!empty($_POST["city"]) 		? ucwords($_POST["city"]) :null ) ;
	$state 		= (isset($_POST["state"]) 		? $_POST["state"]: null) ;

	$nextyear 	= (isset($_POST["nextyear"]) 	? $_POST["nextyear"] 	:null ) ;
	$oldenough 	= (isset($_POST["oldenough"]) 	? $_POST["oldenough"] 	: null) ;
	$birthyear 	= (isset($_POST["birthyear"]))  ? intval($_POST["birthyear"]) : null;
	$optin 		= (isset($_POST["optin"]) 		? $_POST["optin"] 		:null ) ;
	$actualage 	= (!$birthyear ? null : date("Y") - $birthyear);

	//VALIDATE STUFF (matching valid emails, nonnull fname, lastname, zip or city)
	if(is_null($fname) || is_null($lname)){
		$errors[] = lang("ACCOUNT_SPECIFY_F_L_NAME");
	}

	if($email != $emailchek){
		$errors[] = lang("ACCOUNT_EMAIL_MISMATCH");
	}elseif(!isValidemail($email)){
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}

	// if(is_null($zip) && is_null($city)){
	// 	$errors[] = lang("ACCOUNT_NEED_LOCATION");
	// }

	//End data validation
	if(count($errors) == 0){
		//Construct a user auth object
		$auth = new RedcapAuth($username,NULL,$email, $fname, $lname, $zip, $city,$state, $actualage);

		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if($auth->emailExists()){
			$tempu 		= getUserByEmail($email);
			$olduser 	= new RedcapPortalUser($tempu->user_id);
			if($olduser->isActive()){
				//CURRENT ACCOUNT + ACTIVE (LINK ALREADY CLICKED)
				$errors[] = lang("ACCOUNT_EMAIL_IN_USE_ACTIVE",array($email));
			}else{
				//CURRENT ACCOUTN NOT ACTIVE
				if($oldenough && $optin && $actualage >= 18){
					//WAS FORMERLY INELIGIBLE NOW ELIGIBLE, SEND ACTIVATION LINK
					$errors[] = lang("ACCOUNT_NEW_ACTIVATION_SENT",array($email));
					
					//SEND NEW ACTIVATION LINK
					$olduser->updateUser(array(
						getRF("zip") 	=> $zip,
				        getRF("city") 	=> $city,
				        getRF("state") 	=> $state,
				        getRF("age") 	=> $actualage
				      ));
		            $olduser->createEmailToken();
		            $olduser->emailEmailToken();

		            //CLEAN UP
					unset($fname, $lname, $email, $zip, $city);
				}else{
					//WAS FORMERLY AND STILL IS INELIGIBLE
					addSessionMessage( lang("ACCOUNT_NOT_YET_ELIGIBLE",array("")), "notice" );
				}
			}
		}else{
			//IF THEY DONT PASS ELIGIBILITY THEN THEY GET A THANK YOU , BUT NO ACCOUNT CREATION 
			//BUT NEED TO STORE THEIR STUFF FOR CONTACT
			if($oldenough && $optin && $actualage >= 18){
				//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
				if($auth->createNewUser($password)){
					addSessionMessage( lang("ACCOUNT_NEW_ACTIVATION_SENT"), "success");
					
					// THEY WILL NOW NEED TO VERIFY THEIR EMAIL LINK
					$loggedInUser = new RedcapPortalUser($auth->new_user_id);
				}else{
					$errors[] = !empty($auth->error) ? $auth->error : 'Unknown error creating user';
				}
			}else{
				//ADD THEIR EMAIL , NAME TO CONTACT DB
				$auth->createNewUser($password, FALSE);

				$reason 	= "";
				if(!$oldenough || $actualage < 18){
					$reason = lang("ACCOUNT_TOO_YOUNG");
				}

				addSessionMessage( lang("ACCOUNT_NOT_YET_ELIGIBLE",array("")), "notice" );
			}

			//CLEAN UP
			unset($fname, $lname, $email, $zip, $city);
		}
	}

	// Add alerts to session for display
	foreach ($errors as $error) {
		addSessionAlert($error);
	}

}elseif(!empty($_GET['activation']) && !empty($_GET['uid'])){
	$uid 		= $_GET['uid'];
	$activation = $_GET['activation'];

	$newuser 	= new RedcapPortalUser($uid);
	if($newuser->isEmailTokenValid($activation)){
		//SET EMAIL = VERIFIED
		$newuser->setEmailVerified();

		//SET USER IN SESSION
		$loggedInUser = new RedcapPortalUser($uid);

		//AT THIS POINT, LOOK THROUGH ANY OTHER PROJECTS IN THE SURVEYS CONFIG
		//THEN GO AHEAD AND CREATE A NEW RECORD ID FOR EACH INSTRUMENT  (logged in user id + p001_1)
		






		setSessionUser($loggedInUser);

		//REDIRECT TO CONSENT
		header("Location: consent.php");
		exit;
	}else{
		// Invalid token match
		$errors[] = "The supplied email activation token is invalid or expired.  This can happen if you regenerated a new token but followed the link from an older request.";
		addSessionAlert("Invalid email activation token");
	}
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