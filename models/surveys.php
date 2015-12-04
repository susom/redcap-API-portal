<?php
$surveys = array();
$surveys[]  = array("Socio-Demographic",      "sociodemographic_questions"          ,"enrollment_arm_1");
$surveys[]  = array("Health Behavior",        "health_behavior_questions"           ,"enrollment_arm_1");
$surveys[]  = array("Social & Neighborhood",  "social_and_neighborhood_environment" ,"enrollment_arm_1");
$surveys[]  = array("Wellness Questions",     "wellness_questions"                  ,"survey_arm_2");

foreach($surveys as $index => $instrument_event){
	array_push($instrument_event, getSurveyLink($loggedInUser->id,$instrument_event[1],$instrument_event[2])); //returns url
	$status           = getAllCompletionStatus($loggedInUser->id,array($instrument_event[1]) ); //returns array
	$percent_complete = (!empty($status[0][$instrument_event[1]."_complete"]) ? $status[0][$instrument_event[1]."_complete"] : 0);
	array_push($instrument_event, $percent_complete);
	$surveys[$index]  = $instrument_event;
}

// echo "<pre>";
// print_r($surveys);