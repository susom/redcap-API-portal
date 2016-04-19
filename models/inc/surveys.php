<?php
//DEFINITION IN class.Project.php

// $regex 	= '/\/(\w+)\.php/';
// preg_match($regex, $_SERVER['PHP_SELF'] ,$match);
// $getall = ($match[1] == "survey" || $match[1] == "game"? true : false);
if(isset($_SESSION["user_survey_data"])){
	//ONLY A SUNK COST ONCE!
	$user_survey_data 				= $_SESSION["user_survey_data"];
}else{
	$user_survey_data 				= new Surveys($loggedInUser, true);
	$_SESSION["user_survey_data"] 	= $user_survey_data;
}
$core_surveys_complete 		= $user_survey_data->getUserCoreComplete();
$surveys 					= $user_survey_data->getCoreMetaData();
$fruits  					= SurveysConfig::$fruits;
$all_survey_keys  			= array_keys($surveys);
$all_completed 				= $user_survey_data->getAllComplete();
$all_branching 				= $user_survey_data->getAllBranching();

// print_rr($all_branching,1);
// print_rr($all_completed,1);
// print_rr($surveys,1);
// exit;