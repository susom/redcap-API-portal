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
		"ACCOUNT_ERROR_TRY_AGAIN"				=> "Try again... ", 
		"ACCOUNT_ERROR_ATTEMPTS"				=> " attempts remaining.",
		"ACCOUNT_ERROR_ATTEMPT"					=> " attempt remaining.", 

		//REGISTER
		"ACCOUNT_REGISTER" 						=> "Register for this Study",
		"ACCOUNT_YOUR_NAME"						=> "Your Name",
		"ACCOUNT_FIRST_NAME" 					=> "First Name",
		"ACCOUNT_LAST_NAME" 					=> "Last Name",
		"ACCOUNT_YOUR_EMAIL" 					=> "Your Email",
		"ACCOUNT_EMAIL_ADDRESS" 				=> "Email Address",
		"ACCOUNT_REENTER_EMAIL" 				=> "Re-enter Email",
		"ACCOUNT_YOUR_LOCATION" 				=> "Your Location",
		"ACCOUNT_CITY" 							=> "City",
		"ACCOUNT_ZIP" 							=> "ZIP",
		"ACCOUNT_ALREADY_REGISTERED" 			=> "Already Registered?",
		"ACCOUNT_BIRTH_YEAR" 					=> "What is your birth year?",
		"ACCOUNT_18_PLUS" 						=> "Are you 18 years old or older?",
		"ACCOUNT_USA_CURRENT" 					=> "Are you currently living in the USA?",
		"ACCOUNT_AGREE" 						=> "By clicking the Submit button I agree to be contacted about WELL for Life related studies and information.",
		"ACCOUNT_ELITE_THANKS" 					=> "Thank you for being one of our first 500 participants. The data we collect will help us improve all our wellbeing!  Display your ribbon proudly! ",
		"STEP_REGISTER"							=> "Register",
		"STEP_VERIFY"							=> "Verify Email",
		"STEP_CONSENT"							=> "Consent",
		"STEP_SECURITY"							=> "Security",

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
		"FORGOTPASS_INVALID_VALUE" 				=> "Invalid password reset values for question",
		"FORGOTPASS_Q_UPDATED" 					=> "Password recovery questions updated!",
		"FORGOTPASS_SEC_Q_SETUP" 				=> "Password setup and security questions",
		"FORGOTPASS_SEC_Q_ANSWERS" 				=> "So that we can help you recover a lost or forgotten password, please provide answers to the following security questions.",
		"FORGOTPASS_CHOSE_QUESTION" 			=> "Choose a question from the list",
		"FORGOTPASS_WRITE_CUSTOM_Q" 			=> "Write a custom security question",

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
	

	//DASHBOARD TRANSLATIONS
	$lang = array_merge($lang, array(
		 "WELL_FOR_LIFE" 							=> "WELL for Life"
		,"MY_DASHBOARD" 							=> "My Dashboard"
		,"CORE_SURVEYS" 							=> "Core Surveys"
		,"LOGOUT" 									=> "Logout"
		,"MY_STUDIES"								=> "My Studies"
		,"MY_PROFILE" 								=> "My Profile"
		,"CONTACT_US" 								=> "Contact Us"
		,"GET_HELP" 								=> "Where to get help"
		,"GET_HELP_TEXT" 							=> "<p>For a medical emergency, call 911 or your healtcare provider.</p><p>For mental health, please visit <a href=\"https://www.mentalhealth.gov/get-help/\" class='offsite'>MentalHealth.gov</a>.</p>"
		,"QUESTION_FOR_WELL" 						=> "Question for WELL"
		,"YOUVE_BEEN_AWARDED" 						=> "You've been awarded a"
		,"GET_WHOLE_BASKET" 						=> "Get the whole fruit basket!"
		,"CONTINUE_SURVEY" 							=> "Continue the rest of the survey."
		,"CONGRATS_FRUITS" 							=> "Congratulations, you got all the fruits! <br/><br/> Check out some of the new modules under 'Learn More'. <br><br/> In the meantime we invite you to watch this video from our WELL for life director. <br/><br/>"
		,"FITNESS_BADGE" 							=> "You've been awarded a fitness badge"
		,"GET_ALL_BADGES" 							=> "Get all the fitness badges!"
		,"CONGRATS_ALL_FITNESS_BADGES"				=> "Congratulations, you got all the fitness badges! <br/> Check back soon for the opportunity to earn new awards!"
		,"DONE_CORE" 								=> "All done with core surveys!"
		,"TAKE_BLOCK_DIET" 							=> "Take the Block diet assessment, free to WELL participants.  This survey typically takes 30-50 minutes to complete and provides instant feedback."
		,"HOW_WELL_EAT" 							=> "How well do you eat?"
		,"COMPLETE_CORE_FIRST" 						=> "Please complete Core Survyes first"
		,"PLEASE_COMPLETE" 							=> "Please complete "
		,"WELCOME_TO_WELL" 							=> "<b>Wellcome</b> to WELL for Life! <u>Click here</u> to start your adventure here…</a>"
		,"WELCOME_BACK_TO" 							=> "<b>Wellcome Back</b> to WELL for Life!</a>"
		,"REMINDERS" 								=> "Reminders"
		,"ADDITIONAL_SURVEYS" 						=> "Additional Surveys"
		,"SEE_PA_DATA" 								=> "Fill out the 'Your Physical Activity' part of the survey to see your data graphed here!"
		,"HOW_DO_YOU_COMPARE" 						=> "How Do You Compare With Other Survey Takers?"
		,"SITTING" 									=> "Sitting"
		,"WALKING" 									=> "Walking"
		,"MODACT" 									=> "Moderate Activity"
		,"VIGACT" 									=> "Vigorous Activity"
		,"NOACT" 									=> "Light/No Activity"
		,"SLEEP" 									=> "Sleep"
		,"YOU_HOURS_DAY"							=> "You (Hours/Day)"
		,"AVG_ALL_USERS" 							=> "Average All Users (Hours/Day)"
		,"HOW_YOU_SPEND_TIME" 						=> "How You Spend Your Time Each Day"
		,"SUNRISE" 									=> "Sunrise"
		,"SUNSET" 									=> "Sunset"
		,"WIND" 									=> "wind"
		,"DASHBOARD"								=> "Dashboard"
		,"WELCOME_BACK"								=> "Welcome Back"
		,"SUBMIT"									=> "Submit"
		,"SAVE_EXIT"								=> "Save and Exit"
		,"SUBMIT_NEXT"								=> "Submit/Next"
		,"MAT_DATA_DISCLAIM" 						=> "The following data has been prepared in part by utilizing information from previous studies on cardiorespiratory fitness and national standards for health. These results are not intended as a substitute for recommendations or advice from a healthcare provider. Talk to your doctor before making any changes that could affect your health."
		,"MAT_SCORE_40"								=> "In the next 4 years, people with your score are very likely (6.6 out of 10) to lose the ability to do active things they enjoy or value.  However, there are many things you can do to improve your functional capacity."
		,"MAT_SCORE_50"								=> "In the next 4 years, people with your score are likely (5.2 out of 10) to lose the ability to do active things they enjoy or value. However, there are many things you can do to improve your functional capacity."
		,"MAT_SCORE_60"								=> "In the next 4 years, people with your score are reasonably likely (3.5 out of 10) to lose the ability to do active things they enjoy or value. However, there are many things you can do to improve your functional capacity."
		,"MAT_SCORE_70"								=> "People with your score are not very likely to lose the ability to do active things they enjoy or value! Keep up the good work and try to maintain your functional capacity!"
	));

	$template_security_questions = array(
			'concert'	=> 'What was the first concert you attended?',
			'cartoon'	=> 'What was your favorite cartoon series as a child?',
			'reception'	=> 'What was the name of the place your wedding reception was held?',
			'sib_nick'	=> 'What was the nickname of your oldest sibling as a child?',
			'street'	=> 'What street did you live in on 3rd grade?',
			'pet'		=> 'What was the name of your first pet?',
			'parents'	=> 'In what town did your mother and father meet?',
			'grammie'	=> 'What is your maternal grandmother\'s Nickname?',
			'boss'		=> 'What was the name of your first boss at work?',
			'sib_mid'	=> 'What is your oldest sibling\'s middle name?',
			'custom'	=> ''
		);

	$websiteName = "WELL for Life initiative";
?>