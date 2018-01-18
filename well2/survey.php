<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");
include("models/class.Survey.php");

$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];

//SPECIAL CUSTOM MAT SCORINGCAPTURE
if(isset($_REQUEST["TCM"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = $_REQUEST["sid"] ?: null;
  $value        = $_REQUEST["met_score"] ?: null;

  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'tcm_score',
      "value"             => $value
    );

  if($event_name){
    $data[0]["redcap_event_name"] = $event_name;
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
}

//SPECIAL CUSTOM MAT SCORINGCAPTURE
if(isset($_REQUEST["IPAQ"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = $_REQUEST["sid"] ?: null;
  $value        = $_REQUEST["met_score"] ?: null;

  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'tcm_score',
      "value"             => $value
    );

  if($event_name){
    $data[0]["redcap_event_name"] = $event_name;
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);

  exit;
}

//SPECIAL CUSTOM MAT SCORINGCAPTURE
if(isset($_REQUEST["mat"])){
  include "MAT_scoring.php";
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = $_REQUEST["sid"] ?: null;
  $mat_answers  = $_REQUEST["mat_answers"] ?: null;
  $mat_answers  = json_decode($mat_answers,1);

  $matstring  = "";
  foreach($mat_answers as $fieldlabel => $values){
    $mat_key  = $values["vid"];
    $q_val    = $values["value"];
    $mat_category = $MAT_cat[$mat_key];
    $matvalue = getMATscoreCAT($mat_category,$q_val);
    $matstring .= $matvalue;
  }
  
  $matscore = isset($scoring[$matstring]) ? $scoring[$matstring] : 0 ;
  $data[]   = array(
      "record"            => $record_id,
      "field_name"        => 'mat_score',
      "value"             => $matscore
    );

  if($event_name){
    $data[0]["redcap_event_name"] = $event_name;
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
  $data   = array_shift($data);
  $data["matstring"] = $matstring;
  print_r( json_encode($data) );
  exit;
}

//SPECIAL CUSTOM MET SCORECAPTURE
if(isset($_REQUEST["met"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = $_REQUEST["sid"] ?: null;
  $value        = $_REQUEST["met_score"] ?: null;

  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'met_score',
      "value"             => $value
    );

  if($event_name){
    $data[0]["redcap_event_name"] = $event_name;
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
  print_r($data);
  exit;
}

//SPECIAL CUSTOM MET SCORECAPTURE
if(isset($_REQUEST["sleep"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = $_REQUEST["sid"] ?: null;
  $value        = $_REQUEST["met_score"] ?: null;

  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'psqi_score',
      "value"             => $value
    );

  if($event_name){
    $data[0]["redcap_event_name"] = $event_name;
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
  print_r($data);
  exit;
}

//SPECIAL CUSTOM MET SCORECAPTURE
if(isset($_REQUEST["ipaq"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $loggedInUser->user_event_arm;
  $survey_id    = $_REQUEST["sid"] ?: null;

  $ipaqscores   = $_REQUEST["ipaq_scores"] ?: null;
  $scores       = json_decode($ipaqscores,1);
  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'ipaq_total_walking',
      "value"             => $scores["ipaq_total_walking"]
    );
  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'ipaq_total_moderate',
      "value"             => $scores["ipaq_total_moderate"]
    );
  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'ipaq_total_vigorous',
      "value"             => $scores["ipaq_total_vigorous"]
    );
  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'ipaq_total_overall',
      "value"             => $scores["ipaq_total_overall"]
    );


  if(!empty($event_name)){
    $data[0]["redcap_event_name"] = $event_name;
    $data[1]["redcap_event_name"] = $event_name;
    $data[2]["redcap_event_name"] = $event_name;
    $data[3]["redcap_event_name"] = $event_name;
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
  print_r($data);
  exit;
}

//POSTING DATA TO REDCAP API
if(isset($_REQUEST["ajax"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = isset($_REQUEST["sid"]) ? $_REQUEST["sid"] : null;
  
  //IF DOING A END OF SURVEY FINAL SUBMIT
  if(isset($_REQUEST["surveycomplete"])){
    $result     = RC::callApi(array(
        "hash"    => $_REQUEST["hash"], 
        "format"  => "csv"
      ), true, $custom_surveycomplete_API, $API_TOKEN);
  }

  //WRITE TO API
  //ADD OVERIDE PARAMETER 
  unset($_POST["project"]);
  foreach($_POST as $field_name => $value){
    if($value === 0){
      $value = 0;
    }else if($value == ""){
      $value = NULL;
    }

    $record_id  = $loggedInUser->id;
    $event_name = $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

    $is_date    = preg_match('/^\d{2}-\d{2}\-\d{4}$/', $value);
    if($is_date){
      list($mm,$dd,$yyyy) = explode("-",$value);
      $value = "$yyyy-$mm-$dd";
    }

    $data[] = array(
      "record"            => $record_id,
      "field_name"        => $field_name,
      "value"             => $value
    );

    if($event_name){
      $data[0]["redcap_event_name"] = $event_name;
    }
    $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
    // print_r($data);
    // print_r($result);
  }
  exit;
}

//GET THE CURRENT TOP NAV CATEGORy
$nav    = isset($_REQUEST["nav"]) ? $_REQUEST["nav"] : "home";
$navon  = array("home" => "", "reports" => "", "game" => "");
$navon[$nav] = "on";

//IF CORE SURVEY GET THE SURVEY ID
$avail_surveys      = $available_instruments;
$first_core_survey  = array_splice($avail_surveys,0,1);
$sid = $current_surveyid = isset($_REQUEST["sid"]) ? $_REQUEST["sid"] : "";

$surveyon       = array();
$surveynav      = array_merge($first_core_survey, $supp_surveys_keys);
foreach($surveynav as $surveyitem){
    $surveyon[$surveyitem] = "";
}
if(!empty($sid)){
    $navon[$nav] = "";
    if(!array_key_exists($sid,$surveyon)){
        $surveyon["wellbeing_questions"] = "on";
    }else{
        $surveyon[$sid] = "on";   
    }
}

// IF SUPP SURVEY GET PROJECT TOO
$pid = $project = isset($_REQUEST["project"]) ? $_REQUEST["project"] : "";
if(!empty($pid)){
    if(array_key_exists($pid, SurveysConfig::$projects)){
        $supp_project = $supp_surveys[$pid]->getSingleInstrument($sid);
        $surveys      = $supp_surveys[$pid]->getActiveAll();
    }

    if(!array_key_exists($sid,$surveyon)){
      //ONLY ONE MENUITEM HIGHLIGHTED
      $surveyon["wellbeing_questions"] = "on";
    }
}else{
    //ITS A CORESURVEY, FIND THE LATEST INCOMPLETE ONE
    foreach($surveys as $surveyid => $survey){
      $surveycomplete = $survey["survey_complete"];
      if(!$surveycomplete){
        $sid = $current_surveyid = $surveyid;
        break;
      }
    }
}

//GET THE SURVEY DATA
if(array_key_exists($sid, $surveys)){
    $survey_data = $surveys[$sid];

    //ON SURVEY PAGE STORE THIS FOR USE WITH THE AJAX EVENTS 
    $_SESSION[SESSION_NAME]["survey_context"] = array("event" => $survey_data["event"]);

    //LOAD UP THE SURVEY PRINTER HERE
    $active_survey  = new Survey($survey_data);
}else{
  //IF BAD SURVEY ID PASSED, REDIRECT BACK TO DASHBOARD
  $destination = $websiteUrl."/index.php";
  header("Location: " . $destination);
  exit; 
}

//POP UP IN BETWEEN SURVEYS 
//NEEDS TO GO BELOW SUPPLEMENTALL PROJECTS WORK FOR NOW
if(isset($_GET["survey_complete"])){
  //ONLY SHOW THESE POPUPS FOR LONG ANNIVERSARIES
  if(!strpos($user_event_arm,"short") && strpos($user_event_arm,"short") !== 0){

    //ONLY LONG ANNIVERSARIES GET POP UP TREATMENT
    //IF NO URL PASSED IN THEN REDIRECT BACK
    $surveyid = $_GET["survey_complete"];
    
    if(array_key_exists($surveyid,$surveys)){
      $index        = array_search($surveyid, $all_survey_keys);
      $survey       = $surveys[$surveyid];
      $success_msg  = $lang["YOUVE_BEEN_AWARDED"] . " : <span class='fruit " . $fruits[$index] . "'></span> " ;
      if(isset($all_survey_keys[$index+1])){
        $nextlink     = "survey.php?sid=". $all_survey_keys[$index+1];
        $success_msg .= $lang["GET_WHOLE_BASKET"];
        addSessionMessage( $success_msg , "success");
      }
    }

    if(array_key_exists($surveyid,$supp_surveys)){
      $index  = array_search($surveyid, $supp_surveys_keys);
      $survey = $supp_surveys[$surveyid];
      $success_msg  = $lang["FITNESS_BADGE"]. ": <span class='fitness " . SurveysConfig::$fitness[$index] . "'></span>" ;
      if(isset($all_survey_keys[$index+1])){
        $success_msg .= $lang["GET_ALL_BADGES"]. "<br> ";
        addSessionMessage( $success_msg , "success");
      }
    }
  }
}

$pageTitle = "Well v2 Survey";
$bodyClass = "survey";
include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
            <article class="surveyFrame">
                <?php
                  if(!$active_survey->surveycomplete){
                      ?>
                      <div id="survey_progress" class='progress progress-striped active'>
                        <div class='progress-bar bg-info lter' data-toggle='tooltip' data-original-title='<?php echo $active_survey->surveypercent?>%' style='width: <?php echo $active_survey->surveypercent?>%'>Survey Progress</div>
                      </div>
                      <?php    
                  }
                  //PRINT OUT THE HTML FOR THIS SURVEY
                  $active_survey->printHTML($survey_data["event"], $sid);
                ?>
            </article>
            <?php 
            include_once("models/inc/gl_surveynav.php");
            ?>
        </div> <!-- #main -->
    </div> <!-- #main-container -->
<?php 
include_once("models/inc/gl_foot.php");
?>
<script src="assets/js/custom_assessments.js"></script>
<script>
<?php
  //TODO : MOVE THE FRUIT GIVING TO SURVEY PAGES
  $index      = array_search($current_surveyid, $all_survey_keys);
  $nextsurvey = $project == "Supp" ? null : (isset($all_survey_keys[$index+1]) ? $all_survey_keys[$index+1] : null);

  echo "$('#customform').attr('data-next','". $nextsurvey ."');\n\n";

  $isMET    = $sid == "how_fit_are_you"                                     ? "true" : "false";
  $isMAT    = $sid == "how_physically_mobile_are_you"                       ? "true" : "false";
  $isTCM    = $sid == "find_out_your_body_type_according_to_chinese_medic"  ? "true" : "false";
  $isGRIT   = $sid == "how_resilient_are_you_to_stress"                     ? "true" : "false";
  $isSleep  = $sid == "how_well_do_you_sleep"                               ? "true" : "false";
  $isIPAQ   = $sid == "international_physical_activity_questionnaire"       ? "true" : "false";
  echo "var isMET               = $isMET ;\n";
  echo "var isMAT               = $isMAT ;\n";
  echo "var isTCM               = $isTCM ;\n";
  echo "var isGRIT              = $isGRIT ;\n";
  echo "var isSleep             = $isSleep ;\n";
  echo "var isIPAQ              = $isIPAQ ;\n";
  echo "var uselang             = ".(isset($_SESSION["use_lang"]) ? "'".$_SESSION["use_lang"]."'" : "'en'").";\n";

  //THIS IS A CONFusINg FUNCTION
  //BUT SINCE THERE ARE CONDITiONALS THAT SPAN INSTRUMENTS OR EVEN PROJECTS, GOTTA TRACK EM  ALL
  //THE $all_branching is done in surveys.php
  $branching_function =  "function checkGeneralBranching(){\n";
    foreach($all_branching as $branch){
      $andor      = $branch["andor"];
      $affected   = $branch["affected"];
      $effectors  = array();
      $ef_only    = array();
      foreach($branch["effector"] as $ef => $values){
        array_push($ef_only, "all_completed.hasOwnProperty('$ef')");
        
        $temp_arr = array();
        foreach($values as $value){
          $temp_arr[] = " all_completed['$ef'] == $value ";
        }
        $effectors[] = "(".implode(" || ",$temp_arr).")";
      }

      $branching_function .= "if((".implode(" $andor ", $ef_only).") && (".implode(" $andor ", $effectors).")){\n";
      $branching_function .= "\$('.$affected').slideDown('medium');\n";
      $branching_function .= "}else{\n";
      $branching_function .= "\$('.$affected').slideUp('fast');\n";
      $branching_function .= "}\n";
    }

  $branching_function .= "return;\n";
  $branching_function .= "}\n";

  echo $branching_function;

  $all_completed = array_merge($all_completed, $active_survey->completed);
  // //PASS FORMS METADATA 
  echo "var total_questions     = " . $active_survey->surveytotal . ";\n";
  echo "var user_completed      = " . json_encode($active_survey->completed) . ";\n";
  echo "var all_completed       = " . json_encode($all_completed) .";\n";
  echo "var all_branching       = " . json_encode($all_branching).";\n";
  echo "var completed_count     = " . count($active_survey->completed) . ";\n";
  echo "var surveyhash          = '".http_build_query($active_survey->hash)."';\n";
  echo "var form_metadata       = " . json_encode($active_survey->raw) . ";\n";
  echo "var MET_DATA_DISCLAIM   = '".lang("MAT_DATA_DISCLAIM")."';";
  echo "var mat_score_desc = {
           40  : '".lang("MAT_SCORE_40")."'
          ,50  : '".lang("MAT_SCORE_50")."'
          ,60  : '".lang("MAT_SCORE_60")."'
          ,70  : '".lang("MAT_SCORE_70")."'
        };";
    // echo "console.log(".json_encode($all_completed).");";
?>
  //LAUNCH IT INITIALLY TO CHECK IF PAGE HAS BRANCHING
  // checkGeneralBranching();

  //CUSTOM SCORING FOR MET / MAT / TCM SURVEYS
  var mat_map = {
     "mat_walkonground"          : {"vid" : "Flat_NoRail_Slow" , "value" : null } 
    ,"mat_walkonground_fast"     : {"vid" : "Flat_NoRail_Fast" , "value" : null } 
    ,"mat_jogonground"           : {"vid" : "Flat_NoRail_Jog" , "value" : null } 
    ,"mat_walkincline_handrail"  : {"vid" : "Ramp_12Pcnt_Rail_Med" , "value" : null } 
    ,"mat_walkincline"           : {"vid" : "Ramp_12Pcnt_NoRail_Med" , "value" : null } 
    ,"mat_stepover_lowhurdle"    : {"vid" : "Walk_Hurdles_1" , "value" : null } 
    ,"mat_walkincline_tern"      : {"vid" : "Terrain_4" , "value" : null } 
    ,"mat_walkincline_tern_fast" : {"vid" : "Terrain_5" , "value" : null } 
    ,"mat_walkup3_handrail"      : {"vid" : "Stairs_3Step_1Foot_Rail_MedSlo2" , "value" : null } 
    ,"mat_walkdn3"               : {"vid" : "DownStairs_3Step_2Foot_NoRail_Slow" , "value" : null } 
    ,"mat_walkup3_carry"         : {"vid" : "Bag_Stairs_3Step_1Foot_NoRail_2_3" , "value" : null } 
    ,"mat_walkup9_carry"         : {"vid" : "TWObag_stairs_9step_1foot_norail" , "value" : null } 
  };

  var tcm_req = [
     ['tcm_energy','tcm_optimism','tcm_weight','tcm_stool','tcm_loosestool','tcm_stickystool']
    ,['tcm_energy','tcm_voice','tcm_panting','tcm_tranquility','tcm_colds','tcm_pasweat']
    ,['tcm_handsfeet_cold','tcm_cold_aversion','tcm_sensitive_cold','tcm_cold_tolerant','tcm_pain_eatingcold','tcm_sleepwell']
    ,['tcm_handsfeet_hot','tcm_face_hot','tcm_dryskin','tcm_dryeyes','tcm_constipated','tcm_drylips']
    ,['tcm_sleepy','tcm_sweat','tcm_oily_forehead','tcm_eyelid','tcm_snore','tcm_naturalenv']
    ,['tcm_frustrated','tcm_nose','tcm_acne','tcm_bitter','tcm_ribcage','tcm_scrotum']
    ,['tcm_forget','tcm_bruises_skin','tcm_capillary_cheek','tcm_complexion','tcm_darkcircles','tcm_bodyframe']
    ,['tcm_depressed','tcm_anxious','tcm_melancholy','tcm_scared','tcm_suspicious','tcm_breastpain']
    ,['tcm_sneeze','tcm_cough','tcm_allergies','tcm_hives','tcm_skin_red']
  ];

  function isEmpty(v){
    return v == null || v == undefined;
  }

  var breaklength = 6000; //100000 = 10 minutes
  // var takeBreak = setTimeout(SessionExpireEvent, 300000);
  function SessionExpireEvent() {
      var reqmsg  = $("<div>").addClass("required_message alert alert-info").html("<ul><li>We recommend taking a periodic breaks from looking at the computer screen to reduce eye strain and fatigue.  <br>Click 'Close' to continue survey.</li></ul>");
      reqmsg.append($("<button>").addClass("btn btn-alert takebreak").text("Close").click(function(){
        $("section.vbox").removeClass("blur");
        var takeBreak = setTimeout(SessionExpireEvent, 300000);
      }));
      $("body").append(reqmsg);
      $("section.vbox").addClass("blur");
  }
</script>
<script src="assets/js/survey.js"></script>
<?php
