<?php
	/*
		UserPie Langauge File.
		Language: English.
	*/
	
	/*
		%m1% - Dymamic markers which are replaced at run time by the relevant index.
	*/

	$lang = array();
	
	//Account
	$lang = array_merge($lang,array(
		"ACCOUNT_SPECIFY_F_L_NAME" 				=> "Please enter your First and Last name",
		"ACCOUNT_SPECIFY_USERNAME" 				=> "Please enter your username",
		"ACCOUNT_SPECIFY_PASSWORD" 				=> "Please enter your password",
		"ACCOUNT_SPECIFY_EMAIL"					=> "Please enter your email address",
		"ACCOUNT_INVALID_EMAIL"					=> "Invalid email address",
		"ACCOUNT_INVALID_USERNAME"				=> "Invalid username",
		"ACCOUNT_EMAIL_MISMATCH"				=> "Emails must match",
		"ACCOUNT_USER_OR_EMAIL_INVALID"			=> "username or email address is invalid",
		"ACCOUNT_USER_OR_PASS_INVALID"			=> "Email and/or Password Not Recognized.",
		"ACCOUNT_ALREADY_ACTIVE"				=> "Your account is already activatived",
		"ACCOUNT_INACTIVE"						=> "Your account is in-active. Check your emails / spam folder for account activation instructions",
		"ACCOUNT_USER_CHAR_LIMIT"				=> "Your username must be no fewer than %m1% characters or greater than %m2%",
		"ACCOUNT_PASS_CHAR_LIMIT"				=> "Your password must be no fewer than %m1% characters or greater than %m2%",
		"ACCOUNT_PASS_MISMATCH"					=> "passwords must match",
		"ACCOUNT_USERNAME_IN_USE"				=> "username %m1% is already in use",
		"ACCOUNT_EMAIL_IN_USE_ACTIVE"			=> "Email %m1% is already in use. If you have forgotten your password, you may reset it from the <a href='login.php'>Login Form</a>",
		"ACCOUNT_LINK_ALREADY_SENT"				=> "An activation email has already been sent to this email address in the last %m1% hour(s)",
		"ACCOUNT_NEW_ACTIVATION_SENT"			=> "Thank you for registering with WELL!  We have sent an account activation link to your email.  Please check your email and click the link inside.",
		"ACCOUNT_NOW_ACTIVE"					=> "Your account is now active",
		"ACCOUNT_SPECIFY_NEW_PASSWORD"			=> "Please enter your new password",	
		"ACCOUNT_NEW_PASSWORD_LENGTH"			=> "New password must be no fewer than %m1% characters or greater than %m2%",	
		"ACCOUNT_PASSWORD_INVALID"				=> "Current password doesn't match the one we have one record",	
		"ACCOUNT_EMAIL_TAKEN"					=> "This email address is already taken by another user",
		"ACCOUNT_DETAILS_UPDATED"				=> "Account details updated",
		"ACTIVATION_MESSAGE"					=> "You will need first activate your account before you can login, follow the below link to activate your account. \n\n%m1%register.php?uid=%m3%&activation=%m2%",							
		"ACCOUNT_REGISTRATION_COMPLETE_TYPE1"	=> "You have successfully registered. You can now login <a href=\"login.php\">here</a>.",
		"ACCOUNT_REGISTRATION_COMPLETE_TYPE2"	=> "Thank you for registering.  Please fill out this short eligibility survey.",
		"ACCOUNT_NOT_YET_ELIGIBLE"				=> "Thank you for you interest in WELL!  You are not eligible to participate at this time. %m1% We will contact you about WELL related studies and information as we expand.",
		"ACCOUNT_NEED_LOCATION"					=> "Please enter your Zip Code or City",
		"ACCOUNT_TOO_YOUNG"						=> "You are not yet 18 years of age.",
		"ACCOUNT_NOT_IN_GEO"					=> "You do not live in a participating County.",
		"ACCOUNT_TOO_NEW_GEO"					=> "You have lived in the participating County for less than one year.",

	));
	
	//Forgot password
	$lang = array_merge($lang,array(
		"FORGOTPASS_SUGGEST"					=> "Click on the 'Forgot Password?' to reset your password.  Or <a href=\"register.php\">register here</a>.",
		"FORGOTPASS_INVALID_TOKEN"				=> "Invalid token",
		"FORGOTPASS_NEW_PASS_EMAIL"				=> "Please check your email account for instructions on how to reset your forgotten password.  Be sure to check your spam/junk folders if you do not see the message in the next few minutes.",
		"FORGOTPASS_REQUEST_CANNED"				=> "Lost password request cancelled",
		"FORGOTPASS_REQUEST_EXISTS"				=> "A forgotten password authorization email was sent %m1% min ago.<br>Please check your email or try again later.",
		"FORGOTPASS_REQUEST_SUCCESS"			=> "Password reset process initiated.<br>Please check your email for further instructions.",
	));
	
	//Miscellaneous
	$lang = array_merge($lang,array(
		"CONFIRM"								=> "Confirm",
		"DENY"									=> "Deny",
		"SUCCESS"								=> "Success",
		"ERROR"									=> "Error",
		"NOTHING_TO_UPDATE"						=> "Nothing to update",
		"SQL_ERROR"								=> "Fatal SQL error",
		"MAIL_ERROR"							=> "Fatal error attempting mail, contact your server administrator",
		"MAIL_TEMPLATE_BUILD_ERROR"				=> "Error building email template",
		"MAIL_TEMPLATE_DIRECTORY_ERROR"			=> "Unable to open mail-templates directory. Perhaps try setting the mail directory to %m1%",
		"MAIL_TEMPLATE_FILE_EMPTY"				=> "Template file is empty... nothing to send",
		"FEATURE_DISABLED"						=> "This feature is currently disabled",
	));
?>