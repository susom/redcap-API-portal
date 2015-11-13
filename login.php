<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	header("Location: $websiteUrl/portal.php"); 
	exit; 
}

//--------------------------------------------------------------------
// Login Posted

if( !empty($_POST) && isset($_POST['new_login']) ) {
	$errors 	= array();
	$username 	= trim($_POST["username"]);
	$password 	= trim($_POST["password"]);

	//Perform some basic validation
	if($username == "") $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	if($password == "") $errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");

	//End data validation
	if(count($errors) == 0) {
		// Continue with authentication
		$auth = new RedcapAuth($username,$password);
		
		// Valid credentials
		if($auth->authenticated_user_id != Null) {
			// Log user in
			$loggedInUser = new RedcapPortalUser($auth->authenticated_user_id);
			setSessionUser($loggedInUser);

			//Redirect to user account page
			$destination = getSessionRedirectOr('index.php');
			$location_redirect = $destination;
		} else { // Invalid credentials
			//IF NOT A REGISTERED USER - KEEP EMAIL AND PREFILL ON REGISTER FORM
			$_SESSION[SESSION_NAME]['new_username'] = $username;
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
			$location_redirect = $websiteUrl;
		}
	} // Validation
	
	// Add errors messages to session
	foreach ($errors as $error) {
		addSessionAlert($error);
	}

	header("Location: $location_redirect"); 
	exit; 
} // POST