<?php
// HARDCODING SOME STUFF, MAYBE SMARTER WAY TO DO THIS LATER
$fruits 		= array("strawberry","grapes","apple","banana","cherry","orange");
$surveys_arms 	= array();
$survey_arms["sociodemographic_questions"]  		= array("enrollment_arm_1"	, "Socio-Demographic"    	);
$survey_arms["health_behavior_questions"]  			= array("enrollment_arm_1"	, "Health Behavior"    		);
$survey_arms["social_and_neighborhood_environment"]	= array("enrollment_arm_1"	, "Social & Neighborhood" 	);
$survey_arms["wellness_questions"]  				= array("survey_arm_2"		, "Wellness Questions"    	);

$current_arm 	= 0; 
$surveys 		= getInstruments();
foreach($surveys as $index => $instrument_event){
	if(!array_key_exists($instrument_event["instrument_name"], $survey_arms)){
		unset($surveys[$index]);
		continue;
	}

	$instrument_id 							= $instrument_event["instrument_name"];
	if(isset($instrument_arm) && $instrument_arm != $survey_arms[$instrument_id][0]){
		$current_arm++;
	}
	$instrument_arm 						= $survey_arms[$instrument_id][0];
	$surveys[$index]["short_name"] 			= $survey_arms[$instrument_id][1];
	$surveys[$index]["instrument_arm"] 		= $instrument_arm;
	$surveys[$index]["survey_link"] 		= getSurveyLink($loggedInUser->id,$instrument_id,$instrument_arm);
	$surveys[$index]["return_code"] 		= getReturnCode($loggedInUser->id,$instrument_id,$instrument_arm);

	//GET TOTAL QUESTIONS PER SURVEY
	$metadata 			= getMetaData(array($instrument_id )); 
	$actual_questions 	= array_filter($metadata, function($item){
						  return $item["field_type"] != "descriptive";
						});
	$no_branches 		= array_filter($actual_questions, function($item){
						  return empty($item["branching_logic"]);
						});
	$actual_formnames 	= array_map(function($item){
							return array("fieldname" => $item["field_name"], "fieldtype" => $item["field_type"], "branching_logic" => $item["branching_logic"], "user_answer" => null);
						},$actual_questions);
	$unbranched_total 						= count($no_branches);
	$surveys[$index]["total_questions"] 	= $unbranched_total;
	$user_complete 							= 0;

	//GET # OF COMPLETED FIELDS OF SURVEY
	$just_formnames 	= array_map(function($item){
							return $item["field_name"];
						},$actual_questions);

	$user_answers 		= getUserAnswers($loggedInUser->id,$just_formnames);
	if(isset($user_answers[$current_arm])){
		//IF THERE ARE USER ANSWERS THEN MATCH THEM 
		foreach($actual_formnames as $idx => $inputgroup){
			if(isset($user_answers[$current_arm][$inputgroup["fieldname"]]) && $user_answers[$current_arm][$inputgroup["fieldname"]] !== "") {
				$actual_formnames[$idx]["user_answer"] = $user_answers[$current_arm][$inputgroup["fieldname"]];
				$user_complete++;

				if($actual_formnames[$idx]["branching_logic"] !== ""){
					$unbranched_total++;
				}
			}
		}
	}
	$surveys[$index]["meta_data"] 			= $actual_formnames;
	$surveys[$index]["completed_fields"] 	= $user_complete;
}

// echo "<pre>";
// print_r($surveys);
// exit;