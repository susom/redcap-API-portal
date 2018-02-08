 <?php
class Project {
	protected $LOGGED_IN_USER;
	protected $API_URL;
	protected $API_TOKEN;

	protected $ALL_INSTRUMENTS;
	protected $SURVEY_LINKS;
	protected $METADATA;
	protected $PROJECT_INFO;

	protected $ALL_USER_ANSWERS;
	protected $ACTIVE_INSTRUMENTS;

	protected $SHORT_SCALE 				 	= false;
	protected $instrument_list 				= "";

	protected $active_surveys_complete 		= true;
	protected $user_current_survey_index 	= NULL;
	protected $current_arm 				 	= 0; //WHAT EVEN IS THIS
	protected $name                       	= "";

	public function __construct($loggedInUser, $projectName="", $api_url, $api_token, $specific_event=null){
		//DO ALL THE ONE TIME CALLS OUT HERE
		//THEN I CAN GRANULARLY REFRESH JUST THE LIVE DATA
		//WHILE LEAVING THE OBJECT ITSELF STORED IN THE SESSION
		$this->API_URL 			= $api_url;
		$this->API_TOKEN 		= $api_token;
		$this->LOGGED_IN_USER 	= $loggedInUser;
		$this->name 			= $projectName;
		$this->current_arm 		= $loggedInUser->user_event_arm;
		$this->instrument_list 	= SurveysConfig::$core_surveys;
		$all_instruments 		= array();
		$all_events 			= self::getEvents();
		$this->specific_event 	= $specific_event;

		if(empty($all_events) || (is_array($all_events) && array_key_exists("error",$all_events)) ){	
			$all_instruments 		= self::getInstruments($projectName);
		}else{
			//GET ALL INSTRUMENTS/EVENTS IN THIS PROJECT
			$all_instruments 		= array_map(function($event){
				global $loggedInUser;
				$instrument_id 		= $event["form"];
				$instrument_label 	= str_replace("_"," ",$instrument_id);
				$user_current_event = !empty($loggedInUser->user_event_arm) ? $loggedInUser->user_event_arm  : REDCAP_PORTAL_EVENT ;
				
				if(!is_null($this->specific_event)){
					$user_current_event = $this->specific_event;
				}
				if($event["unique_event_name"] == $user_current_event){
					return array(
						 "arm_num" 				=> $event["arm_num"]
						,"unique_event_name" 	=> $event["unique_event_name"]
						,"instrument_name"		=> $instrument_id
						,"instrument_label"		=> ucwords($instrument_label)

					);
				}
			}, $all_events);
		}

		$user_current_event 	= !empty($loggedInUser->user_event_arm) ? $loggedInUser->user_event_arm  : REDCAP_PORTAL_EVENT ;
		if(strpos($user_current_event,"short") > -1){
			$this->SHORT_SCALE 		= true;
			$this->instrument_list 	= SurveysConfig::$short_surveys;
		}

		//ALL INSTRUMENTS(surveys) IN THIS PROJECT
		$this->ALL_INSTRUMENTS 	= array_filter($all_instruments);
		$surveylinks 	= array();
		$metadata 		= array(); 
		foreach($all_instruments as $index => $instrument){
			if(empty($instrument)){
				continue;
			}
			$instrument_id 					= $instrument["instrument_name"];
			$unique_event_name 				= isset($instrument["unique_event_name"]) 	? $instrument["unique_event_name"] 	: NULL;
			// markPageLoadTime("start $instrument_id");
			$surveylinks[$instrument_id]  	= self::getSurveyLink($this->LOGGED_IN_USER->id, $instrument_id, $unique_event_name);
			$metadata[$instrument_id]		= self::getMetaData(array($instrument_id));
			// markPageLoadTime("end $instrument_id");
		}
		$this->SURVEY_LINKS 	= $surveylinks;
		$this->METADATA 		= $metadata;
		$this->PROJECT_INFO 	= self::getProjectInfo();

		//ALL USER ANSWERS
		self::refreshData();
    }

	//GET ALL EVENTS (IF LONGITUDINAL)
	private function getEvents(){
		$extra_params = array(
			'content' 	=> 'formEventMapping',
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);	
		return $result;
	}

	//GET ALL THE INSTRUMENTS
    private function getInstruments($projname = false){
		$extra_params = array(
			'content' 	=> 'instrument',
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);	
		return $result;
	}

	//GET SURVEY LINKS
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

	//GET METADATA 
	private function getMetaData($instruments = null){
		$extra_params = array(
			'content' 	=> 'metadata',
			'forms'		=> ($instruments?: null)
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);
		return $result;
	}

	//GET PROJECT INFO
	private function getProjectInfo(){
		$extra_params = array(
			'content' 	=> 'project',
		);
		$result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);
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

	//GET THE RETURN CODE (NOT USED IN API BASED PORTAL)
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

	private function getSurveyInfo($all_instruments, $getall = true){
    	$surveys = array();

    	foreach($all_instruments as $index => $instrument){
			$instrument_id 		= $instrument["instrument_name"];
			$instrument_label	= $instrument["instrument_label"];
			$unique_event_name 	= isset($instrument["unique_event_name"]) 	? $instrument["unique_event_name"] 	: NULL;
			$arm_num 			= isset($instrument["arm_num"]) 			? $instrument["arm_num"] 			: NULL;
			$check_survey_link  = $this->SURVEY_LINKS[$instrument_id];

			//IF SURVEY ENABLED, RETURNS URL (STRING) , ELSE RETURNS JSON OBJECT (WITH ERROR CODE) SO JUST IGNORE
			if(strpos($check_survey_link,"error") > -1){
				continue;
			}

			//PUT TOGETHER SURVEY DATA FOR USER
			list($junk,$survey_hash) 	= explode("s=",$check_survey_link);

			//SURVEY COMPLETE
			$user_arm_answers = array();
			foreach($this->ALL_USER_ANSWERS as $i => $answers){
				if(isset($answers["redcap_event_name"]) && $answers["redcap_event_name"] !== $unique_event_name){
					continue;
				}
				$user_arm_answers  = $this->ALL_USER_ANSWERS[$i];
				break;
			}
			
			$proper_completed_timestamp = $instrument_id . "_timestamp";
			$user_actually_completed 	= isset($user_arm_answers[$proper_completed_timestamp]) ? $user_arm_answers[$proper_completed_timestamp] : null; //= "[not completed]"
			$instrument_complete 		= $user_actually_completed == "[not completed]" || $user_actually_completed == ""   ? 0 : 1;
			if(!$instrument_complete && in_array($instrument_id, $this->instrument_list)){
				$this->active_surveys_complete = false;
			}

			if($getall){
				//THIS IS KIND OF SERVER INTENSIVE SO TRY TO LIMIT IT TO BE CALLED ONLY WHEN NEEDED
				$metadata 			= $this->METADATA[$instrument_id];
				$projectInfo 		= $this->PROJECT_INFO;
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
				$just_form_ans 		= array_intersect_key($user_arm_answers,$just_formnames);
				$answers_only 		= array_filter($just_form_ans, function($var){
									  return ($var !== NULL && $var !== FALSE && $var !== '');
									});

				foreach($metadata as $idx => $item){
					$fieldname 						= $item["field_name"];
					$metadata[$idx]["user_answer"] 	= (array_key_exists($fieldname, $user_arm_answers) ? $user_arm_answers[$fieldname] : "");
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

    //GET ALL USER ANSWERS
	public function getUserAnswers($record_id=NULL,$fields = NULL,$event=NULL, $filterLogic=NULL){
		$extra_params = array(
		  'content'   	=> 'record',
		  'records' 	=> (is_null($record_id) ? null:  array($record_id) ),
		  'type'      	=> "flat",
		  'fields'    	=> $fields,
		  'exportSurveyFields' => true
		);
		if($event){
			$extra_params["events"] = "$event";
		}
		if($filterLogic){
			$extra_params["filterLogic"] = "$filterLogic";
		}
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
	
    public function getActiveAll(){
    	//BUILD SNAPSHOT OF ACTIVE INSTRUMENT DATA FOR THIS USER
		$this->ACTIVE_INSTRUMENTS 	= self::getSurveyInfo($this->ALL_INSTRUMENTS);
    	return $this->ACTIVE_INSTRUMENTS;
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

	public function getAllUserAnswers(){
		return $this->ALL_USER_ANSWERS;
	}

    //GET ALL THE BRANCHING ACROSS ALL ACTIVE INSTRUMENTS
	public function getAllInstrumentsBranching(){
		$all_branching = array();
		if(!isset($this->ACTIVE_INSTRUMENTS)){
			return;
		}

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

    public function getUserActiveComplete (){
    	return $this->active_surveys_complete;
    }

    public function getSingleInstrument($instrument_id){
    	return $this->ACTIVE_INSTRUMENTS[$instrument_id];
    }

    public function getUserCurrentInstrument (){
    	return $this->user_current_survey_index;
    }

    public function getInstrumentIds(){
    	return array_map(function($instrument){
    		return $instrument["instrument_id"];
    	},$this->ALL_INSTRUMENTS);
    }

    public function name(){
    	return $this->name;
    }

    //PROJECT OBJECT IS STORED IN SESSION, NEED TO REFRESH DATA TO MAKE SURE ITS NOT STALE
	public function refreshData(){
		//REFRESH DATA THAT FEEDS THESE
		$this->active_surveys_complete = true;
		$user_answers 				= self::getUserAnswers($this->LOGGED_IN_USER->id);
		$this->ALL_USER_ANSWERS 	= !empty($user_answers) ? $user_answers : array(); 
		return;
	}
}

class PreGenAccounts extends Project{
	//NOW INSTRUMENT HAS ACCESS TO THE METHODS THAT PROJECT HAS.

	//BUT... IT WILL NEED ALSO ITS API URL AND API TOKEN?
	public function __construct($loggedInUser, $projectName, $api_url, $api_token){
		parent::__construct($loggedInUser,$projectName, $api_url, $api_token);
    }

    public function getAccount(){
    	$ffq_ts 	  		= strToTime($this->LOGGED_IN_USER->consent_ts);
		$generate_new 		= false;

		if(!empty($ffq_ts)){
			$datediff    	= time() - $ffq_ts;
			$days_active 	= floor($datediff / (60 * 60 * 24));
			$years 			= ceil($days_active/365);

			if($years > 1){
				$generate_new = true;
			}
		}else{
			$generate_new = true;
		}

		$result = $this->checkExisting();
		if($generate_new && count($result) < $years){
	    	$ffq = $this->genNewAccount();
	    }else{
	    	if(!empty($result)){
	    		$ffq = array_pop($result);
    			unset($ffq["portal_id"]);
	    	}else{
	    		$ffq = $this->genNewAccount();
	    	}
	    }
	    return $ffq;
    }

    private function genNewAccount(){
    	global $loggedInUser;
    	$extra_params = array(
	      'content'   	=> 'record',
	      'fields'  	=> array("portal_id", "record_id","ffq_username","ffq_password"),
	      'filterLogic' => "[portal_id] = ''"
	    );
	    $result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);
	    if(count($result)){
	    	$foreign_key  = PROJ_ENV ."_" . $this->LOGGED_IN_USER->id;
	    	$ffq = array_shift($result);
	    	unset($ffq["portal_id"]);
	    	$data[] = array(
	          "record"            => $ffq["record_id"],
	          "field_name"        => "portal_id",
	          "value"             => $foreign_key
	        );
	        $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $this->API_URL,$this->API_TOKEN);
	    	$this->LOGGED_IN_USER->updateUser( array("ffq_generated_ts" => date('Y-m-d H:i:s')) ); 
	    	$loggedInUser->ffq_generated_ts = $this->LOGGED_IN_USER->ffq_generated_ts;
	    	setSessionUser($loggedInUser);
	    }else{
			//ERROR
			$ffq = array("error" => "Error. No new accounts available.");		    	
	    }
	    return $ffq;
    }

	private function checkExisting(){	
		$foreign_key  = PROJ_ENV ."_" . $this->LOGGED_IN_USER->id;
    	$extra_params = array(
	      'content'   	=> 'record',
	      'fields'  	=> array("portal_id", "record_id","ffq_username","ffq_password"),
	      'filterLogic' => "[portal_id] = '$foreign_key'"
	    );

	    $result = RC::callApi($extra_params, true, $this->API_URL, $this->API_TOKEN);
	    return $result;
	}
}

