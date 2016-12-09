<?php
// ob_start("ob_gzhandler"); //gzip outputted html

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
if( !empty($loggedInUser) ){
	// Check for logout
	if ( isset($_GET['logout']) && $_GET['logout'] == 1 ){
		unset($_SESSION[SESSION_NAME]);
		logout("Goodbye!");
	}
}

if(isset($_GET["lang"]) && is_file(dirname(__FILE__) . "/lang/".$_GET["lang"] .".php" )){
	$_SESSION["use_lang"] = $_GET["lang"];
	if(isset($loggedInUser->lang)){
		$loggedInUser->updateUser(array(
				        getRF("lang") 	=> $_GET["lang"]
				      ));
	}
}

if(!isset($_SESSION["use_lang"])){
	$_SESSION["use_lang"] = $_CFG->WEBSITE["language"];
}else{
	if($_SESSION["use_lang"] == "sp" && empty($_POST)){ //SO IT DONT PRINT FOR AJAX CALLS .. YIKES
		?>
		<style>
			.lang.en {
				display:none;
			}
			.lang.sp {
				display:inline-block !important;
			}
			.well {
				background-image:url(assets/img/well_logo_sp.png) !important;
			}
		</style>
		<?php
	}
}
if(isset($loggedInUser->lang)){
	if($loggedInUser->lang == "sp"){
		$_SESSION["use_lang"] = "sp";
		?>
		<style>
			.lang.en {
				display:none;
			}
			.lang.sp {
				display:inline-block !important;
			}
			.well {
				background-image:url(assets/img/well_logo_sp.png) !important;
			}
		</style>
		<?php
	}
}
require_once( dirname(__FILE__) . "/lang/".$_SESSION["use_lang"].".php");


$PAGE = basename($_SERVER["SCRIPT_FILENAME"]);
