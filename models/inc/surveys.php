<?php
//DEFINITION IN class.Project.php
//
//
// man i really like cueto.  
 
// i will just nominate my POTG now, Cueto

// as im not expecting anything from this comatose offense.
// print_rr($secondProject,1);
// exit;

// if(!isset($_SESSION["user_survey_data"])){
	// $user_survey_data 				= $_SESSION["user_survey_data"];
// }else{
	$user_survey_data				= new Project($loggedInUser, SESSION_NAME, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
	// $_SESSION["user_survey_data"] 	= $user_survey_data;
	//WILL NEED TO REFRESH THIS WHEN SURVEY SUBMITTED OR ELSE STALE DATA 
// }

$fruits  				= SurveysConfig::$fruits;
$surveys 				= $user_survey_data->getActiveAll();
$all_survey_keys  		= array_keys($surveys);

$all_branching 			= $user_survey_data->getAllInstrumentsBranching();
$all_completed 			= $user_survey_data->getAllComplete();
$core_surveys_complete 	= $user_survey_data->getUserActiveComplete();



// print_rr($all_branching,1);
// print_rr($all_completed,1);
// print_rr($surveys,1);
// exit;