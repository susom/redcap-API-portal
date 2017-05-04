<?php
require_once("../models/config.php");

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
  print_r($data);
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
    print_r($data);
    print_r($result);
  }
  exit;
}

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) { 
  $destination = $websiteUrl."login.php";
  header("Location: " . $destination);
  exit; 
}else{
  // GET $surveys
  include("../models/inc/surveys.php");
  include("inc/classes/Survey.php");
}

//THIS PAGE NEEDS A SURVEY ID
$surveyid = $sid = $_GET["sid"];
$project  = (isset($_GET["project"])? $_GET["project"]:null);

if($project){
  if(array_key_exists($project, SurveysConfig::$projects)){
    $supp_project = $supp_surveys[$project]->getSingleInstrument($surveyid);
    $surveys      = $supp_surveys[$project]->getActiveAll();
  }
}

if(array_key_exists($surveyid, $surveys)){
  $survey_data    = $surveys[$surveyid];

  //ON SURVEY PAGE STORE THIS FOR USE WITH THE AJAX EVENTS 
  $_SESSION[SESSION_NAME]["survey_context"] = array("event" => $survey_data["event"]);

  //LOAD UP THE SURVEY PRINTER HERE
  $active_survey  = new Survey($survey_data);
}else{
  //IF BAD SURVEY ID PASSED, REDIRECT BACK TO DASHBOARD
  $destination = $websiteUrl."dashboard/index.php";
  header("Location: " . $destination);
  exit; 
}

//SOME PAGE SET UP
$shownavsmore   = false;
$survey_active  = ' class="active"';
$studies_active = '';
$profile_active = '';
$game_active    = '';
$assesments     = '';
$pg_title       = "Surveys : $websiteName";
$body_classes   = "dashboard survey";
include("inc/gl_head.php");
?>
  <section class="vbox">
    <?php 
      include("inc/gl_header.php"); 
    ?>
    <section>
      <section class="hbox stretch">
        <?php 
          include("inc/gl_sidenav.php"); 
        ?>
        <section id="content">
          <section class="hbox stretch">
            <section>
              <section class="vbox">
                <section class="scrollable padder">              
                  <section class="row m-b-md">
                    <h2></h2>
                  </section>
                  <div class="row">
                    <div class="col-sm-1">&nbsp;</div>
                    <div class="col-sm-10 surveyFrame">
                    <?php
                      if(!$active_survey->surveycomplete){
                          ?>
                          <div id="survey_progress" class='progress progress-striped active'>
                            <div class='progress-bar bg-info lter' data-toggle='tooltip' data-original-title='<?php echo $active_survey->surveypercent?>%' style='width: <?php echo $active_survey->surveypercent?>%'>Survey Progress</div>
                          </div>
                          <?php    
                      }
                      //PRINT OUT THE HTML FOR THIS SURVEY
                      $active_survey->printHTML();
                    ?>
                    </div>
                    <div class="col-sm-1">&nbsp;</div>
                  </div>
                </section>
              </section>
            </section>
            <?php
              include("inc/gl_slideout.php");
            ?>
          </section>
          <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen,open" data-target="#nav,html"></a>
        </section>
      </section>
    </section>
  </section>
<?php
include("inc/gl_foot.php");
?>
<script>
<?php
  $isMET    = $sid == "how_fit_are_you"                                     ? "true" : "false";
  $isMAT    = $sid == "how_physically_mobile_are_you"                       ? "true" : "false";
  $isTCM    = $sid == "find_out_your_body_type_according_to_chinese_medic"  ? "true" : "false";
  $isGRIT   = $sid == "how_resilient_are_you_to_stress"                     ? "true" : "false";
  $isSleep  = $sid == "how_well_do_you_sleep"                               ? "true" : "false";
  echo "var isMET               = $isMET ;\n";
  echo "var isMAT               = $isMAT ;\n";
  echo "var isTCM               = $isTCM ;\n";
  echo "var isGRIT              = $isGRIT ;\n";
  echo "var isSleep             = $isSleep ;\n";
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
      var reqmsg  = $("<div>").addClass("required_message alert alert-info").html("<ul><li>We recommend taking a periodic breaks from looking at the computer screen to reduce eye strain and fatigue.  <br>Click 'Close' to continue survey.<li></ul>");
      reqmsg.append($("<button>").addClass("btn btn-alert takebreak").text("Close").click(function(){
        $("section.vbox").removeClass("blur");
        var takeBreak = setTimeout(SessionExpireEvent, 300000);
      }));
      $("body").append(reqmsg);
      $("section.vbox").addClass("blur");
  }
</script>
<script src="js/survey.js"></script>
