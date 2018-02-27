<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");
include("models/inc/scoring_functions.php");

$nav    = isset($_REQUEST["nav"]) ? $_REQUEST["nav"] : "home";
$navon  = array("home" => "", "reports" => "", "game" => "");
$navon[$nav] = "on";

$avail_surveys      = $available_instruments;
$first_core_survey  = array_splice($avail_surveys,0,1);
$surveyon           = array();
$surveynav          = array_merge($first_core_survey, $supp_surveys_keys);
foreach($surveynav as $surveyitem){
    $surveyon[$surveyitem] = "";
}

$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];
$extra_params   = array();
$loc            = !isset($_REQUEST["loc"])  ? 1 : 2; //1 US , 2 Taiwan
$cats           = array(0,1);
foreach($cats as $cat){
    $filterlogic                    = array();
    $filterlogic[]                  = '[well_cms_loc] = "'.$loc.'"';
    $filterlogic[]                  = '[well_cms_catagory] = "'.$cat.'"';
    $filterlogic[]                  = '[well_cms_active] = "1"';
    $extra_params["filterLogic"]    = implode(" and ", $filterlogic);
    $events                         = RC::callApi($extra_params, true, $API_URL, $API_TOKEN); 
    if($cat == 0){
        //is events
        $cats[0] = array();
        foreach($events as $event){
            $recordid   = $event["id"];
            $eventpic   = "";
            $file_curl  = RC::callFileApi($recordid, "well_cms_pic", null, $API_URL,$API_TOKEN);
            if(strpos($file_curl["headers"]["content-type"][0],"image") > -1){
              $split    = explode("; ",$file_curl["headers"]["content-type"][0]);
              $mime     = $split[0];
              $split2   = explode('"',$split[1]);
              $imgname  = $split2[1];
              $eventpic = '<img class="event_img" src="data:'.$mime.';base64,' . base64_encode($file_curl["file_body"]) . '">';
            }

            $order = intval($event["well_cms_displayord"]) - 1;
            if($order == 0 && $core_surveys_complete){
                //first event is only for core survey incomplete people
                continue;
            }
            $cats[0][$order] = array(
                 "subject"  => $event["well_cms_subject"] 
                ,"content"  => $event["well_cms_content"] 
                ,"pic"      => $eventpic
                ,"link"     => $event["well_cms_event_link"] 
            );
        }
        ksort($cats[0]);
    }else{
        $recordid   = $events[0]["id"];
        $eventpic   = "";
        $file_curl  = RC::callFileApi($recordid, "well_cms_pic", null, $API_URL,$API_TOKEN);
        if(strpos($file_curl["headers"]["content-type"][0],"image") > -1){
          $split    = explode("; ",$file_curl["headers"]["content-type"][0]);
          $mime     = $split[0];
          $split2   = explode('"',$split[1]);
          $imgname  = $split2[1];
          $eventpic = "data:".$mime.";base64,". base64_encode($file_curl["file_body"]);
        }
        $cats[1] = array(
             "subject"  => $events[0]["well_cms_subject"] 
            ,"content"  => $events[0]["well_cms_content"] 
            ,"pic"      => $eventpic 
        );
    }
}

//CALCULATE WELL SCORES
if($core_surveys_complete){
  if(!$user_short_scale){
    // CHECK IF EXISTING LONG SCORE
    $extra_params = array(
      'content'     => 'record',
      'records'     => array($loggedInUser->id) ,
      'fields'      => array("id","well_long_score_json"),
      'events'      => $user_event_arm
    );
    $user_ws      = RC::callApi($extra_params, true, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN); 

    if(!isset($user_ws[0]) || (isset($user_ws[0]) && empty( json_decode($user_ws[0]["well_long_score_json"],1) )) ){
      //10 DOMAINS TO CALCULATE THE WELL LONG SCORE
      $domain_mapping = array(
         "well_score_creativity" => "Exploration and Creativity"
        ,"well_score_religion"   => "Spirituality and Religion"
        ,"well_score_financial"  => "Financial Security"
        ,"well_score_purpose"    => "Purpose and Meaning"
        ,"well_score_health"     => "Physical Health"
        ,"well_score_senseself"  => "Sense of Self"
        ,"well_score_emotion"    => "Experience of Emotions"
        ,"well_score_stress"     => "Stress and Resilience"
        ,"well_score_social"     => "Social Connectedness"
        ,"lifestyle"             => "Lifestyle Behaviors"
      );

      $domain_fields  = array(
         "well_score_creativity"      => array("core_engage_oppo") 
        ,"well_score_religion"        => array("core_religious_beliefs")
        ,"well_score_financial"       => array("core_money_needs")

        ,"well_score_purpose"         => array("core_contribute_doing"
                                              ,"core_contribute_alive")

        ,"well_score_health"            => array("core_fitness_level"
                                              ,"core_health_selfreported"
                                              ,"core_physical_illness"
                                              ,"core_energy_level"
                                              ,"core_interfere_life")

        ,"well_score_senseself"       => array("core_true_person"
                                              ,"core_accepting_yourself"
                                              ,"core_satisfied_yourself"
                                              ,"core_capable"
                                              ,"core_daily_activities")

        ,"well_score_emotion"         => array("core_calm"
                                              ,"core_content"
                                              ,"core_drained"
                                              ,"core_excited"
                                              ,"core_frustrated"
                                              ,"core_happy"
                                              ,"core_hopeless"
                                              ,"core_joyful"
                                              ,"core_sad"
                                              ,"core_secure"
                                              ,"core_worried")

        ,"well_score_stress"      => array("core_bounce_back"
                                              ,"core_adapt_change"
                                              ,"core_deal_whatever"
                                              ,"core_humorous_side"
                                              ,"core_overcome_obstacles"
                                              ,"core_focused_pressure"
                                              ,"core_strong_person"
                                              ,"core_unpleasant_feelings"
                                              ,"core_disheartened_setbacks"
                                              ,"core_important_time"
                                              ,"core_confident_psnlproblem"
                                              ,"core_going_way"
                                              ,"core_overwhelm_difficult"
                                              ,"core_important_energy")

        ,"well_score_social"       => array("core_lack_companionship"
                                              ,"core_left_out"
                                              ,"core_isolated_others"
                                              ,"core_tune_people"
                                              ,"core_people_talk"
                                              ,"core_people_rely"
                                              ,"core_drained_helping"
                                              ,"core_people_close"
                                              ,"core_group_friends"
                                              ,"core_people_upset"
                                              ,"core_meet_expectations"
                                              ,"core_energized_help"
                                              ,"core_help")

        ,"lifestyle"                => array("core_lpaq"
                                              ,"core_sleep_total", "core_sleep_hh", "core_sleep_mm"
                                              ,"core_fallasleep_min"
                                              ,"core_fallasleep"
                                              ,"core_wokeup"
                                              ,"core_wokeup_early"
                                              ,"core_wokeup_unrefresh"
                                              ,"core_sleep_quality"
                                              ,"core_vegatables_intro_v2"
                                              ,"core_fruit_intro_v2"
                                              ,"core_grain_intro_v2"
                                              ,"core_bean_intro_v2"
                                              ,"core_sweet_intro_v2"
                                              ,"core_meat_intro_v2"
                                              ,"core_nuts_intro_v2"
                                              ,"core_sodium_intro_v2"
                                              ,"core_sugar_intro_v2"
                                              ,"core_fish_intro_v2"
                                              ,"core_cook_intro_v2"
                                              ,"core_fastfood_intro_v2"
                                              ,"core_bngdrink_female_freq"
                                              ,"core_bngdrink_male_freq"
                                              ,"core_smoke_100"
                                              ,"core_smoke_freq")
      );
      
      //JUST GET THE INDIVIDUAL FIELDS
      $q_fields   = array();
      foreach($domain_fields as $domains){
        $q_fields = array_merge($q_fields, array_values($domains));
      }

      //INTERSECT ALL USER COMPLETED FIELDS WITH THE REQUIRED ONES TO GET THE USER ANSWERS
      $user_completed_keys = array_filter(array_intersect_key( $all_completed, array_flip($q_fields) ),function($v){
          return $v !== false && !is_null($v) && ($v != '' || $v == '0');
      });

      //MAKE SURE THAT AT LEAST 70% OF THE FIELDS IN EACH DOMAIN IS COMPLETE OR ELSE CANCEL THE SCORING
      $minimumData = true;
      foreach($domain_fields as $domain => $fields){
        $dq_threshold   = ceil(count($fields) * .3);
        $missing_keys   = array_diff($fields, array_keys($user_completed_keys)) ;
        if(count($missing_keys) >= $dq_threshold
           || (!isset($user_completed_keys["core_lpaq"]) 
              || (!isset($user_completed_keys["core_bngdrink_female_freq"]) && !isset($user_completed_keys["core_bngdrink_male_freq"]) 
              || (!isset($user_completed_keys["core_smoke_100"]) || (isset($user_completed_keys["core_smoke_100"]) && $user_completed_keys["core_smoke_100"] != 0 && !isset($user_completed_keys["core_smoke_freq"]))   ) ))
          ){
          $minimumData  = false;
        }
      }

      if($minimumData){
        $q_fields = array_merge($q_fields, array("core_vegetables_intro_v2_1"
                                                ,"core_vegetables_intro_v2_2"
                                                ,"core_vegetables_intro_v2_3"
                                                ,"core_fruit_intro_v2_1"
                                                ,"core_fruit_intro_v2_2"
                                                ,"core_fruit_intro_v2_3"
                                                ,"core_grain_intro_v2_1"
                                                ,"core_grain_intro_v2_2"
                                                ,"core_grain_intro_v2_3"
                                                ,"core_bean_intro_v2_1"
                                                ,"core_bean_intro_v2_2"
                                                ,"core_bean_intro_v2_3"
                                                ,"core_sweet_intro_v2_1"
                                                ,"core_sweet_intro_v2_2"
                                                ,"core_sweet_intro_v2_3"
                                                ,"core_meat_intro_v2_1"
                                                ,"core_meat_intro_v2_2"
                                                ,"core_meat_intro_v2_3"
                                                ,"core_nuts_intro_v2_1"
                                                ,"core_nuts_intro_v2_2"
                                                ,"core_nuts_intro_v2_3"
                                                ,"core_sodium_intro_v2_1"
                                                ,"core_sodium_intro_v2_2"
                                                ,"core_sodium_intro_v2_3"
                                                ,"core_sugar_intro_v2_1"
                                                ,"core_sugar_intro_v2_2"
                                                ,"core_sugar_intro_v2_3"
                                                ,"core_fish_intro_v2_1"
                                                ,"core_fish_intro_v2_2"
                                                ,"core_fish_intro_v2_3"
                                                ,"core_cook_intro_v2_1"
                                                ,"core_cook_intro_v2_2"
                                                ,"core_cook_intro_v2_3"
                                                ,"core_fastfood_intro_v2_1"
                                                ,"core_fastfood_intro_v2_2"
                                                ,"core_fastfood_intro_v2_3"
                                              ) );

        // DAMNIT TOHELL, GOTTA DO THIS PROCESS AGAIN SINCE THE ABOVE ISNT USED FOR THE "minimum data"
        $user_completed_keys = array_filter(array_intersect_key( $all_completed, array_flip($q_fields) ),function($v){
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });

        $long_scores = getLongScores($domain_fields, $user_completed_keys);
      }else{
        $long_scores = array();
      }

      // save individual scores
      foreach($long_scores as $redcap_var => $value){
        if($redcap_var == "ls_sub_domains"){
          foreach($value as $rc_var => $val){
            $data = array(
              "record"            => $loggedInUser->id,
              "field_name"        => $rc_var,
              "value"             => $val,
              "redcap_event_name" => $user_event_arm
            );
            $result =  RC::writeToApi(array($data), array("overwriteBehavior" => "overwite", "type" => "eav"), $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
          }
        }elseif($redcap_var == "lifestyle"){
          // do nothing
        }else{
          $data = array(
            "record"            => $loggedInUser->id,
            "field_name"        => $redcap_var,
            "value"             => $value,
            "redcap_event_name" => $user_event_arm
          );
          $result = RC::writeToApi(array($data), array("overwriteBehavior" => "overwite", "type" => "eav"), $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
        }
      }

      // save the entire block as json
      array_pop($long_scores);
      $remapped_long_scores = array();
      foreach($long_scores as $rc_var => $value){
        $remapped_long_scores[$domain_mapping[$rc_var]] = $value;
      }
      $data = array(
        "record"            => $loggedInUser->id,
        "field_name"        => "well_long_score_json",
        "value"             => json_encode($remapped_long_scores),
        "redcap_event_name" => $user_event_arm
      );
      $result = RC::writeToApi(array($data), array("overwriteBehavior" => "overwite", "type" => "eav"), $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
      $data = array(
        "record"            => $loggedInUser->id,
        "field_name"        => "well_score_long",
        "value"             => round(array_sum($remapped_long_scores),2),
        "redcap_event_name" => $user_event_arm
      );
      $result = RC::writeToApi(array($data), array("overwriteBehavior" => "overwite", "type" => "eav"), $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
    }
  }else{
    //CHECK IF EXISTING SHORT SCORE
    $extra_params = array(
      'content'     => 'record',
      'records'     => array($loggedInUser->id) ,
      'fields'      => array("id","well_score"),
      'events'      => $user_event_arm
    );
    $user_ws      = RC::callApi($extra_params, true, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN); 

    // ONLY WANT TO SHOW IT IF AT LEAST THE 1st anniversary WAS COMPLETED
    if( !count($user_ws) ){
      //CALCULATE WELL_SCORE FOR CURRENT USER IF NOT ALREADY STORED
      //SHORT SCALE SCORE
      $short_q_fields  = array(
         //SOCIAL CONNECTEDNESS
         "core_lack_companionship"
        ,"core_people_upset"
        ,"core_energized_help"

        //Lifestyle BEHAVIORS
        ,"core_vegatables_intro"
        ,"core_vegatables_intro_v2"
        ,"core_vegetables_intro_v2_1"
        ,"core_vegetables_intro_v2_2"
        ,"core_vegetables_intro_v2_3"
        ,"core_sugar_intro"
        ,"core_sugar_intro_v2"
        ,"core_sugar_intro_v2_1"
        ,"core_sugar_intro_v2_2"
        ,"core_sugar_intro_v2_3"
        ,"core_lpaq"
        ,"core_smoke_100"
        ,"core_smoke_freq"
        ,"core_sleep_quality"
        ,"core_bngdrink_female_freq"
        ,"core_bngdrink_male_freq"

        //STRESS AND RESILIENCE
        ,"core_important_time"
        ,"core_deal_whatever"

        //EXPERIENCE OF EMOTIONS
        ,"core_joyful"
        ,"core_worried"

        //PHYSICAL HEALTH
        ,"core_fitness_level"

        //PURPOSE AND MEANING
        ,"core_contribute_doing"

        //SENSE OF SELF
        ,"core_satisfied_yourself"

        //FINANCIAL SECURITY/SATISFACTION
        ,"core_money_needs"

        //SPIRITUALITY AND RELIGION
        ,"core_religious_beliefs"

        //EXPLORATION AND CREATIVITY
        ,"core_engage_oppo"
      );

      $short_circuit_diff_ar = array(
         "core_contribute_doing" => 1
        ,"core_satisfied_yourself" => 1
        ,"core_money_needs" => 1
        ,"core_religious_beliefs" => 1
        ,"core_engage_oppo" => 1
        ,"core_fitness_level" => 1
        ,"core_important_time" => 1
        ,"core_deal_whatever" => 1
        ,"core_joyful" => 1
        ,"core_worried" => 1
        ,"core_lack_companionship" => 1
        ,"core_people_upset" => 1
        ,"core_energized_help" => 1
        ,"core_lpaq" => 1
        ,"core_vegatables_intro_v2" => 1
        ,"core_sugar_intro_v2" => 1
        ,"core_smoke_100" => 1
        ,"core_sleep_quality" => 1
      );

      $arms_answers = array();

      $user_answers   = $user_survey_data->getUserAnswers($loggedInUser->id,$short_q_fields,$user_event_arm);
      $user_completed_keys        = array_filter(array_intersect_key( $user_answers[0],  array_flip($short_q_fields)),function($v){
        return $v !== false && !is_null($v) && ($v != '' || $v == '0');
      });
      $missing_data_keys          = array_diff_key($short_circuit_diff_ar,$user_completed_keys);
      $minimumData                = checkMinimumForShortScore($missing_data_keys);

      //ENOUGH DATA TO CALC SCORE
      $arms_answers[$user_event_arm] = $minimumData ? $user_completed_keys : array();
      $short_scores = getShortScores($arms_answers);
      if(isset($short_scores[$user_event_arm])){
        $score  = round(array_sum($short_scores[$user_event_arm]));
        $data[] = array(
          "record"            => $loggedInUser->id,
          "field_name"        => "well_score",
          "value"             => $score,
          "redcap_event_name" => $user_event_arm
        );
        $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
      }
    }
  }
}

//NEEDS TO GO BELOW SHORTSCALE WORK FOR NOW
if(isset($_GET["survey_complete"])){
  //IF NO URL PASSED IN THEN REDIRECT BACK
  $surveyid = $_GET["survey_complete"];
  if(array_key_exists($surveyid,$surveys)){
    $index  = array_search($surveyid, $all_survey_keys);
    $survey = $surveys[$surveyid];

    if(!isset($all_survey_keys[$index+1])){ 
      //TODO PUT THIS INTO A FUNCTION OR SOMEWHERE
      $get_well_score = $user_short_scale ? $short_score : $long_scores;
      if($user_short_scale){
        $for_popup        = array_slice($get_well_score, -1);
        $new_well_score   = round((array_sum($for_popup[$user_event_arm])/50)*100);
      }else{
        $new_well_score   = round(array_sum($get_well_score)) . "/100";
      }
      $show_well_score  = "<p>Your WELL Score for $current_year is $new_well_score</p>";

      // will pass $arm_year into the include
      require_once('PDF/fpdf181/fpdf.php');
      require_once('PDF/FPDI-2.0.1/src/autoload.php');
      include_once("PDF/generatePDFcertificate.php");
    
      $success_msg    = $lang["CONGRATS_FRUITS"] . "$show_well_score<a target='blank' href='$filename'>[Click here to download your certificate!]</a>";
      addSessionMessage( $success_msg , "success");
    }
  }
}

$pageTitle = "Well v2 Home Page";
$bodyClass = "home";
include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
            <article>
                <h3>How can I enhance my wellbeing?</h3>
                <?php  
                if(isset($cats[0])){
                    foreach($cats[0] as $event){
                ?>
                    <section>
                        <figure>
                            <?php echo $event["pic"] ?>
                            <figcaption>
                                <h2><?php echo $event["subject"] ?></h2>
                                <p><?php echo $event["content"] ?></p>
                                <?php
                                if(!empty($event["link"])){
                                ?>
                                <a href="<?php echo $event["link"] ?>">Read More</a>
                                <?php
                                }
                                ?>
                            </figcaption>
                        </figure>
                    </section>
                <?php 
                    }
                }
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
