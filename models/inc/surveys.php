<?php
//DEFINITION IN class.Project.php
unset($_SESSION["user_survey_data"]);
// exit;

//DETERMINE WHICH ARM TO BE IN
$consent_date = strToTime($loggedInUser->consent_ts);
$datediff     = time() - $consent_date;
$days_active  = floor($datediff / (60 * 60 * 24));
$user_event_arm = isset($loggedInUser->user_event_arm) ? $loggedInUser->user_event_arm : REDCAP_PORTAL_EVENT;

//ON ANNIVERSARY UPDATE THEIR EVENT ARM
if( $days_active > 364 && $user_event_arm !== REDCAP_PORTAL_EVENT_1) {
	unset($_SESSION["user_survey_data"]);
	$loggedInUser->updateUser(array(
				"user_event_arm" => REDCAP_PORTAL_EVENT_1,
	));
	$loggedInUser->user_event_arm = REDCAP_PORTAL_EVENT_1;
}else if($days_active > 729 && $user_event_arm !== REDCAP_PORTAL_EVENT_2){
	unset($_SESSION["user_survey_data"]);
	$loggedInUser->updateUser(array(
				"user_event_arm" => REDCAP_PORTAL_EVENT_2,
	));
	$loggedInUser->user_event_arm = REDCAP_PORTAL_EVENT_2;
}

if(isset($_SESSION["user_survey_data"])){
	//THE BULK OF IT HAS BEEN CALLED ONCE, NOW JUST REFRESH THE NECESSARY DATA
	$user_survey_data 				= $_SESSION["user_survey_data"];
	//NEW METHOD TO REFRESH JUST THE NECESSARY DATA
	$user_survey_data->refreshData();
}else{
	//THIS KICKS OF 7 HEAVY API CALLS.  BUT NOT EVERYTHING CHANGES
	$user_survey_data				= new Project($loggedInUser, SESSION_NAME, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
	$_SESSION["user_survey_data"] 	= $user_survey_data;
	// WILL NEED TO REFRESH THIS WHEN SURVEY SUBMITTED OR ELSE STALE DATA 
}

//THIS DATA NEEDS TO BE REFRESHED EVERYTIME OR RISK BEING STALE 
$surveys 				= $user_survey_data->getActiveAll();	
$all_completed 			= $user_survey_data->getAllComplete(); 
 
$first_survey 			= reset($surveys);

//THESE ONLY NEED DATA CALL ONCE PER SESSION
$all_branching 			= $user_survey_data->getAllInstrumentsBranching();
$core_surveys_complete 	= $user_survey_data->getUserActiveComplete();
$all_survey_keys  		= array_keys($surveys); 
$fruits  				= SurveysConfig::$fruits;

// unset($_SESSION["supplemental_surveys"]);
// exit;
//SUPPLEMENTAL PROJECTS
if(isset($_SESSION["supplemental_surveys"])){
	//THE BULK OF IT HAS BEEN CALLED ONCE, NOW JUST REFRESH THE NECESSARY DATA
	$supp_surveys  = $_SESSION["supplemental_surveys"];
	foreach($supp_surveys as $supp_survey){
		$supp_survey->refreshData();
		$supp_branching 	= $supp_survey->getAllInstrumentsBranching();
		$all_branching 		= array_merge($all_branching,$supp_branching);
	}
}else{
	$supp_surveys = array();
	$supp_proj    = SurveysConfig::$projects;
	foreach($supp_proj as $proj_name => $project){
	  if($proj_name == $_CFG->SESSION_NAME || $proj_name == "Studies"){
	    continue;
	  }

	  $supplementalProject  	= new Project($loggedInUser, $proj_name, SurveysConfig::$projects[$proj_name]["URL"], SurveysConfig::$projects[$proj_name]["TOKEN"]);
	  $supp_branching 			= $supplementalProject->getAllInstrumentsBranching();
	  if(!empty($supp_branching)){
		  $all_branching 		= array_merge($all_branching,$supp_branching);
	  }
	  $supp_surveys[$proj_name] = $supplementalProject;
	}
	$_SESSION["supplemental_surveys"] 	= $supp_surveys;
	// WILL NEED TO REFRESH THIS WHEN SURVEY SUBMITTED OR ELSE STALE DATA 
}

$supp_instruments = array();
foreach($supp_surveys as $projname => $supp_project){
	$supp_instruments = array_merge( $supp_instruments,  $supp_project->getActiveall() );
} 
// print_rr($supp_instruments,1);
$supp_surveys_keys 	= array_keys($supp_instruments);
// print_rr($supp_surveys_keys);
// print_rr($surveys,1);
// exit;