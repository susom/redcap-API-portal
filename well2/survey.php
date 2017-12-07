<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");
include("dashboard/inc/classes/Survey.php");

$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];

//GET THE CURRENT TOP NAV CATEGORy
$nav    = isset($_REQUEST["nav"]) ? $_REQUEST["nav"] : "home";
$navon  = array("home" => "", "reports" => "", "game" => "");
$navon[$nav] = "on";

//IF CORE SURVEY GET THE SURVEY ID
$sid    = isset($_REQUEST["sid"]) ? $_REQUEST["sid"] : "";
$surveyon       = array();
$surveynav      = array_merge(array_splice($available_instruments,0,1), $supp_surveys_keys);
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
$pid    = isset($_REQUEST["project"]) ? $_REQUEST["project"] : "";
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
        $sid = $surveyid;
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

$pageTitle = "Well v2 Survey";
$bodyClass = "survey";
include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
            <article>
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
<script>
<?php
  //TODO : MOVE THE FRUIT GIVING TO SURVEY PAGES
  $index      = array_search($current_surveyid, $all_survey_keys);
  $nextsurvey = $project == "Supp2" ? null : (isset($all_survey_keys[$index+1]) ? $all_survey_keys[$index+1] : null);

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
<?php
