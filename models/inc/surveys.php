<?php
// HARDCODING SOME STUFF, MAYBE SMARTER WAY TO DO THIS LATER
$fruits 		= array("strawberry","grapes","apple","banana","cherry","orange");
$surveys_arms 	= array();
$survey_arms["about_you"]  									= array("enrollment_arm_1"	, "About You"    	);
$survey_arms["your_health_behaviors"]  						= array("enrollment_arm_1"	, "Health Behaviors"    		);
$survey_arms["your_social_and_neighborhood_environment"]	= array("enrollment_arm_1"	, "Social & Neighborhood" 	);
$survey_arms["wellness_questions"]  						= array("enrollment_arm_1"		, "Wellness Questions"    	);

$core_surveys_complete 		= true;
$user_current_survey_index 	= null;
$current_arm 				= 0; 
$surveys 					= getInstruments();

//FIRST REMOVE NON PUBLIC "SURVEYS"
foreach($surveys as $index => $instrument_event){
	if(!array_key_exists($instrument_event["instrument_name"], $survey_arms)){
		unset($surveys[$index]);
		continue;
	}
}

//THEN REINDEX SURVEYS TO [0] INDEX
$surveys = array_values($surveys);

//NOW FILL OUT THE METADATA
foreach($surveys as $index => $instrument_event){
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
// print_rr($metadata,1);
	$actual_questions 	= array_filter($metadata, function($item){
						  return $item["field_type"] != "descriptive";
						});
	$no_branches 		= array_filter($actual_questions, function($item){
						  return empty($item["branching_logic"]);
						});
	$actual_formnames 	= array_map(function($item){
							return array("fieldname" => $item["field_name"], "fieldtype" => $item["field_type"], "branching_logic" => $item["branching_logic"], "fieldlabel" => $item["field_label"], "select_choices" => $item["select_choices_or_calculations"], "user_answer" => null);
						},$actual_questions);
	$unbranched_total 						= count($no_branches);
	$surveys[$index]["total_questions"] 	= $unbranched_total;
	$user_complete 							= 0;

	//GET # OF COMPLETED FIELDS OF SURVEY
	$just_formnames 	= array_map(function($item){
							return $item["field_name"];
						},$actual_questions);
	array_push($just_formnames, $instrument_id."_complete");
	$user_answers 		= getUserAnswers($loggedInUser->id,$just_formnames);

	if(isset($user_answers[0])){
		$user_actually_completed = (!isset($user_answers[$current_arm]) ? $user_answers[0][$instrument_id . "_timestamp"] : $user_answers[$current_arm][$instrument_id . "_timestamp"]); //= "[not completed]"

		//IF THERE ARE USER ANSWERS THEN MATCH THEM 
		$surveys[$index]["survey_complete"] = ($user_actually_completed == "[not completed]" || $user_actually_completed == "" ? 0 : 1);
		if(!$surveys[$index]["survey_complete"]){
			$core_surveys_complete = false;
		}
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

	if (is_null($user_current_survey_index) && !$surveys[$index]["survey_complete"] ) {
		$user_current_survey_index = $index;
	}
}

if(is_null($user_current_survey_index)){
	$user_current_survey_index = count($surveys) -1;
}

// echo $user_current_survey_index;
// echo "<pre>";
// print_r($surveys);
// exit;

