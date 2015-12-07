<?php
$surveys = array();
$surveys[]  = array("Socio-Demographic",      "sociodemographic_questions"          ,"enrollment_arm_1");
$surveys[]  = array("Health Behavior",        "health_behavior_questions"           ,"enrollment_arm_1");
$surveys[]  = array("Social & Neighborhood",  "social_and_neighborhood_environment" ,"enrollment_arm_1");
$surveys[]  = array("Wellness Questions",     "wellness_questions"                  ,"survey_arm_2");

foreach($surveys as $index => $instrument_event){
	array_push($instrument_event, getSurveyLink($loggedInUser->id,$instrument_event[1],$instrument_event[2])); //returns url [3] array index
	$total_questions  = count(getMetaData(array($instrument_event[1]))) ;
	array_push($instrument_event, $total_questions); //[total questions] [4]
	$status           = getAllCompletionStatus($loggedInUser->id,array($instrument_event[1]) ); //returns array
	$fields_complete  = (!empty($status[0][$instrument_event[1]."_complete"]) ? $status[0][$instrument_event[1]."_complete"] : 0);
	array_push($instrument_event, $fields_complete); //[user completed] [5]
	$percent_complete = round((intval($fields_complete)/intval($total_questions)),2);
	array_push($instrument_event, $fields_complete); //[user complete percentage] [6]
	$surveys[$index]  = $instrument_event;
}

// echo "<pre>";
// print_r($surveys);