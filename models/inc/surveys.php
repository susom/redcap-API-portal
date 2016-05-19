<?php
//DEFINITION IN class.Project.php
// unset($_SESSION["user_survey_data"]);
// exit;
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

//THESE ONLY NEED DATA CALL ONCE PER SESSION
$all_branching 			= $user_survey_data->getAllInstrumentsBranching();
$core_surveys_complete 	= $user_survey_data->getUserActiveComplete();
$all_survey_keys  		= array_keys($surveys); 
$fruits  				= SurveysConfig::$fruits;

//SUPPLEMENTAL PROJECTS
if(isset($_SESSION["supplemental_surveys"])){
	//THE BULK OF IT HAS BEEN CALLED ONCE, NOW JUST REFRESH THE NECESSARY DATA
	$supp_surveys  = $_SESSION["supplemental_surveys"];
}else{
	$supp_surveys = array();
	$supp_proj    = SurveysConfig::$projects;
	foreach($supp_proj as $proj_name => $project){
	  if($proj_name == $_CFG->SESSION_NAME){
	    continue;
	  }

	  $supplementalProject  = new Project($loggedInUser, $proj_name, SurveysConfig::$projects[$proj_name]["URL"], SurveysConfig::$projects[$proj_name]["TOKEN"]);
	  $supp_surveys         = array_merge($supp_surveys,$supplementalProject->getActiveAll());
	}
	$_SESSION["supplemental_surveys"] 	= $supp_surveys;
	// WILL NEED TO REFRESH THIS WHEN SURVEY SUBMITTED OR ELSE STALE DATA 
}
$supp_surveys_keys 	= array_keys($supp_surveys);

// print_rr($supp_surveys,1);
// print_rr($surveys,1);
// exit;