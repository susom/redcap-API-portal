<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	$destination = (isUserActive() ? $websiteUrl . "index.php" : $websiteUrl . "consent.php");
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
$lang_req 			= $_SESSION["use_lang"];

$step_one_on		= empty($_GET['step']) ? "on" : "";
$step_two_on 		= !empty($_GET['step']) ? "on" : "";

if(!empty($_GET["msg"])){
	addSessionAlert( $_GET["msg"] );
}

if(isset($_GET["ref"])){
	$linked_proj = base64_decode($_GET["ref"]);
	$_SESSION["linked_project"] = json_decode($linked_proj,1);
}

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
	$in_usa 	= (isset($_POST["in_usa"]) 		? $_POST["in_usa"] 		:null ) ;
	$oldenough 	= (isset($_POST["oldenough"]) 	? $_POST["oldenough"] 	: null) ;
	$birthyear 	= (isset($_POST["birthyear"]))  ? intval($_POST["birthyear"]) : null;
	$optin 		= (isset($_POST["optin"]) 		? $_POST["optin"] 		:null ) ;
	$actualage 	= (!$birthyear ? null : date("Y") - $birthyear);
	$lang_req 	= $_POST["lang_req"];

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
				        getRF("age") 	=> $actualage,
				        getRF("lang") 	=> $lang_req

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
			if($in_usa && $oldenough && $optin && $actualage >= 18){
				//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
				if($auth->createNewUser($password)){
					addSessionMessage( lang("ACCOUNT_NEW_ACTIVATION_SENT"), "success");
					header("Location: register.php?step=2");
					exit;
					// // THEY WILL NOW NEED TO VERIFY THEIR EMAIL LINK
					// $loggedInUser = new RedcapPortalUser($auth->new_user_id);
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

				if(!$in_usa){
					$reason = lang("ACCOUNT_NOT_IN_USA");
				}
				
				addSessionMessage( lang("ACCOUNT_NOT_YET_ELIGIBLE",array($reason)), "notice" );
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
		$supp_proj 		= SurveysConfig::$projects;
		foreach($supp_proj as $proj_name => $project){
			if($proj_name == $_CFG->SESSION_NAME){
				continue;
			}

			$supp_id 					= linkSupplementalProject($project, $loggedInUser,REDCAP_PORTAL_EVENT);
			$loggedInUser->{$proj_name} = $supp_id;
		}
		setSessionUser($loggedInUser);

		//REDIRECT TO CONSENT
		header("Location: consent.php");
		exit;
	}else{
		// Invalid token match
		$errors[] = lang("REGISTER_TOKEN_INVALID_1");
		addSessionAlert( lang("REGISTER_TOKEN_INVALID_2"));
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
		<?php
			include("models/inc/language_select.php");
		?>
		<div class="well row">
		  <ul id="register_steps">
		  	<li class='<?php echo $step_one_on ?>'><span>1</span> <?php echo lang("STEP_REGISTER") ?></li>
		  	<li class='<?php echo $step_two_on ?>'><span>2</span> <?php echo lang("STEP_VERIFY") ?></li>
		  	<li><span>3</span> <?php echo lang("STEP_CONSENT") ?></li>
		  	<li><span>4</span> <?php echo lang("STEP_SECURITY") ?></li>
		  </ul>
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