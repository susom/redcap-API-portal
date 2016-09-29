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
		//ERROR AND POPUP
		"ACCOUNT_SPECIFY_F_L_NAME" 				=> "Please enter your First and Last name",
		"ACCOUNT_SPECIFY_USERNAME" 				=> "Please enter your username",
		"ACCOUNT_SPECIFY_PASSWORD" 				=> "Please enter your password",
		"ACCOUNT_SPECIFY_EMAIL"					=> "Please enter your email address",
		"ACCOUNT_INVALID_EMAIL"					=> "Invalid email address",
		"ACCOUNT_EMAIL_MISMATCH"				=> "Emails must match",
		"ACCOUNT_USER_OR_PASS_INVALID"			=> "Email and/or Password Not Recognized.",
		"ACCOUNT_PASS_MISMATCH"					=> "passwords must match",
		"ACCOUNT_EMAIL_IN_USE_ACTIVE"			=> "Email %m1% is already in use. If you have forgotten your password, you may reset it from the <a href='login.php'>Login Form</a>",
		"ACCOUNT_NEW_ACTIVATION_SENT"			=> "Thank you for registering with the WELL for Life initiative!  We have sent an account activation link to your email.  Please check your email and click the link inside. If you do not recieve the email within 1 hour, contact us at wellforlife@stanford.edu",
		"ACCOUNT_SPECIFY_NEW_PASSWORD"			=> "Please enter your new password",	
		"ACCOUNT_NOT_YET_ELIGIBLE"				=> "Thank you for you interest in the WELL for Life initiative!  You are not eligible to participate at this time. %m1% We will contact you about WELL for Life related studies and information as we expand.",
		"ACCOUNT_NEED_LOCATION"					=> "Please enter your Zip Code or City",
		"ACCOUNT_TOO_YOUNG"						=> "You are not yet 18 years of age.",
		"ACCOUNT_NOT_IN_USA"					=> "This study is only for participants living in the USA.",
		"ACTIVATION_MESSAGE"					=> "You will need to first activate your account before you can login.  Follow the link below to activate your account. \n\n%m1%register.php?uid=%m3%&activation=%m2%",							
		
		//REGISTER
		"ACCOUNT_REGISTER" 						=> "Register for this Study",
		"ACCOUNT_FIRST_NAME" 					=> "First Name",
		"ACCOUNT_LAST_NAME" 					=> "Last Name",
		"ACCOUNT_YOUR_EMAIL" 					=> "Your Email",
		"ACCOUNT_EMAIL_ADDRESS" 				=> "Email Address",
		"ACCOUNT_REENTER_EMAIL" 				=> "Re-enter Email",
		"ACCOUNT_YOUR_LOCATION" 				=> "Your Location",
		"ACCOUNT_CITY" 							=> "City",
		"ACCOUNT_ZIP" 							=> "ZIP",
		"ACCOUNT_ALREADY_REGISTERED" 			=> "Already Registered?<",
		"ACCOUNT_BIRTH_YEAR" 					=> "What is your birth year?",
		"ACCOUNT_18_PLUS" 						=> "Are you 18 years old or older?",
		"ACCOUNT_USA_CURRENT" 					=> "Are you currently living in the USA?",
		"ACCOUNT_AGREE" 						=> "By clicking the Submit button I agree to be contacted about WELL for Life related studies and information.",
		"ACCOUNT_ELITE_THANKS" 					=> "Thank you for being one of our first 500 participants. The data we collect will help us improve all our wellbeing!  Display your ribbon proudly! "

		"ACCOUNT_NEW_PASSWORD" 					=> "New Password",
		"ACCOUNT_PASSWORD" 						=> "Password",
		"ACCOUNT_PASSWORD_AGAIN" 				=> "Password Again",

		"ACCOUNT_LOGIN_PAGE" 					=> "Login Page",
		"ACCOUNT_REGISTER_PAGE" 				=> "Register Page",
		
		"REGISTER_STUDY" 						=> "Register for Study",
		"REGISTER_TOKEN_INVALID_1" 				=> "The supplied email activation token is invalid or expired.  This can happen if you regenerated a new token but followed the link from an older request.",
		"REGISTER_TOKEN_INVALID_2" 				=> "Invalid email activation token <br><a class='alink' href='login.php'>Click Here</a> and chose 'Forgot Password' to get a new token.",

		//LOGIN
		"ACCOUNT_LOGIN_CONTINUE" 				=> "Please Login to continue",
		"ACCOUNT_LOGIN_NOW" 					=> "Login Now",
		"ACCOUNT_NEXT_STEP" 					=> "Next Step",
		
		//CONSENT
		"CONSENT_BULLET_1" 						=> "We need your permission before we can ask you any questions, so please read the following Informed Consent Document",
		"CONSENT_BULLET_2" 						=> "The initial survey will take 20-30 minutes to complete – but you don't need to fill it all out at one time",
		"CONSENT_BULLET_3" 						=> "We will check back in with you every few months",
		"CONSENT_BULLET_4" 						=> "We will add new surveys, materials, and content and invite you to participate over time",
		"CONSENT_WELCOME" 						=> "WELCOME!",
		"CONSENT_CONTACT" 						=> "FOR QUESTIONS ABOUT THE STUDY, CONTACT the Protocol Director, John Ioannidis at (650) 725-5465 or the Protocol Co-Director, Sandra Winter at 650-723-8513.",
		"CONSENT_I_AGREE" 						=> "I Agree",
		
		//FORGOT PASSWORD AND ACCOUNT SETUP
		"FORGOTPASS" 							=> "Forgot Password?",
		"FORGOTPASS_RESET" 						=> "Password Reset",
		"FORGOTPASS_RESET_FORM" 				=> "Password Reset Form",
		"FORGOTPASS_PLEASE_ANSWER" 				=> "Please answer your security questions.",
		"FORGOTPASS_RECOVERY_ANSWER" 			=> "Password Recovery Answer",
		"FORGOTPASS_SEC_Q" 						=> "Security Question",
		"FORGOTPASS_ANSWER_QS" 					=> "Answer my security questions",
		"FORGOTPASS_EMAIL_ME" 					=> "Email me a password reset link",
		"FORGOTPASS_RECOVERY_METHOD" 			=> "Chose recovery method",
		"FORGOTPASS_BEGIN_RESET" 				=> "Enter email to begin password reset",
		"FORGOTPASS_SUGGEST"					=> "Click on the 'Forgot Password?' to reset your password.  Or <a href=\"register.php\">register here</a>.",
		"FORGOTPASS_INVALID_TOKEN"				=> "Invalid token.",
		"FORGOTPASS_REQUEST_EXISTS"				=> "A forgotten password authorization email was sent %m1% min ago.<br>Please check your email or try again later.",
		"FORGOTPASS_REQUEST_SUCCESS"			=> "Password reset process initiated.<br>Please check your email for further instructions.",
		"FORGOTPASS_UPDATED" 					=> "Password Updated",
		"FORGOTPASS_INVALID_VALUE" 				=> "Invalid password reset values for question"
		"FORGOTPASS_Q_UPDATED" 					=> "Password recovery questions updated!"
		"FORGOTPASS_SEC_Q_SETUP" 				=> "Please setup your password and security questions"
		"FORGOTPASS_SEC_Q_ANSWERS" 				=> "So that we can help you recover a lost or forgotten password, please provide answers to the following security questions."
		"FORGOTPASS_CHOSE_QUESTION" 			=> "Choose a question from the list"
		"FORGOTPASS_WRITE_CUSTOM_Q" 			=> "Write a custom security question"





		//MAIL
		"MAIL_ERROR"							=> "Fatal error attempting mail, contact your server administrator",
		"MAIL_TEMPLATE_BUILD_ERROR"				=> "Error building email template",
		"MAIL_TEMPLATE_DIRECTORY_ERROR"			=> "Unable to open mail-templates directory. Perhaps try setting the mail directory to %m1%",
		"MAIL_TEMPLATE_FILE_EMPTY"				=> "Template file is empty... nothing to send",

		//Miscellaneous
		"GENERAL_YES" 							=> "Yes",
		"GENERAL_NO" 							=> "No",
		"GENERAL_BACK" 							=> "Back",
		"GENERAL_NEXT" 							=> "Next",
		"GENERAL_SUBMIT" 						=> "Submit",
		"CONFIRM"								=> "Confirm",
		"ERROR"									=> "Error",
	));
?>