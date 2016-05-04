<?php
class Project {
	PRIVATE $LOGGED_IN_USER;
	PRIVATE $API_URL;
	PRIVATE $API_TOKEN;

	PRIVATE $ALL_INSTRUMENTS;
	PRIVATE $ACTIVE_INSTRUMENTS;
	PRIVATE $ALL_USER_ANSWERS;

	PRIVATE $active_surveys_complete 	= true;
	PRIVATE $user_current_survey_index 	= NULL;
	PRIVATE $current_arm 				= 0; //WHAT EVEN IS THIS

	PRIVATE $name = "";

	public function __construct($loggedInUser, $projectName="", $api_url, $api_token){
		$this->API_URL 			= $api_url;
		$this->API_TOKEN 		= $api_token;
		$this->LOGGED_IN_USER 	= $loggedInUser;
		$this->name 			= $projectName;

		$all_instruments 		= array();
		$all_events 			= self::getEvents();

		if(is_array($all_events)  ){
			if(array_key_exists("error",$all_events) || empty($all_events)){
				$all_instruments 		= self::getInstruments();
			}else{
				//GET ALL INSTRUMENTS/EVENTS IN THIS PROJECT
				$all_instruments 		= array_map(function($event){
					$instrument_id 		= $event["form"];
					$instrument_label 	= str_replace("_"," ",$instrument_id);
					return array(
						 "arm_num" 				=> $event["arm_num"]
						,"unique_event_name" 	=> $event["unique_event_name"]
						,"instrument_name"		=> $instrument_id
						,"instrument_label"		=> ucwords($instrument_label)

					);
				}, $all_events);
			}
		}

		$this->ALL_INSTRUMENTS 		= $all_instruments;

		//ALL USER ANSWERS IN ONE SHOT/ PRICEY BUT WITH CACHING WILL BE GOOD
		$user_answers 				= self::getUserAnswers($this->LOGGED_IN_USER->id);
		$this->ALL_USER_ANSWERS 	= !empty($user_answers) ? $user_answers[0] : array(); //ALL PPOSSIBLE USER ANSWERS

		//BUILD SNAPSHOT OF ACTIVE INSTRUMENT DATA FOR THIS USER
		$this->ACTIVE_INSTRUMENTS 	= self::getSurveyInfo($this->ALL_INSTRUMENTS);
    }

    //GET ALL THE INSTRUMENTS
    private function getInstruments(){
		$extra_params = array(
			'content' 	=> 'instrument',
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);	
		return $result;
	}

	//GET ALL EVENTS (IF LONGITUDINAL)
	private function getEvents(){
		$extra_params = array(
			'content' 	=> 'formEventMapping',
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);	
		return $result;
	}

	//GET METADATA - DEFAULT GETS ALL
	public function getMetaData($instruments = null){
		$extra_params = array(
			'content' 	=> 'metadata',
			'forms'		=> ($instruments?: null)
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);
		return $result;
	}

	//GET METADATA - DEFAULT GETS ALL
	public function getProjectInfo(){
		$extra_params = array(
			'content' 	=> 'project',
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);
		return $result;
	}

	/*
	INSTRUMENT LEVEL STUFF
	*/
	//GET SURVEY LINK
	private function getSurveyLink($id,$instrument,$event=null) {
		$extra_params = array(
			'content' 		=> 'surveyLink',		
			'record' 		=> $id,
			'instrument' 	=> $instrument,
			'event' 		=> $event
		);
		$result = RC::callApi($extra_params, false, $this->API_URL, $this->API_TOKEN);
		return $result;
	}

	//GET COMPLETE STATUS FOR ARRAY OF INSTRUMENTS
	private function getAllCompletionStatus($id, $instruments, $event=null) {
		$complete_fieldnames = array();
		foreach ($instruments as &$value) {
			$complete_fieldnames[] = $value.'_complete';		
		}
		$extra_params = array(
			'content' 	=> 'record',
			'records' 	=> $id,
			'fields'	=> $complete_fieldnames
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);	
		return $result;
	}

	//GET ALL USER INPUTTED
	public function getAllComplete(){
		$all_complete = array();
		foreach($this->ACTIVE_INSTRUMENTS as $instrument =>  $data){
			if ($instrument == "users") {
				continue;
			}
			if(isset($data["completed_fields"])){
				$all_complete = array_merge($all_complete,$data["completed_fields"]);
			}
		}
		return $all_complete;
	}

	//GET ALL USER ANSWERS
	public function getUserAnswers($record_id=NULL,$fields = NULL){
		$extra_params = array(
		  'content'   	=> 'record',
		  'records' 	=> (is_null($record_id) ? null:  array($record_id) ),
		  'type'      	=> "flat",
		  'fields'    	=> $fields,
		  'exportSurveyFields' => true
		);

		$result 		= RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN); 
		$proper_answers = array();
		if(!empty($result)){
			foreach($result as $i => $res){
				$proper_answers[$i] = array();
				foreach($res as $key => $val){
					$realkey 	= $key;
					$realvalue 	= $val;

					if(strpos($key, "___") > -1 && $val == 0){
						continue;
					}
					if(strpos($key, "___") > -1 && $val == 1){
						list($realkey,$realvalue) = explode("___", $key);
					}
					$proper_answers[$i][$realkey] = $realvalue;
				}	
			}
		}
		return $proper_answers;
	}

	private function getReturnCode($record_id, $instrument, $event=null){
		$extra_params = array(
			'content' 		=> 'surveyReturnCode',
			'record'		=> $record_id,
			'instrument'	=> $instrument,
			'event' 		=> $event
		);
		$result = RC::callApi($extra_params, false, $this->API_URL, $this->API_TOKEN);	
		return $result;
	}

	//GET ALL THE BRANCHING ACROSS ALL ACTIVE INSTRUMENTS
	public function getAllInstrumentsBranching(){
		$all_branching = array();
		foreach($this->ACTIVE_INSTRUMENTS as $instrument =>  $data){
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
					
					$andor  = "&&"; //Defualt && , doesnt matter
					if(count($effectors) == 1){
						//then it doesnt matter it will be OR
						//its mutually exclusive values for the same input(fieldname)
						//so the $andor value is moot
					}else{
						preg_match_all("/(?<ors>\sor\s)/",$branching, $matches);
						$ors 	= count($matches["ors"]);
						preg_match_all("/(?<ands>\sand\s)/",$branching, $matches);
						$ands 	= count($matches["ands"]);
						
						if($ors && !$ands){
							$andor = "||";
							// print_rr($branching);
							// print_rr($effectors);
						}else if($ors && $ands){
							//the multiple effector will take the "or" and the and is for the other
						}//else its default 
					}

					array_push($all_branching, array(
						 "affected" 	=> $field["field_name"]
						,"effector" 	=> $effectors
						,"branching" 	=> $branching
						,"andor"		=> $andor //WILL BE BEST GUESS
						) );
				}
			}
		}
		return array_filter($all_branching);
	}

	public function getSurveyInfo($all_instruments, $getall = true){
    	$surveys = array();
    	foreach($all_instruments as $index => $instrument){
			$instrument_id 		= $instrument["instrument_name"];
			$instrument_label	= $instrument["instrument_label"];
			$unique_event_name 	= isset($instrument["unique_event_name"]) 	? $instrument["unique_event_name"] 	: NULL;
			$arm_num 			= isset($instrument["arm_num"]) 			? $instrument["arm_num"] 			: NULL;
			$check_survey_link  = self::getSurveyLink($this->LOGGED_IN_USER->id, $instrument_id, $unique_event_name);





			//IF SURVEY ENABLED, RETURNS URL (STRING) , ELSE RETURNS JSON OBJECT (WITH ERROR CODE) SO JUST IGNORE
			if(json_decode($check_survey_link)){
				continue;
			}

			//PUT TOGETHER SURVEY DATA FOR USER
			list($junk,$survey_hash) 	= explode("s=",$check_survey_link);

			//SURVEY COMPLETE
			$proper_completed_timestamp = $instrument_id . "_timestamp";
			$user_actually_completed 	= $this->ALL_USER_ANSWERS[$proper_completed_timestamp]; //= "[not completed]"
			$instrument_complete 		= $user_actually_completed == "[not completed]" || $user_actually_completed == ""   ? 0 : 1;
			if(!$instrument_complete && in_array($instrument_id, SurveysConfig::$core_surveys)){
				$this->active_surveys_complete = false;
			}

			if($getall){
				//THIS IS KIND OF SERVER INTENSIVE SO TRY TO LIMIT IT TO BE CALLED ONLY WHEN NEEDED
				$metadata 			= self::getMetaData(array($instrument_id));
				$projectInfo 		= self::getProjectInfo();
				$project_notes	 	= $projectInfo["project_notes"];

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
				$just_form_ans 		= array_intersect_key($this->ALL_USER_ANSWERS,$just_formnames);
				$answers_only 		= array_filter($just_form_ans, function($var){
									  return ($var !== NULL && $var !== FALSE && $var !== '');
									});

				foreach($metadata as $idx => $item){
					$fieldname 						= $item["field_name"];
					$metadata[$idx]["user_answer"] 	= (array_key_exists($fieldname, $this->ALL_USER_ANSWERS) ? $this->ALL_USER_ANSWERS[$fieldname] : "");
				}
				$user_branched 		= array_intersect_key($branched_fields, $answers_only) ;
			}
			
			$surveys[$instrument_id] = array(
				 "label" 			=> str_replace("And","&",$instrument_label)
				,"project" 			=> $this->name
				,"event" 			=> $unique_event_name
				,"arm"				=> $arm_num
				,"survey_link" 		=> $check_survey_link
				,"survey_hash" 		=> $survey_hash
				,"survey_complete" 	=> $instrument_complete
				,"project_notes"	=> $project_notes
				,"raw"				=> ($getall ? $metadata 		: null)
				,"completed_fields"	=> ($getall ? $answers_only 	: null)
				,"total_questions"	=> ($getall ? $unbranched_total + count($user_branched): null)
				,"instrument_name"	=> $instrument_id
			);
		}
		return $surveys;
    }

    /*
    PUBLIC ACCESS
    */
   	public function getActiveAll(){
    	return $this->ACTIVE_INSTRUMENTS;
    }

    public function getUserActiveComplete (){
    	return $this->active_surveys_complete;
    }

    public function getUserCurrentInstrument (){
    	return $this->user_current_survey_index;
    }

    public function getInstrumentIds(){
    	return array_map(function($instrument){
    		return $instrument["instrument_id"];
    	},$this->ALL_INSTRUMENTS);
    }

    public function getSingleInstrument($instrument_id){
    	return $this->ACTIVE_INSTRUMENTS[$instrument_id];
    }

    public function name(){
    	return $this->name;
    }
}

class Instrument extends Project{
	//NOW INSTRUMENT HAS ACCESS TO THE METHODS THAT PROJECT HAS.
	
	//BUT... IT WILL NEED ALSO ITS API URL AND API TOKEN?
	public function __construct($instrument_id, $loggedInUser, $api_url, $api_token){
		parent::__construct($loggedInUser, $api_url, $api_token);

    }

    //SINCE EVERYTHING IS IN PARENT PROJECT CLASS.
    //WHY DO I NEED ANYTHING DOWN HERE?
    //WHAT SPECIAL THING CAN THIS DO?
}