<?php

/**
 * Sort a 2 dimensional array based on 1 or more indexes.
 * 
 * msort() can be used to sort a rowset like array on one or more
 * 'headers' (keys in the 2th array).
 * 
 * @param array        $array      The array to sort.
 * @param string|array $key        The index(es) to sort the array on.
 * @param int          $sort_flags The optional parameter to modify the sorting 
 *                                 behavior. This parameter does not work when 
 *                                 supplying an array in the $key parameter. 
 * 
 * @return array The sorted array.
 */
function msort($array, $key, $sort_flags = SORT_REGULAR) {
    if (is_array($array) && count($array) > 0) {
        if (!empty($key)) {
            $mapping = array();
            foreach ($array as $k => $v) {
                $sort_key = '';
                if (!is_array($key)) {
                    $sort_key = $v[$key];
                } else {
                    // @TODO This should be fixed, now it will be sorted as string
                    foreach ($key as $key_key) {
                        $sort_key .= $v[$key_key];
                    }
                    $sort_flags = SORT_STRING;
                }
                $mapping[$k] = $sort_key;
            }
            asort($mapping, $sort_flags);
            $sorted = array();
            foreach ($mapping as $k => $v) {
                $sorted[] = $array[$k];
            }
            return $sorted;
        }
    }
    return $array;
}


if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( ! isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( ! isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}

//------------------------------------------------------------------
/* SESSION FUNCTIONS */
//------------------------------------------------------------------

// Determines if session contains a user object and has not timed out -> returns user object
function getSessionUser() {
	if( isset($_SESSION[SESSION_NAME]['user']) && get_class($_SESSION[SESSION_NAME]['user']) == 'RedcapPortalUser' ) {
		// We have a user object in the session
		if(!isSessionExpired() ) {
			return $_SESSION[SESSION_NAME]['user'];
		}
		// Session expired
		clearSession();
		$_SESSION[SESSION_NAME]['message'] = "Session timed out.  Please login again.";
	}elseif( !empty($_SESSION[SESSION_NAME]['register']) && isset($_SESSION[SESSION_NAME]['user']) ){
 		// We have a user object in the session
		if(!isSessionExpired() ) {
			return $_SESSION[SESSION_NAME]['user'];
		}
	}
	return null;
}


// Takes a user object and saves the object to the session
function setSessionUser($user) {
	$_SESSION[SESSION_NAME]['user'] = $user;
}


// If a session redirect is present, then return the saved url and clear, otherwise return $default
function getSessionRedirectOr($default = '') {
	if ( isset($_SESSION[SESSION_NAME]['redirect']) && 
		 !empty($_SESSION[SESSION_NAME]['redirect']) ) {
		$default = $_SESSION[SESSION_NAME]['redirect'];
		// Clear saved redirect
		unset($_SESSION[SESSION_NAME]['redirect']);
	}
	return $default;
}


// Return the saved redirect url
function setSessionRedirect($url) {
	$_SESSION[SESSION_NAME]['redirect'] = $url;
}


// Returns the number of password reset attempts in this session
function getSessionPassResetAttempt(){
	$attempt = isset($_SESSION[SESSION_NAME]['password_reset_attempt']) && $_SESSION[SESSION_NAME]['password_reset_attempt'] > 1 ?
		$_SESSION[SESSION_NAME]['password_reset_attempt'] : 
		1;
	return $attempt;
}


// Increments the number of password reset attempts in this session
function incrementSessionPassResetAttempt() {
	$_SESSION[SESSION_NAME]['password_reset_attempt'] = getSessionPassResetAttempt() + 1;
	return getSessionPassResetAttempt();
}


// Clears the attempts counter in the session
function clearSessionPassResetAttempt() {
	unset($_SESSION[SESSION_NAME]['password_reset_attempt']);
}


// Determine if session has timed out - otherwise set/reset last activity
function isSessionExpired() {
	if( isset($_SESSION[SESSION_NAME]['LAST_ACTIVITY']) ) {
		// Timestamp exists
		if( time() - $_SESSION[SESSION_NAME]['LAST_ACTIVITY'] > SESSION_TIMEOUT ) {
			// Timestamp expired
			logIt("Session timeout.  Last activity " . (time() - $_SESSION[SESSION_NAME]['LAST_ACTIVITY']) . " sec ago", "INFO");
			return true;
		}
		logIt("Session updated after " . (time() - $_SESSION[SESSION_NAME]['LAST_ACTIVITY']) . " secs", "DEBUG");
	} else {
		logIt("Session started: " . date('Y-m-d H:i:s'), "DEBUG");
	}
	
	// Update session timestamp
	$_SESSION[SESSION_NAME]['LAST_ACTIVITY'] = time();
	return false;
}


// Erase the stored user and activity from session
function clearSession() {
	global $loggedInUser;
	//logIt('Logging out and killing session: ' . print_r($_SESSION,true),'DEBUG');
	$_SESSION[SESSION_NAME] = NULL;
	unset($_SESSION[SESSION_NAME]);
	$_SESSION = array();
	session_destroy();
	logIt('Session cleared','DEBUG');
	//$loggedInUser = NULL;
}


//------------------------------------------------------------------
/* SESSION MESSAGING */
//------------------------------------------------------------------

// Add a message for display on the next page - and optionally log the message
function addSessionMessage($msg, $type = 'notice', $logIt = true) {
	if ($type != 'notice' && $type != 'alert' && $type != 'success') {
		logIt("Invalid session message type: $type","ERROR"); 
		return false;
	}
	$_SESSION[SESSION_NAME]['messages'][$type][] = $msg;
	if ($logIt) logIt($msg, strtoupper($type));	
};


// Shortcut for adding an alert and whether to log it as well
function addSessionAlert($alert, $logIt = true) {
	addSessionMessage($alert, 'alert', $logIt);
}


//** returns html for all the session messages
function getSessionMessages($clear = true) {
	$types = array('alert','notice','success');
	$messages = array();
	foreach ($types as $type) {
		if(isset($_SESSION[SESSION_NAME]['messages'][$type]) && count($_SESSION[SESSION_NAME]['messages'][$type])) {
			$messages[] = makeMessageBox($_SESSION[SESSION_NAME]['messages'][$type], $type);
		} 
	}
	$result = count($messages) ? implode("\n",$messages) : '';

	// Clear out the session messages
	if ($clear) $_SESSION[SESSION_NAME]['messages'] = array();

	return $result;
}

// Make a nice bootstrap dismissable alert from an array of messages - color set by type
function makeMessageBox($messages, $type) {
	// Allowed type-to-class
	$typeClass = array(
		'alert' 	=> 'alert-danger',
		'notice' 	=> 'alert-info',
		'success' 	=> 'alert-success',
	);

	$btnClass = array(
		'alert' 	=> 'btn-warning',
		'notice' 	=> 'btn-info',
		'success' 	=> 'btn-success'
	);

	$pluralclass = (count($messages) < 2 ? "text-center" : "multi-message");
	$html = '<div class="alert ' . $typeClass[$type] . ' ' . $pluralclass . ' mb-30">
				<button class="btn '.$btnClass[$type].' data-dismiss="alert">OK</button>
				<ul>
			';
	$lines = array();
	foreach ($messages as $msg) {
		$lines[] = "<li><strong>$msg</strong></li>";
	}
	$html .= implode("\n",$lines) . '
		</ul></div>';
	return $html;
}


//------------------------------------------------------------------
/* LOGIN / STATUS FUNCTIONS */
//------------------------------------------------------------------
//GET "ELITE" 500 USERS
function getEliteUsers(){
	$extra_params = array(
		'content' 	=> 'record',
		'fields'	=> array("id","portal_active","portal_consent_ts")
	);
	$result = RC::callApi($extra_params, false, REDCAP_API_URL, REDCAP_API_TOKEN);
	$result = json_decode($result,1);
	//GET ALL USERS, THEN GET ACTIVE
	$active = array_filter($result, function($u){
		return $u["portal_active___1"] == 1;
	});

	//THEN SORT BY OLDEST FIRST
	$elite 	= msort($active, "portal_consent_ts", 0);
	
	//NOW JUST SLICE OFF THE FIRST 500
	$elite 	= array_slice($elite, 0, 500);

	//EXTRACT USER ID INTO ARRAY
	$elite 	= array_column($elite, "id");

	return $elite;
}

// Returns true if user object is defined globally
function isUserLoggedIn() {
	global $loggedInUser;

	return (isset($loggedInUser) && !empty($loggedInUser));
}


// Returns true if user is suspended
function isUserSuspended() {
	global $loggedInUser;

	return (isset($loggedInUser) && !empty($loggedInUser) && $loggedInUser->isSuspended());
}


// Returns true if user is active
function isUserActive() {
	global $loggedInUser;

	return (isset($loggedInUser) && !empty($loggedInUser) && $loggedInUser->isActive());
}


// Allows any user that has logged in but profile may be incomplete
function requireUserAccount() {
	global $PAGE;
	if( !isUserLoggedIn() ) {
		logIt($PAGE . " only allows users - force login", "DEBUG");
		redirectToLogin();
	} elseif( isUserSuspended() ) {
		logIt("Redirecting suspended account to profile.php","DEBUG");
		$msg = "Your account has been suspended.  You will be unable to access the site until this is resolved.";
		logout($msg);
	}

	return;
}


// Allows only users marked as 'active' - otherwise redirect to profile
function requireActiveUserAccount() {
	requireUserAccount();
	if( !isUserActive() ) {
		$message = "Please complete your profile setup";
		redirectToProfile($message);
	}

	return;
}


// Redirects to the login page with a referral url...
function redirectToLogin() {
	$current_url 	= $_SERVER['PHP_SELF'];
	$current_page 	= basename($current_url);
	if ($current_page !== 'login.php' && !isset($_GET['logout']))
	{
		$url = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
		$url = str_replace('/webtools/','',$url);
		setSessionRedirect($url);
	}
	header('Location: login.php'); die();
}


// Redirect user to profile page with optional message
function redirectToProfile($message = Null) {
	if (!empty($message)) addSessionMessage($message);
	$current_url 	= $_SERVER['PHP_SELF'];
	$current_page 	= basename($current_url);
	if ($current_page !== 'profile.php') {
		header('Location: profile.php');
		die();
	}

	return;
}

// Logout current user and session.  If called via timeout, then redirect back after login
function logout($message, $timeout = false) {
	//logIt("Logout called: $message / ".(int)$timeout, "DEBUG");
	global $loggedInUser,$websiteUrl;

	$lang_query = "?lang=".$_SESSION["use_lang"];
	
	if( isUserLoggedIn() ) {
		$loggedInUser->log_entry[] = "Logged out";
		$loggedInUser->updateUser();
		$loggedInUser = NULL;
	}
	clearSession();
	if (!empty($message)) addSessionMessage($message);
	if ($timeout == true) setSessionRedirect($_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
	
	// Goto Login page but redirect back to original page after authentication
	$destination = $websiteUrl."login.php".$lang_query;
	header("Location: $destination");
	exit;
}

//------------------------------------------------------------------
// PASSWORD RESET / FORGOTTEN PASSWORD METHODS

function verifyReCaptcha() {
	$url = "https://www.google.com/recaptcha/api/siteverify";
	$params = array(
		'secret' 	=> 	GOOGLE_RECAPTCHA_SECRET,
		'response' 	=> 	(isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : NULL),
		'remoteip' 	=> 	$_SERVER['REMOTE_ADDR']
	);
	$response = RC::http_post($url, $params);
	//logIt("ReCaptuca Result " . print_r($response,true) . " from " . print_r($params,true), "DEBUG");
	
	return json_decode($response,true);
}


// Look up all users and find the one with a matching password reset token
function getUserByPasswordToken($token) {
	$params = array(
		'fields' => array(
			REDCAP_FIRST_FIELD, 
			getRF('pass_reset_token'), 
			getRF('pass_reset_req_ts')
		)
	);
	$result 		= RC::callApi($params, true, REDCAP_API_URL, REDCAP_API_TOKEN);
	$errors 		= array();
	$token_matches 	= array();
	
	foreach ($result as $idx => $record) {
		$recordToken = trim($record[getRF('pass_reset_token')]);	
		logIt("Comparing $token with $recordToken - DEBUG","DEBUG");
		
		if( $token == $recordToken ) {
			$token_matches[$record[REDCAP_FIRST_FIELD]] = $record;
		}
	}
	
	if (count($token_matches) == 0) {
		logIt("Found no matching password tokens", "DEBUG");
		return false;
	}
	
	if (count($token_matches) > 1) {
		// Found more than one match!
		logIt("Found more than one match for a password reset token: $token", "ERROR");
		return false;
	}
	
	if (count($token_matches) == 1) {
		$user_id = key($token_matches);
		logIt("Found $user_id via password token", "INFO");
		$user = new RedcapPortalUser($user_id);
		return $user;
	}
}

// Takes a user object and returns true if it is still in a valid reset session
function isPasswordResetActive($user) {
	if (empty($user->pass_reset_token)) {
		logIt('Empty password reset token - password reset is expired',"DEBUG");
		return false;
	}
	
	if (empty($user->pass_reset_req_ts)) {
		logIt('Empty password reset request timestamp - password reset is assumed expired.', 'ERROR');
		return false;
	}
	$age_in_min = getPasswordTokenAgeInMin($user);
	
	return $age_in_min <= PASS_TOKEN_EXPIRY;
}


// Takes a user object and returns that age of the password token in minutes
function getPasswordTokenAgeInMin($user) {
	$now_ts = new DateTime();
	$token_requested_ts = new DateTime($user->pass_reset_req_ts);
	$age_in_min = floor( ( $now_ts->getTimestamp() - $token_requested_ts->getTimestamp() ) / 60);
	logIt("Age of password token is $age_in_min min", "DEBUG");
	return $age_in_min;
}


// Look up a user by email address - return false or user object
function getUserByEmail($email) {
	$params = array(
		'fields' => array(
			REDCAP_FIRST_FIELD, 
			getRF('email')
		)
	);
	$result = RC::callApi($params, true, REDCAP_API_URL, REDCAP_API_TOKEN);
	
	$errors = array();
	$matches = array();
	foreach ($result as $idx => $record) {
		$recordEmail = sanitize($record[getRF('email')]);
		
		logIt("email: $email / recordEmail: $recordEmail");
		if( $email == $recordEmail ) {
			$matches[$record[REDCAP_FIRST_FIELD]] = $record;
		}
	}
	
	if (count($matches) == 0) {
		logIt("Found no matching users with email: $email", "DEBUG");
		return false;
	}
	
	if (count($matches) > 1) {
		// Found more than one match!
		logIt("Found more than one match for email: $email", "ERROR");
		return false;
	}
	
	if (count($matches) == 1) {
		$user_id = key($matches);
		logIt("Found $user_id via email", "INFO");
		$user = new RedcapPortalUser($user_id);
		return $user;
	}	
}

// Look up a user by email address - return false or user object
function getUserByUsername($username) {
	$params = array(
		'fields' => array(
			REDCAP_FIRST_FIELD, 
			getRF('username')
		)
	);
	$result = RC::callApi($params, true, REDCAP_API_URL, REDCAP_API_TOKEN);
	
	$errors = array();
	$matches = array();
	foreach ($result as $idx => $record) {
		$recordUsername = sanitize($record[getRF('username')]);
		
		logIt("username: $username / recordUsername: $recordUsername");
		if( $username == $recordUsername ) {
			$matches[$record[REDCAP_FIRST_FIELD]] = $record;

			if (count($matches) > 1) {
				// Found more than one match!
				logIt("Found more than one match for email: $email", "ERROR");
				break;
				return false;
			}
		}
	}
	
	if (count($matches) == 0) {
		logIt("Found no matching users with email: $email", "DEBUG");
		return false;
	}

	if (count($matches) == 1) {
		$user_id = key($matches);
		logIt("Found $user_id via username", "INFO");
		$user = new RedcapPortalUser($user_id);
		return $user;
	}	
}

// Look up a user by email address - return false or user object
function getUsernameByParticipantID($portal_participant_id) {
	$params = array(
		'fields' => array(
			REDCAP_FIRST_FIELD, 
			getRf("username"),
			"portal_participant_id",

		)
	);
	$result = RC::callApi($params, true, REDCAP_API_URL, REDCAP_API_TOKEN);
	
	$errors 	= array();
	$matches 	= array();
	foreach ($result as $idx => $record) {
		$recordPartID = sanitize($record["portal_participant_id"]);
		
		if( $portal_participant_id == $recordPartID ) {
			$matches[$record[REDCAP_FIRST_FIELD]] = $record;

			if (count($matches) > 1) {
				// Found more than one match!
				logIt("Found more than one match for participant id: $portal_participant_id", "ERROR");
				break;
				return false;
			}
		}
	}
	
	if (count($matches) == 0) {
		logIt("Found no matching users with participant id: $portal_participant_id", "DEBUG");
		return false;
	}

	if (count($matches) == 1) {
		$user_id = key($matches);
		logIt("Found $user_id via username", "INFO");
		$user = new RedcapPortalUser($user_id);
		return $user;
	}	
}

//------------------------------------------------------------------
// OTHER METHODS
//------------------------------------------------------------------

function linkSupplementalProject($api, $portalUser, $ev_name = "") {
	//THIS LINKS A USER OF A MAIN PROJECT (PORTAL w/ userInfo) TO ANOTHER SUPLLEMENTAL PROJECT
	$pk_field 		= $api["Primary_Key"];
	$fk_field 		= $api["Foreign_Key"];
	$portalUserId 	= $portalUser->id;

	$params 				= array( "fields" => array($pk_field) ); //record id + foreignKey
	$params["filterLogic"] 	= "[$fk_field] = '$portalUserId'"; //foreign key filter
	if(!empty($ev_name)){
		$params["events"] = array($ev_name); //event filter
    }
    $q = RC::callApi($params, true, $api["URL"], $api["TOKEN"]);
    $new_id = $portalUserId;
    if( !empty($q) && !empty($ev_name) ){
    	//OK DONT CREATE A NEW ROW EVERY DANG TIME ,
    	//ONLY IF THERE IS ANOTHER EVENT ARM ADDED I GUESS
    	$prefix = $portalUserId;
	    $i 		= 2;
	    do {
            $new_id = $prefix . "-" . $i;
            $i++;
            $found = false;
            foreach ($q as $record) {
            	if( $record[$pk_field] === $new_id ){
            		$found = true;
            		break;
            	}
            }
        } while ($found && $i < 99);
	}
	$data = array("id"=> $new_id, $fk_field => $portalUserId);
	if(!empty($ev_name)){
		$data["redcap_event_name"] = $ev_name;
	}
	RC::writeToApi($data, array(), $api["URL"], $api["TOKEN"]);
	return $new_id;
}

// Looks up the redcap field name for a given user property
function getRF($property) {
	global $redcap_field_map;
	if( isset($redcap_field_map[$property]) ) {
		return $redcap_field_map[$property];
	} else {
		logIt("Error finding $property in redcap_field_map", "ERROR");
		return false;
	}
}


// lowercase and strip tags
function sanitize($str) {
	return strtolower(strip_tags(trim(($str))));
}


// Regex to validate email address
function isValidemail($email) {
	// return preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", trim($email) );
	return preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",trim($email));
}


// Determine if length of string is in range
function minMaxRange($min, $max, $what) {
	if(strlen(trim($what)) < $min || strlen(trim($what)) > $max) {
		return true;
	} else {
		return false;
	}
}


//@ Thanks to - http://phpsec.org
function generateHash($plainText, $salt = null) {
	if ($salt === null || strlen($salt) < 25) {
		$salt = generateRandomString(25, true);
	} else {
		$salt = substr($salt, 0, 25);
	}

	return $salt . sha1($salt . $plainText);
}


// Used to pull from languge file
function lang($key,$markers = NULL) {
	global $lang;
	$str = $lang[$key];

	if($markers != NULL) {
		//Replace any dyamic markers
		$iteration 	= 1;
		foreach($markers as $marker) {
			$str = str_replace("%m".$iteration."%",$marker,$str);
			$iteration++;
		}
	}
	
	//Ensure we have something to return
	if($str == "") {
		return ("No language key found");
	} 
	
	return $str;
}


//Log to file
function logIt($msg, $level = "INFO") {
	global $loggedInUser, $user;
	
	$user_id = isset($loggedInUser) ? "[" . $loggedInUser->user_id . "]" : NULL;
	if (!$user_id && is_object($user)) $user_id = "~" . $user->user_id . "~";
	file_put_contents(LOG_FILE,	date( 'Y-m-d H:i:s' ) . "\t" . $level . "\t" . $user_id . "\t" . $msg . "\n", FILE_APPEND );

	return;
}


// Creates random alphanumeric string
function generateRandomString($length=25, $addNonAlphaChars=false, $onlyHandEnterableChars=false, $alphaCharsOnly=false) {
	// Use character list that is human enterable by hand or for regular hashes (i.e. for URLs)
	if ($onlyHandEnterableChars) {
		$characters = '34789ACDEFHJKLMNPRTWXY'; // Potential characters to use (omitting 150QOIS2Z6GVU)
	} else {
		$characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789'; // Potential characters to use 
		if ($addNonAlphaChars) $characters .= '~.$#@!%^&*-';
	}
	// If returning only letter, then remove all non-alphas from $characters
	if ($alphaCharsOnly) {
		$characters = preg_replace("/[^a-zA-Z]/", "", $characters);
	}
	// Build string
	$strlen_characters = strlen($characters);
	$string = '';
	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, $strlen_characters-1)];
	}
	// If hash matches a number in Scientific Notation, then fetch another one 
	// (because this could cause issues if opened in certain software - e.g. Excel)
	if (preg_match('/^\d+E\d/', $string)) {
		return generateRandomString($length, $addNonAlphaChars, $onlyHandEnterableChars);
	} 
		
	return $string;
}


// Clean and convert security answer to MD5 hash
function hashSecurityAnswer($answer_orig) {
	// Trim and remove non-alphanumeric characters (but keep spaces and keep lower-case)
	$answer = trim($answer_orig);	
	// Replace non essential characters
	$answer = preg_replace("/[^0-9a-z ]/", "", strtolower($answer));
	// If answer is not ASCII encoded and also results with a blank string after the string replacement, then leave as-is before hashing.
	//if (!(function_exists('mb_detect_encoding') && mb_detect_encoding($answer) != 'ASCII' && $answer_repl == '')) {
	//	$answer = $answer_repl;
	//}
	// Return MD5 hashed answer
	return md5($answer);	
}	


//------------------------------------------------------------------
// Used by UserPie Email
function replaceDefaultHook($str) {
	global $default_hooks,$default_replace;

	return (str_replace($default_hooks,$default_replace,$str));
}

// SURVEY METADATA STUFF
function getAnswerOptions($choices){
  //GET PRE BAKED ANSWER FROM USER CHOICE #
  $answer_choices = explode(" | ",$choices);
  $select_choices = array();

  foreach($answer_choices as $qa){
    $temp = explode("," , $qa);
    $select_choices[trim($temp[0])] = trim($temp[1]);
  }

  return $select_choices;
}

function getActionTags($fieldmeta){
	$re = "/  (?(DEFINE)
	     (?<number>    -? (?= [1-9]|0(?!\\d) ) \\d+ (\\.\\d+)? ([eE] [+-]? \\d+)? )    
	     (?<boolean>   true | false | null )
	     (?<string>    \" ([^\"\\\\\\\\]* | \\\\\\\\ [\"\\\\\\\\bfnrt\\/] | \\\\\\\\ u [0-9a-f]{4} )* \" )
	     (?<array>     \\[  (?:  (?&json)  (?: , (?&json)  )*  )?  \\s* \\] )
	     (?<pair>      \\s* (?&string) \\s* : (?&json)  )
	     (?<object>    \\{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \\s* \\} )
	     (?<json>      \\s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) )  ) \\s*
	     (?<tag>       \\@(?:[[:alnum:]])*)
	  )
	  
	  (?'actiontag'
	    (?:\\@(?:[[:alnum:]_-])*)
	  )
	  (?:\\=
	    (?:
	     (?:
	      (?'params_json'(?&json))
	     )
	     |
	     (?:
	       (?'params'(?:[[:alnum:]_-]+))
	     )
	    )
	  )?/ixm"; 

	$str 		= $fieldmeta["field_annotation"];
	preg_match_all($re, $str, $matches);

	$results 	= array();
	foreach($matches["actiontag"] as $key => $tag){
		$params = false;
		if(!empty($matches["params_json"][$key])){
			$params = json_decode($matches["params_json"][$key],1);
		}elseif(!empty($matches["params"][$key])){
			$params = $matches["params"][$key];
		}

		$results[$tag] = $params;
	}
	
	return $results;
}

function getBase64Img($file_curl ){
	if(strpos($file_curl["headers"]["content-type"][0],"image") > -1){
      $split    = explode("; ",$file_curl["headers"]["content-type"][0]);
      $mime     = $split[0];
      $split2   = explode('"',$split[1]);
      $imgname  = $split2[1];
      $eventpic = '<img class="event_img" src="data:'.$mime.';base64,' . base64_encode($file_curl["file_body"]) . '">';
    }
    return $eventpic;
}

if (!function_exists('curl_file_create')) {
  function curl_file_create($filename, $mimetype = '', $postname = '') {
    return "@$filename;filename="
        . ($postname ?: basename($filename))
        . ($mimetype ? ";type=$mimetype" : '');
  }
}
  
function print_rr($d,$exit=false){
	echo "<pre>";
	print_r($d);
	echo "</pre>";
	if($exit){
		exit;
	}
}

function markPageLoadTime($msg=null){
	global $start_time;
	
	echo "<h6>";
	if($msg){
		echo $msg ."<br>";
	}
	echo microtime(true) - $start_time;
	echo "</h6>";
}