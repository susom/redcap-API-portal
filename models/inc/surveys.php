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
		  "wellbeing_questions" 								
		, "a_little_bit_about_you"				
		, "your_physical_activity"
		, "your_sleep_habits"
		, "your_tobacco_and_alcohol_use"
		, "your_diet"
		, "your_health"
		, "about_you"
		, "wellbeing_questions"
		, "your_social_and_neighborhood_environment"
		, "contact_information"
	);
}

class Surveys {
	PUBLIC $CoreSurveys;
	PUBLIC $core_surveys_complete 		= true;
	PUBLIC $user_current_survey_index 	= NULL;
	PUBLIC $current_arm 				= 0;
	PRIVATE $event_mappings; 
	PRIVATE $user_answers;
	PRIVATE $loggedInUser;

	public function __construct( $loggedInUser, $getall ){
		$this->loggedInUser = $loggedInUser;

		//GET ALL THE INSTRUMENTS
		$all_instruments 	= self::getInstruments();

		//GET ALL THE EVENT MAPPINGS
		$this->event_mappings 	= self::getEvents();

		//ALL USER ANSWERS IN ONE SHOT
		$this->user_answers 	= self::getUserAnswers($this->loggedInUser->id); //ALL PPOSSIBLE USER ANSWERS

		//BUILD SURVEY INFO FOR USER
		
		$surveys = self::getSurveyInfo($all_instruments, $getall);
		
		$this->CoreSurveys = $surveys;
    }

    public function getSurveyInfo($all_instruments, $getall){
    	$surveys = array();
    	foreach($all_instruments as $index => $instrument){
			$instrument_id 		= $instrument["instrument_name"];
			$check_survey_link  = self::getSurveyLink($this->loggedInUser->id, $instrument_id, $this->event_mappings[$index]["unique_event_name"]);
			
			//IF SURVEY ENABLED, RETURNS URL (STRING) , ELSE RETURNS JSON OBJECT (WITH ERROR CODE) SO JUST IGNORE
			if(json_decode($check_survey_link)){
				continue;
			}

			//PUT TOGETHER SURVEY DATA FOR USER
			$split_hash 				= explode("s=",$check_survey_link);

			//SURVEY COMPLETE
			$proper_completed_timestamp = $instrument_id . "_timestamp";
			$user_actually_completed 	= (!isset($this->user_answers[$this->current_arm]) ? $this->user_answers[0][$proper_completed_timestamp] : $this->user_answers[$this->current_arm][$proper_completed_timestamp]); //= "[not completed]"
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
			
				$just_formnames 	= array_map(function($item){
										return $item["field_name"];
									},$actual_questions);
				$just_formnames 	= array_flip($just_formnames);

				$proper_answers = array();
				foreach($this->user_answers[0] as $key => $val){
					$realkey 	= $key;
					$realvalue 	= $val;

					if(strpos($key, "___") > -1 && $val == 0){
						continue;
					}
					if(strpos($key, "___") > -1 && $val == 1){
						list($realkey,$realvalue) = explode("___", $key);
					}
					$proper_answers[$realkey] = $realvalue;
				}

				$just_form_ans 		= array_intersect_key($proper_answers,$just_formnames);
				$answers_only 		= array_filter($just_form_ans);
				foreach($metadata as $idx => $item){
					$fieldname 						= $item["field_name"];
					$metadata[$idx]["user_answer"] 	= (array_key_exists($fieldname, $this->user_answers[0]) ? $this->user_answers[0][$fieldname] : "");
				}

				$user_branched 		= array_intersect_key($branched_fields, $answers_only) ;
			}
			
			$surveys[$instrument_id] = array(
				 "label" 			=> str_replace("And","&",$instrument["instrument_label"])
				,"event" 			=> $this->event_mappings[$index]["unique_event_name"]
				,"arm"				=> $this->event_mappings[$index]["arm_num"]
				,"survey_link" 		=> $check_survey_link
				,"survey_hash" 		=> $split_hash[1]
				,"survey_complete" 	=> $survey_complete
				
				,"raw"				=> ($getall ? $metadata 		: null)
				,"completed_fields"	=> ($getall ? $answers_only 	: null)
				,"total_questions"	=> ($getall ? $unbranched_total + count($user_branched): null)
				,"instrument_name"	=> $instrument_id
			);

		}
		return $surveys;
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

	public function getMetaData( $instruments = null ){
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

	public function getAllComplete(){
		$all_complete = array();
		foreach($this->CoreSurveys as $instrument =>  $data){
			if ($instrument == "users") {
				continue;
			}
			if(isset($data["completed_fields"])){
				$all_complete = array_merge($all_complete,$data["completed_fields"]);
			}
		}
		return $all_complete;
	}
	public function getAllBranching(){
		$all_branching = array();
		foreach($this->CoreSurveys as $instrument =>  $data){
			if ($instrument == "users") {
				continue;
			}

			if(isset($data["raw"])){
				foreach($data["raw"] as $field){
					$branching = $field["branching_logic"];
					if(empty($branching) ){
						continue;
					}
					preg_match_all("/\[(?<effector>[\w_]+)(\((?<check_value>\d+)\))?\] = \'(?<value>\d+)\'/",$branching, $matches);
					
					$effectors = array();
					foreach($matches["effector"] as $i => $ef){
						if(!array_key_exists($ef,$effectors)){
							$effectors[$ef] = array();
						}
						if(!empty($matches["check_value"][$i])){
							array_push($effectors[$ef],$matches["check_value"][$i]);
						}else{
							array_push($effectors[$ef],$matches["value"][$i]);
						}
					}
					array_push($all_branching, array(
						 "affected" 	=> $field["field_name"]
						,"effector" 	=> $effectors
						,"branching" 	=> $branching) );
				}
			}
		}
		return array_filter($all_branching);
	}
}

//WHAT FILE IS CALLING
$regex = '/\/(\w+)\.php/';
preg_match($regex, $_SERVER['PHP_SELF'] ,$match);
$getall 					= ($match[1] == "survey" || $match[1] == "game"? true : false);
$user_survey_data 			= new Surveys($loggedInUser, $getall);
$core_surveys_complete 		= $user_survey_data->getUserCoreComplete();
$surveys 					= $user_survey_data->getCoreMetaData();
$fruits  					= SurveysConfig::$fruits;
$all_survey_keys  			= array_keys($surveys);
$all_completed 				= $user_survey_data->getAllComplete();
$all_branching 				= $user_survey_data->getAllBranching();

//THIS IS ALREADY SUNK COST;
// print_rr($all_branching,1);
// print_rr($all_completed,1);
// exit;