<?php
	/*
		THIS IS A REDCAP PORTAL BASED LOOSELY ON UserPie Version: 1.0 (http://userpie.com)
		
	WEB URLS:
	www.myserver.com/										WEB_ROOT_URL
	www.myserver.com/project_a/index.html			WEB_PORTAL_URL	(home of index page)
	www.myserver.com/project_a/redcap_portal/		WEB_PORTAL
	
	
	*/
	
	include 'my_settings.php';
	
/*
	//General Settings
	//--------------------------------------------------------------------------
	
	// REDCap API Url
	defined('REDCAP_API_URL')				|| define('REDCAP_API_URL',				'http://redcap.localhost.com/api/');
	defined('REDCAP_API_TOKEN')			|| define('REDCAP_API_TOKEN',			'DF49692ADECA617BF740');
	defined('REDCAP_FIRST_FIELD')			|| define('REDCAP_FIRST_FIELD',			'user_id');
	defined('HASH_RANDOM_CODE')			|| define('HASH_RANDOM_CODE',				'hPwEXl8sdGtgRGr');
	defined('LOG_FILE')						|| define('LOG_FILE',						'/Users/andy123/Documents/local REDCap server/webtools/redcap_portal.log');
	defined('SESSION_TIMEOUT')				|| define('SESSION_TIMEOUT',				60*30); // Session timeout in seconds
	defined('SESSION_NAME')					|| define('SESSION_NAME',					'REDCAP_PORTAL'); // Session variable name
	defined('PASS_TOKEN_EXPIRY')			|| define('PASS_TOKEN_EXPIRY',			60); // Number of minutes a forgotten password reset is valid
	defined('PASSWORD_MIN_LENGTH')		|| define('PASSWORD_MIN_LENGTH',			8); // Minimum number of characters for the password
	defined('EMAIL_ACTIVATION_EXPIRY')	|| define('EMAIL_ACTIVATION_EXPIRY',	3); // Number of minutes to wait before re-emailing an activation link
	defined('PORTAL_MODEL_PATH')			|| define('PORTAL_BASE_PATH',				dirname(__FILE__)); // local dir for this file
	defined('PORTAL_INC_PATH')				|| define('PORTAL_INC_PATH',				PORTAL_BASE_PATH."/inc"); // path to include files
	defined('GOOGLE_RECAPTCHA_SECRET')  || define('GOOGLE_RECAPTCHA_SECRET' 	  '-_QnKL_6nfjYVH5l1JpDdwx'); 


	
	// REDCap Field Map (allows you to set different redcap field names for this portal instance)
	$redcap_field_map = array(
		//INTERNAL ID			//REDCAP FIELD NAMES
		'user_id'				=>	REDCAP_FIRST_FIELD,
		'username' 				=>	'portal_username',
		'password' 				=>	'portal_password',
		'salt'					=> 'portal_salt',
		'email' 				=>	'portal_email',
		'active' 				=>	'portal_active___1',
		'suspended' 			=>	'portal_suspended___1',
		'email_verified'		=>	'portal_email_verified___1',
		'email_verified_ts'		=>	'portal_email_verified_ts',
		'email_act_token' 		=>	'portal_email_act_token',
		'email_act_sent_ts' 	=>	'portal_email_act_sent_ts',
		'pass_reset_req_ts'		=>	'portal_pass_reset_req_ts',
		'pass_reset_token' 		=>	'portal_pass_reset_token',
		'pass_reset_question' 	=>	'portal_pass_reset_question',
		'pass_reset_answer' 	=>	'portal_pass_reset_answer',
		'pass_reset_question2' 	=>	'portal_pass_reset_q2',
		'pass_reset_answer2' 	=>	'portal_pass_reset_a2',
		'pass_reset_question3' 	=>	'portal_pass_reset_q3',
		'pass_reset_answer3' 	=>	'portal_pass_reset_a3',
		'created_ts' 			=>	'portal_created_ts',
		'last_login_ts' 		=>	'portal_last_login_ts',
		'log' 					=>	'portal_log'
	);
	
	$template_security_questions = array(
		'concert'	=> 'What was the first concert you attended?',
		'cartoon'	=> 'What was your favorite cartoon series as a child?',
		'reception'	=> 'What was the name of the place your wedding receiption was held?',
		'sib_nick'	=> 'What was the nickname of your oldest sibling as a child?',
		'street'	=> 'What street did you live in on 3rd grade?',
		'pet'		=> 'What was the name of your first pet?',
		'parents'	=> 'In what town did your mother and father meet?',
		'grammie'	=> 'What is your maternal grandmother\'s Nickname?',
		'boss'		=> 'What was the name of your first boss at work?',
		'sib_mid'	=> 'What is your oldest sibling\'s middle name?'
	);
	
	// This array contains the names of the fields used to store the Q/A pairs
	$password_reset_pairs = array(
		1 => array(
			'question' => 'pass_reset_question',
			'answer'	=>	'pass_reset_answer'),
		2 => array(
			'question' => 'pass_reset_question2',
			'answer'	=>	'pass_reset_answer2'),
		3 => array(
			'question' => 'pass_reset_question3',
			'answer'	=>	'pass_reset_answer3')
	);
	// This is the number of questions that must be answered correctly to reset your password
	$password_reset_pairs_min_correct = max(count($password_reset_pairs) - 1, 1);
	
	$portal_config = array(
		'validateEmail' 			=> true,	// Force users to verify their email address prior to using the portal
		'useEmailAsUsername' 	=> true	// Will hide the username field during registration and make it the same as the email field
	);
	
	if(!isset($language)) $langauge = "en";
	
	//Generic website variables
	$websiteName = "My Test Portal Site";
	$websiteUrl = "http://webtools.localhost.com/portal/"; //including trailing slash
	
	//Tagged onto our outgoing emails
	$emailAddress = "noreply@stanford.edu";
	
	//Date format used on email's
	$emailDate = date("l \\t\h\e jS");
	
	//Directory where txt files are stored for the email templates.
	$mail_templates_dir = "models/mail-templates/";
	
	$default_hooks = array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
	$default_replace = array($websiteName,$websiteUrl,$emailDate);
	
	//Display explicit error messages?
	$debug_mode = true;
	
	//Do you wish UserPie to send out emails for confirmation of registration?
	//We recommend this be set to true to prevent spam bots.
	//False = instant activation
	//If this variable is falses the resend-activation file not work.
	//$emailActivation = true;

	//In hours, how long before UserPie will allow a user to request another account activation email
	//Set to 0 to remove threshold
	//$resend_activation_threshold = 1;
	
	//---------------------------------------------------------------------------
*/
?>
