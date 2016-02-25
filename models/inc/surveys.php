<?php
class SurveysConfig {
	STATIC $fruits 			= array( 
		  "strawberry"
		, "grapes"
		, "watermelon"
		, "peach"
		, "bananas"
		, "raspberry"
		, "greenapple"
		, "pear"
		, "cherries"
		, "plum"
		, "pomegranate"
		, "mango"
		, "redapple"
		, "ranier"
		, "orange"
		, "apricot"
		, "lime"
		, "lemon"
	);

	STATIC $core_surveys 	= array(
		  "about_you" 								
		, "your_health_behaviors"					
		, "your_social_and_neighborhood_environment"
		, "general_questions"
		// , "physical_health_questions"
		// , "stress_and_resilience_questions"
		// , "emotional_health_questions"
		// , "social_connectedness"
		// , "little_five_domain_questions"
		// , "illness_questions"
	);
}

class Surveys {
	PUBLIC $CoreSurveys;
	PUBLIC $core_surveys_complete 		= true;
	PUBLIC $user_current_survey_index 	= NULL;
	PUBLIC $current_arm 				= 0;

	public function __construct( $loggedInUser ){
		//GET ALL THE INSTRUMENTS
		$all_instruments 	= SELF::getInstruments();

		//GET ALL THE EVENT MAPPINGS
		$event_mappings 	= SELF::getEvents();

		//BUILD SURVEY INFO FOR USER
		$surveys = array();
		foreach($all_instruments as $index => $instrument){
			$instrument_id 		= $instrument["instrument_name"];
			$check_survey_link  = SELF::getSurveyLink($loggedInUser->id, $instrument_id, $event_mappings[$index]["unique_event_name"]);
			
			//IF SURVEY ENABLED, RETURNS URL (STRING) , ELSE RETURNS JSON OBJECT (WITH ERROR CODE) SO JUST IGNORE
			if(json_decode($check_survey_link)){
				continue;
			}

			//PUT TOGETHER SURVEY DATA FOR USER
			$split_hash 		= explode("s=",$check_survey_link);
			$metadata 			= SELF::getMetaData(array( $instrument_id ));

			//SOME QUESTION ACCOUNTING
			$actual_questions 	= array_filter($metadata, function($item){
								  return $item["field_type"] != "descriptive";
								});
			$has_branches 		= array_filter($actual_questions, function($item){
								  return !empty($item["branching_logic"]);
								});
			$unbranched_total 	= count($actual_questions) - count($has_branches);
			$branched_fields 	= array_flip(array_column($has_branches,"field_name") );

			//GET POSSIBLE FORM FIELDS TO CHECK FOR USER ANWERS
			$just_formnames 	= array_map(function($item){
									return $item["field_name"];
								},$actual_questions);
			$user_answers 		= SELF::getUserAnswers($loggedInUser->id,$just_formnames);

			if(isset($user_answers[0])){ //WHY IS THIS 0 and SOEMTIMES 1?
				$proper_completed_timestamp = $instrument_id . "_timestamp";
				$user_actually_completed 	= (!isset($user_answers[$this->current_arm]) ? $user_answers[0][$proper_completed_timestamp] : $user_answers[$this->current_arm][$proper_completed_timestamp]); //= "[not completed]"
				$survey_complete = ( ($user_actually_completed == "[not completed]" || $user_actually_completed == "" )  ? 0 : 1);
				
				if(!$survey_complete && in_array($instrument_id, SurveysConfig::$core_surveys)){
					$this->core_surveys_complete = false;
				}
		
				$user_answers = array_filter($user_answers[0]);
				foreach($metadata as $idx => $item){
					$fieldname 						= $item["field_name"];
					$metadata[$idx]["user_answer"] 	= (array_key_exists($fieldname, $user_answers) ? $user_answers[$fieldname] : "");
				}
			}
			$user_branched 							= array_intersect_key($branched_fields, $user_answers ) ;

			$surveys[$instrument_id] = array(
				 "label" 			=> str_replace("And","&",$instrument["instrument_label"])
				,"event" 			=> $event_mappings[$index]["unique_event_name"]
				,"arm"				=> $event_mappings[$index]["arm_num"]
				,"survey_link" 		=> $check_survey_link
				,"survey_hash" 		=> $split_hash[1]
				,"survey_complete" 	=> $survey_complete
				,"raw"				=> $metadata
				,"completed_fields"	=> $user_answers
				,"total_questions"	=> $unbranched_total + count($user_branched)
				,"instrument_name"	=> $instrument_id
			);
		}

		$this->CoreSurveys = $surveys;
    }

    public function getCoreMetaData(){
    	return $this->CoreSurveys;
    }

    public function getSuppMetaData(){
    	return $this->Surveys;
    }

    public function getUserCurrent (){
    	return $this->user_current_survey_index;
    }

    public function getUserCoreComplete (){
    	return $this->core_surveys_complete;
    }

    private function getSurveyLink($id,$instrument,$event=null) {
		$params = array(
			'content' 		=> 'surveyLink',		
			'record' 		=> $id,
			'instrument' 	=> $instrument,
			'event' 		=> $event
		);
		$result = RC::callApi($params,REDCAP_API_URL,false);

		return $result;
	}

	private function getAllCompletionStatus($id,$instruments,$event=null) {
		$complete_fieldnames = array();
		foreach ($instruments as &$value) {
			$complete_fieldnames[] = $value.'_complete';		
		}

		$extra_params = array(
			'content' 	=> 'record',
			'records' 	=> $id,
			'fields'	=> $complete_fieldnames
		);
		$result = RC::callApi($extra_params, REDCAP_API_URL);	
		
		return $result;
	}

	private function getInstruments(){
		$extra_params = array(
			'content' 	=> 'instrument',
		);
		$result = RC::callApi($extra_params);	
		
		return $result;
	}

	private function getEvents(){
		$extra_params = array(
			'content' 	=> 'formEventMapping',
		);
		$result = RC::callApi($extra_params);	
		
		return $result;
	}

	private function getMetaData( $instruments = null ){
		$extra_params = array(
			'content' 	=> 'metadata',
			'forms'		=> ($instruments?: null)
		);
		$result = RC::callApi($extra_params);	
		
		return $result;
	}

	private function getReturnCode($record_id, $instrument, $event=null){
		$extra_params = array(
			'content' 		=> 'surveyReturnCode',
			'record'		=> $record_id,
			'instrument'	=> $instrument,
			'event' 		=> $event
		);
		$result = RC::callApi($extra_params,REDCAP_API_URL,false);	

		return $result;
	}

	public function getUserAnswers($record_id=null,$fields = null){
		$extra_params = array(
		  'content'   	=> 'record',
		  'records' 	=> (is_null($record_id) ? null:  array($record_id) ),
		  'type'      	=> "flat",
		  'fields'    	=> $fields,
		  'exportSurveyFields' => true
		);
		$result = RC::callApi($extra_params); 
		  
		return $result;
	}
}

$fruits  					= SurveysConfig::$fruits;
$user_survey_data 			= new Surveys($loggedInUser);
$core_surveys_complete 		= $user_survey_data->getUserCoreComplete();
$surveys 					= $user_survey_data->getCoreMetaData();
$all_survey_keys  			= array_keys($surveys);

// print_rr($surveys);
// exit;