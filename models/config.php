<?php
	require_once(dirname(__FILE__)."/settings.php");
	
	require_once(dirname(__FILE__)."/lang/".$langauge.".php");

	require_once(dirname(__FILE__)."/class.mail.php");
	require_once(dirname(__FILE__)."/funcs.general.php");
	require_once(dirname(__FILE__)."/class.redcapportaluser.php");
	require_once(dirname(__FILE__)."/class.htmlpage.php");
	
	$PAGE = basename($_SERVER["SCRIPT_FILENAME"]);
	
	session_start();
	
	/*
		Start Session and determine if we are authenticated
		Authenticated means user+pass has matched, but does NOT mean the account is active
	*/
	
	$loggedInUser = getSessionUser();
	
	if( !empty($loggedInUser) )
	{
		// Check for logout
		if ( isset($_GET['logout']) && $_GET['logout'] == 1 )
		{
			logIt("Logging out", "INFO");
			logout("Goodbye!");
		}
		
		// We have a user from the session
		logIt("Rendering $PAGE","INFO");	
	}
