<?php
//MUST SET TIMEZONE
date_default_timezone_set('America/Los_Angeles');

//Display explicit error messages?
$debug_mode = true;

//WHATS THIS FOR?
$custom_surveycomplete_API 	= "http://redcap.irvins.loc/plugins/api_methods/survey_status.php";

//General Settings
//--------------------------------------------------------------------------
defined('REDCAP_API_URL')           || define('REDCAP_API_URL',            'http://redcap.irvins.loc/api/');
defined('REDCAP_API_TOKEN')         || define('REDCAP_API_TOKEN',          '379870900189B71CF04F47F6DC260835');
defined('REDCAP_PORTAL_EVENT')      || define('REDCAP_PORTAL_EVENT',       'enrollment_arm_1'); // Set to NULL if project is classic, otherwise name of event where redcap_fields are stored
defined('REDCAP_PORTAL_EVENT_1')    || define('REDCAP_PORTAL_EVENT_1',       'short_anniversary_arm_1'); // Set to NULL if project is classic, otherwise name of event where redcap_fields are stored
defined('REDCAP_PORTAL_EVENT_2')    || define('REDCAP_PORTAL_EVENT_2',       'anniversary_2_arm_1'); // Set to NULL if project is classic, otherwise name of event where redcap_fields are stored
defined('REDCAP_FIRST_FIELD')       || define('REDCAP_FIRST_FIELD',        'id');
defined('LOG_FILE')                 || define('LOG_FILE',                  '/Users/irvins/Work/log/redcap_portal.log');
defined('SESSION_TIMEOUT')          || define('SESSION_TIMEOUT',           60*30); // Session timeout in seconds
defined('SESSION_NAME')             || define('SESSION_NAME',              'REDCAP_PORTAL'); // Session variable name
defined('PORTAL_BASE_PATH')         || define('PORTAL_BASE_PATH',          dirname(__FILE__)); // local dir for this file
defined('PORTAL_INC_PATH')          || define('PORTAL_INC_PATH',           PORTAL_BASE_PATH."/inc"); // path to include files
defined('PASS_TOKEN_EXPIRY')        || define('PASS_TOKEN_EXPIRY',         60); // Number of minutes a forgotten password reset is valid
defined('PASSWORD_MIN_LENGTH')      || define('PASSWORD_MIN_LENGTH',       8); // Minimum number of characters for the password
defined('EMAIL_ACTIVATION_EXPIRY')  || define('EMAIL_ACTIVATION_EXPIRY',   3); // Number of minutes to wait before re-emailing an activation link
defined('GOOGLE_RECAPTCHA_SECRET')  || define('GOOGLE_RECAPTCHA_SECRET',   '6LcEIQoTAAAAAB9Yh-_QnKL_6nfjYVH5l1JpDdwx'); 
defined('PROJ_ENV')  			   	|| define('PROJ_ENV',   'local'); 

// REDCap Field Map (allows you to set different redcap field names for this portal instance)
$redcap_field_map = array(
  	//INTERNAL ID        	//REDCAP FIELD NAMES
	'id'               	  	=> REDCAP_FIRST_FIELD,
	'username'              => 'portal_username',
	'firstname'             => 'portal_firstname',
	'lastname'              => 'portal_lastname',
	'portal_middlename'		=> 'portal_middlename',
	'portal_nickname'		=> 'portal_nickname',
	'portal_mail_street'	=> 'portal_mail_street',
	'portal_apartment_no'	=> 'portal_apartment_no',
	'portal_pic'			=> 'portal_pic',
	'portal_contact_name' 	=> 'portal_contact_name',
	'portal_contact_phone' 	=> 'portal_contact_phone',
	'zip'              	  	=> 'portal_zip',
	'city'              	=> 'portal_city',
	'state'              	=> 'portal_state',
	'age'              	  	=> 'portal_age',
	'gender'              	=> 'core_gender',
	'password'              => 'portal_password',
	'email'                 => 'portal_email',
	'active'                => 'portal_active___1',
	'suspended'             => 'portal_suspended___1',
	'email_verified'        => 'portal_email_verified___1',
	'email_verified_ts'     => 'portal_email_verified_ts',
	'email_act_token'       => 'portal_email_act_token',
	'email_act_sent_ts'     => 'portal_email_act_sent_ts',
	'pass_reset_req_ts'     => 'portal_pass_reset_req_ts',
	'pass_reset_token'      => 'portal_pass_reset_token',
	'pass_reset_question'   => 'portal_pass_reset_question',
	'pass_reset_answer'     => 'portal_pass_reset_answer',
	'pass_reset_question2'  => 'portal_pass_reset_q2',
	'pass_reset_answer2'    => 'portal_pass_reset_a2',
	'pass_reset_question3'  => 'portal_pass_reset_q3',
	'pass_reset_answer3'    => 'portal_pass_reset_a3',
	'created_ts'            => 'portal_created_ts',
	'last_login_ts'         => 'portal_last_login_ts',
	'log'                   => 'portal_log',
	'consent_ts'            => 'portal_consent_ts',
	'user_bucket'          	=> 'portal_user_bucket',
	'lang'					=> 'portal_lang',
	'linked_projects'		=> 'linked_projects'
	,'user_event_arm'		=> 'user_event_arm'
	,'ffq_generated_ts'		=> 'ffq_generated_ts'
);

// This array contains the names of the fields used to store the Q/A pairs
$password_reset_pairs = array(
	1 => array(
		'question'	=> 'pass_reset_question',
		'answer'	=> 'pass_reset_answer'),
	2 => array(
		'question' 	=> 'pass_reset_question2',
		'answer'	=> 'pass_reset_answer2'),
	3 => array(
		'question' 	=> 'pass_reset_question3',
		'answer'	=> 'pass_reset_answer3')
);
// This is the number of questions that must be answered correctly to reset your password
$password_reset_pairs_min_correct = max(count($password_reset_pairs) - 1, 1);

$portal_config = array(
	'validateEmail' 		=> true,	// Force users to verify their email address prior to using the portal
	'useEmailAsUsername' 	=> true	// Will hide the username field during registration and make it the same as the email field
);

if(!isset($language)) $langauge = "en";

//Generic website variables
$websiteName 				= "WELL for Life initiative";
$websiteUrl 				= "http://webtools.irvins.loc/portal/well2/"; //including trailing slash //http://lymphaticnetwork.org/
$websiteAllowedChildOrigin 	= "http://redcap.irvins.loc";

//Tagged onto our outgoing emails
$emailAddress 				= "wellforlife@stanford.edu";

//Date format used on email's
$emailDate 					= date("m-d-Y");

//Directory where txt files are stored for the email templates.
$mail_templates_dir 		= "models/mail-templates/";
$portal_test 				= true;


//CONFIG OBJECT (PUT IT IN SESSION?)
class PortalConfig {
	private $data;
	public function __construct($values){
		$this->data = $values;
	}

	public function __get($varName){
		if (!array_key_exists($varName,$this->data)){
			//this attribute is not defined!
			throw new Exception('.....');
		} else {
			return $this->data[$varName];
		}
	}

	public function __set($varName,$value){
	  	$this->data[$varName] = $value;
	}

	public function getUsers(){
		$extra_params = array(
			'content' 	=> 'record',
			'fields'	=> array("id","portal_active","portal_consent_ts")
		);
		$result = RC::callApi($extra_params, false, $this->REDCAP_API_URL, $this->REDCAP_API_TOKEN);

		print_rr($result,1);

		return $result;	
	}
}

if(!isset($_SESSION["portalConfig"])){
	//ONLY A SUNK COST ONCE!
	$_CFG = new PortalConfig(array(
			 'REDCAP_API_URL'          => 'http://redcap.irvins.loc/api/'
			,'REDCAP_API_TOKEN'        => '379870900189B71CF04F47F6DC260835'
			,'REDCAP_PORTAL_EVENT'     => 'enrollment_arm_1'// Set to NULL if project is classic, otherwise name of event where redcap_fields are stored
			,'REDCAP_PORTAL_EVENT_1'   => 'short_anniversary_arm_1'
			,'REDCAP_PORTAL_EVENT_2'   => 'anniversary_2_arm_1'
			,'REDCAP_FIRST_FIELD'      => 'id'
			,'SPECIAL_USERS_RANGE'     => 500
			,'LOG_FILE'                => '/Users/irvins/Work/log/redcap_portal.log'
			,'SESSION_NAME'            => 'REDCAP_PORTAL' // Session variable name
			,'SESSION_TIMEOUT'         => 60*30// Session timeout in seconds
			
			,'PORTAL_BASE_PATH'        => dirname(__FILE__) // local dir for this file
			,'PORTAL_INC_PATH'         => dirname(__FILE__)."/inc" // path to include files
			,'PASS_TOKEN_EXPIRY'       => 60 // Number of minutes a forgotten password reset is valid
			,'PASSWORD_MIN_LENGTH'     => 8 // Minimum number of characters for the password
			,'EMAIL_ACTIVATION_EXPIRY' => 3 // Number of minutes to wait before re-emailing an activation link
			
			,'GOOGLE_RECAPTCHA_SECRET' => '6LcEIQoTAAAAAB9Yh-_QnKL_6nfjYVH5l1JpDdwx' 
			
			,'USER_FIELD_MAP' 		   => array(
									      	//INTERNAL ID        	//REDCAP FIELD NAMES
											 'id'               	=> 'id' //REDCAP_FIRST_FIELD
											,'username'             => 'portal_username'
											,'firstname'            => 'portal_firstname'
											,'lastname'             => 'portal_lastname'
											,'portal_middlename'	=> 'portal_middlename'
											,'portal_nickname'		=> 'portal_nickname'
											,'portal_mail_street'	=> 'portal_mail_street'
											,'portal_apartment_no'	=> 'portal_apartment_no'
											,'portal_pic'			=> 'portal_pic'
											,'portal_contact_name' 	=> 'portal_contact_name'
											,'portal_contact_phone' => 'portal_contact_phone'
											,'zip'              	=> 'portal_zip'
											,'city'              	=> 'portal_city'
											,'state'              	=> 'portal_state'
											,'age'              	=> 'portal_age'
											,'gender'              	=> 'core_gender'
											,'password'            	=> 'portal_password'
											,'email'               	=> 'portal_email'
											,'active'              	=> 'portal_active___1'
											,'suspended'           	=> 'portal_suspended___1'
											,'email_verified'      	=> 'portal_email_verified___1'
											,'email_verified_ts'   	=> 'portal_email_verified_ts'
											,'email_act_token'     	=> 'portal_email_act_token'
											,'email_act_sent_ts'   	=> 'portal_email_act_sent_ts'
											,'pass_reset_req_ts'   	=> 'portal_pass_reset_req_ts'
											,'pass_reset_token'    	=> 'portal_pass_reset_token'
											,'pass_reset_question' 	=> 'portal_pass_reset_question'
											,'pass_reset_answer'   	=> 'portal_pass_reset_answer'
											,'pass_reset_question2'	=> 'portal_pass_reset_q2'
											,'pass_reset_answer2'  	=> 'portal_pass_reset_a2'
											,'pass_reset_question3'	=> 'portal_pass_reset_q3'
											,'pass_reset_answer3'  	=> 'portal_pass_reset_a3'
											,'created_ts'          	=> 'portal_created_ts'
											,'last_login_ts'       	=> 'portal_last_login_ts'
											,'log'                 	=> 'portal_log'
											,'consent_ts'           => 'portal_consent_ts'
											,'user_bucket'          => 'portal_user_bucket'
											,'lang'					=> 'portal_lang'
											,'linked_projects'		=> 'linked_projects'
											,'user_event_arm'		=> 'user_event_arm'
											,'ffq_generated_ts'		=> 'ffq_generated_ts'
									  	)

			,'WEBSITE'					=> array(
											 'Name' 				=> "WELL for Life initiative"
											,'Url' 					=> "http://webtools.irvins.loc/portal/well2/"//including trailing slash //http://lymphaticnetwork.org/
											,'AllowedChildOrigin' 	=> "http://redcap.irvins.loc"
											,'emailAddress' 		=> "wellforlife@stanford.edu"//Tagged onto our outgoing emails
											,'emailDate' 			=> date("m-d-Y") //Date format used on email's
										    ,'mail_templates_dir' 	=> "models/mail-templates/" //Directory where txt files are stored for the email templates.
											,'language'				=> "en"
										)
		));
	$_SESSION["portalConfig"] 		= $_CFG;
}else{
	$_CFG = $_SESSION["portalConfig"];
}


class SurveysConfig {
	STATIC $fruits 			= array( 
		  "strawberry"
		, "grapes"
		, "watermelon"
		, "peach"
		, "bananas"
		, "raspberry"
		, "greenapple"
		, "pear"
		, "cherries"
		, "plum"
		, "pomegranate"
		, "mango"
		, "redapple"
		, "ranier"
		, "orange"
		, "apricot"
		, "lime"
		, "lemon"
	);

	STATIC $fitness 			= array( 
		  "basketball"
		, "biking"
		, "weightlifting"
		, "cardio"
		, "tbone"
		, "carrot"
		, 
	);

	STATIC $core_surveys 		= array(
		  "wellbeing_questions" 								
		, "a_little_bit_about_you"				
		, "your_physical_activity"
		, "your_sleep_habits"
		, "your_tobacco_and_alcohol_use"
		, "your_diet"
		, "your_health"
		, "about_you"
		, "wellbeing_questions"
		, "your_social_and_neighborhood_environment"
		, "contact_information"
		, "your_feedback"
	);

	STATIC $short_surveys 	= array(
		  "brief_well_for_life_scale" 								
	);

	STATIC $supp_surveys 	= array(
		  "how_physically_mobile_are_you" 								
		, "how_fit_are_you"				
		, "how_resilient_are_you_to_stress"
		, "how_well_do_you_sleep"
		, "find_out_your_body_type_according_to_chinese_medic"
		, "international_physical_activity_questionnaire"
	);

	STATIC $projects = array(
		 "REDCAP_PORTAL" 	=> array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "379870900189B71CF04F47F6DC260835", "Primary_Key" => "id", 		"Foreign_Key" => "")
		,"Supp2" 			=> array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "705F37450857D65739138B670C1020F7", "Primary_Key" => "id", 		"Foreign_Key" => "f_key")
		,"SHORT_SCALE"		=> array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "598D5570FDE2F4858ECC021C62D0E9DB", "Primary_Key" => "id", 		"Foreign_Key" => "f_key")
		,"ADMIN_CMS"		=> array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "8EF3C1EC1F0290E83BBC8B19C1B8D661", "Primary_Key" => "id", 		"Foreign_Key" => "")
		,"Studies" 			=> array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "AF618B5273FBA5D48BF54F2F68EA2E31", "Primary_Key" => "id", 		"Foreign_Key" => "f_key")	
		,"foodquestions" 	=> array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "C0449461FCEBCD4960B5CE544503156F", "Primary_Key" => "record_id", "Foreign_Key" => "portal_id")
		,"miniintervention" => array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "B512E84CB19643E93A335C9EBF52B877", "Primary_Key" => "record_id", "Foreign_Key" => "portal_id")
		,"taiwan_admin" 	=> array("URL" => "http://redcap.irvins.loc/api/", "TOKEN" => "2AC734BFA26EBF3618719A0B09EDAA0F", "Primary_Key" => "record_id", "Foreign_Key" => "portal_id")
	);
}
