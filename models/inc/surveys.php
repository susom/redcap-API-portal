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
		, "contact_information"	
		, "your_social_and_neighborhood_environment"				
		, "your_physical_activity"
		, "your_diet"
		, "your_sleep_habits"
		, "your_tobacco_and_alchohol_use"
		, "your_health"
		, "wellbeing_questions"
		, "more_wellbeing_questions"
		, "even_more_wellbeing_questions"
	);
}

class Surveys {
	PUBLIC $CoreSurveys;
	PUBLIC $core_surveys_complete 		= true;
	PUBLIC $user_current_survey_index 	= NULL;
	PUBLIC $current_arm 				= 0;

	public function __construct( $loggedInUser, $getall ){
		//GET ALL THE INSTRUMENTS
		$all_instruments 	= SELF::getInstruments();
		
		//GET ALL THE EVENT MAPPINGS
		$event_mappings 	= SELF::getEvents();

		//ALL USER ANSWERS IN ONE SHOT
		$user_answers 		= SELF::getUserAnswers($loggedInUser->id); //ALL PPOSSIBLE USER ANSWERS

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
			$split_hash 				= explode("s=",$check_survey_link);

			//SURVEY COMPLETE
			$proper_completed_timestamp = $instrument_id . "_timestamp";
			$user_actually_completed 	= (!isset($user_answers[$this->current_arm]) ? $user_answers[0][$proper_completed_timestamp] : $user_answers[$this->current_arm][$proper_completed_timestamp]); //= "[not completed]"
			$survey_complete 			= ( ($user_actually_completed == "[not completed]" || $user_actually_completed == "" )  ? 0 : 1);
			if(!$survey_complete && in_array($instrument_id, SurveysConfig::$core_surveys)){
				$this->core_surveys_complete = false;
			}

			if($getall){
				//THIS IS KIND OF SERVER INTENSIVE SO Try TO LIMIT IT TO BE CALLED ONLY WHEN NEEDED
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
			
				foreach($metadata as $idx => $item){
					$fieldname 						= $item["field_name"];
					$metadata[$idx]["user_answer"] 	= (array_key_exists($fieldname, $user_answers) ? $user_answers[$fieldname] : "");
				}

				$user_branched 		= array_intersect_key($branched_fields, $user_answers ) ;
			}
			
			$surveys[$instrument_id] = array(
				 "label" 			=> str_replace("And","&",$instrument["instrument_label"])
				,"event" 			=> $event_mappings[$index]["unique_event_name"]
				,"arm"				=> $event_mappings[$index]["arm_num"]
				,"survey_link" 		=> $check_survey_link
				,"survey_hash" 		=> $split_hash[1]
				,"survey_complete" 	=> $survey_complete
				
				,"raw"				=> ($getall ? $metadata: null)
				,"completed_fields"	=> ($getall ? $user_answers: null)
				,"total_questions"	=> ($getall ? $unbranched_total + count($user_branched): null)
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

//WHAT FILE IS CALLING
$regex = '/\/(\w+)\.php/';
preg_match($regex, $_SERVER['PHP_SELF'] ,$match);
$getall 					= ($match[1] == "survey" ? true : false);
$user_survey_data 			= new Surveys($loggedInUser, $getall);
$core_surveys_complete 		= $user_survey_data->getUserCoreComplete();
$surveys 					= $user_survey_data->getCoreMetaData();
$fruits  					= SurveysConfig::$fruits;
$all_survey_keys  			= array_keys($surveys);

// print_rr($surveys);
// exit;