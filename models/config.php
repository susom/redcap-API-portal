<?php
ob_start("ob_gzhandler"); //gzip outputted html

//REQUIRED LIBRARIES
$requires 	= array(
	 "/settings.php"
	,"/funcs.general.php"
	,"/class.RCAPI.php"
	,"/class.redcapAuth.php"
	,"/class.redcapportaluser.php"
	,"/class.mail.php"
	,"/class.Project.php"
);
foreach($requires as $required){
	require_once( dirname(__FILE__) . $required);
}
require_once( dirname(__FILE__) . "/lang/".$_CFG->WEBSITE["language"] .".php");

$start_time	= microtime(true);
// $end_time 	= microtime(true) - $start_time; //measure script time somewhere

$default_hooks 				= array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
$default_replace 			= array( $_CFG->WEBSITE["Name"]
									,$_CFG->WEBSITE["Url"]
									,$_CFG->WEBSITE["emailDate"]
								);


// Start Session and determine if we are authenticated
// Authenticated means user+pass has matched, but does NOT mean the account is active
session_start();
$loggedInUser = getSessionUser($_CFG->SESSION_NAME);

$special_user = $loggedInUser->id < $_CFG->SPECIAL_USERS_RANGE ?  "special_user_icon" : ""; 
if( !empty($loggedInUser) ){
	// Check for logout
	if ( isset($_GET['logout']) && $_GET['logout'] == 1 ){
		logout("Goodbye!");
	}
}

$PAGE = basename($_SERVER["SCRIPT_FILENAME"]);
