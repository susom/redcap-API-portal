<?php
// ob_start("ob_gzhandler"); //gzip outputted html

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//START TIMER FOR PAGE LOAD
$start_time	= microtime(true);

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
		$loggedInUser->lang = $_GET["lang"];
		$loggedInUser->updateUser(array(
				        getRF("lang") 	=> $_GET["lang"]
				      ));
	}
}

function languageSwitch($flag){
	$lang_span 				= $flag; 
	$lang_logo 				= "_".$lang_span;

	switch($flag){
		case "sp":
			$_SESSION["use_lang"] 	= "sp";
		break;

		case "tw":
			$_SESSION["use_lang"] 	= "tw";
			$lang_logo = "";
		break;

		case "cn":
			$_SESSION["use_lang"] 	= "cn";
			$lang_logo = "";
		break;

		default:
			$lang_span = "en";
			$lang_logo = "";
			return;
		break;
	}

	?>
	<style>
		.lang{
			display:none;
		}
		.lang.<?php echo $lang_span ?> {
			display:inline-block !important;
		}
		.well {
			background-image:url(assets/img/well_logo<?php echo $lang_logo ?>.png) !important;
		}
	</style>
	<?php
	return;
}

if(!isset($_SESSION["use_lang"])){
	$_SESSION["use_lang"] = $_CFG->WEBSITE["language"];
}else{
	if(empty($_POST)){
		languageSwitch($_SESSION["use_lang"]);
	}
}

if(isset($loggedInUser->lang)){
	languageSwitch($loggedInUser->lang);
}

require_once( dirname(__FILE__) . "/lang/".$_SESSION["use_lang"].".php");
$PAGE = basename($_SERVER["SCRIPT_FILENAME"]);

