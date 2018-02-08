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

if(!$user_short_scale){
  //GATHER DATA FOR DATA VISUALIZATIONS
  //FOR THE PIE CHART
  $graph_fields               = array(
                                   "core_sitting_hh"
                                  ,"core_sitting_mm"
                                  ,"core_sitting_nowrk_hh"
                                  ,"core_sitting_nowrk_mm"
                                  ,"core_sitting_weekend_hh"
                                  ,"core_sitting_weekend_mm"
                                  ,"core_walking_hh"
                                  ,"core_walking_mm"
                                  ,"core_pa_mod_hh"
                                  ,"core_pa_mod_mm"
                                  ,"core_pa_vig_hh"
                                  ,"core_pa_vig_mm"
                                  ,"core_sleep_hh"
                                  ,"core_sleep_mm"
                                );
  $instrument_event           = $user_survey_data->getSingleInstrument("your_physical_activity");

  //GET ANSWERS FOR ALL USERS
  $all_answers                = $user_survey_data->getUserAnswers(NULL,$graph_fields,$instrument_event["event"]);

  //GATHER UP THIS USERS ANSWERS
  $health_behaviors_complete  = $instrument_event["survey_complete"] ?: false;
  $user_answers               = array_intersect_key( $all_completed,  array_flip($graph_fields) );

  // AGGREGATE OF ALL PARTICIPANTS
  $ALL_TIME_PA_MOD_IN_HOURS   = array();
  $ALL_TIME_PA_VIG_IN_HOURS   = array();
  $ALL_TIME_WALKING_IN_HOURS  = array();
  $ALL_TIME_SITTING_IN_HOURS  = array();
  $ALL_TIME_SLEEP_HOURS       = array();
  $sitting_count              = 0;

  foreach($all_answers as $users_answers){
    $u_ans = array_intersect_key( $users_answers,  array_flip($graph_fields) );
    foreach($u_ans as $fieldname => $hhmm){
      if(!empty($hhmm)){
        if(strpos($fieldname,"hh") > -1){
          $answer_value = (int) $hhmm;
        }else if(strpos($fieldname,"mm") > -1){
          $answer_value = (float) $hhmm/60;
        }

        if(strpos($fieldname,"core_pa_mod") > -1){
          $ALL_TIME_PA_MOD_IN_HOURS[]  = $answer_value;
        }
        
        if(strpos($fieldname,"core_pa_vig") > -1){
          $ALL_TIME_PA_VIG_IN_HOURS[]  = $answer_value;
        }

        if(strpos($fieldname,"walking") > -1){
          $ALL_TIME_WALKING_IN_HOURS[] = $answer_value;
        }
        
        if(strpos($fieldname,"sitting") > -1){
          $answer_value = strpos($fieldname,"nowrk") > -1 ? $answer_value : $answer_value/2;
          $ALL_TIME_SITTING_IN_HOURS[] = $answer_value;

          if(strpos($fieldname,"nowrk") > -1){
            $sitting_count = $sitting_count  + 1;
          }else{
            $sitting_count = $sitting_count  +  .5;
          }
        }

        if(strpos($fieldname,"sleep") > -1){
          if($answer_value <= 0){
            continue;
          }
          $ALL_TIME_SLEEP_HOURS[] = $answer_value;
        }
      }
    }
  }

  if($health_behaviors_complete){
    $ALL_TIME_PA_MOD_IN_HOURS   = count($ALL_TIME_PA_MOD_IN_HOURS ) ? round(array_sum($ALL_TIME_PA_MOD_IN_HOURS )/count($ALL_TIME_PA_MOD_IN_HOURS ),2) : 0;
    $ALL_TIME_PA_VIG_IN_HOURS   = count($ALL_TIME_PA_VIG_IN_HOURS ) ? round(array_sum($ALL_TIME_PA_VIG_IN_HOURS )/count($ALL_TIME_PA_VIG_IN_HOURS ),2) : 0;
    $ALL_TIME_WALKING_IN_HOURS  = count($ALL_TIME_WALKING_IN_HOURS) ? round(array_sum($ALL_TIME_WALKING_IN_HOURS)/count($ALL_TIME_WALKING_IN_HOURS),2) : 0;
    $ALL_TIME_SITTING_IN_HOURS  = count($ALL_TIME_SITTING_IN_HOURS) ? round(array_sum($ALL_TIME_SITTING_IN_HOURS)/$sitting_count,2) : 0;
    $ALL_TIME_SLEEP_HOURS       = count($ALL_TIME_SLEEP_HOURS)      ? round(array_sum($ALL_TIME_SLEEP_HOURS)/count($ALL_TIME_SLEEP_HOURS),2) : 0;
    $ALL_NO_ACTIVITY            = ($ALL_TIME_SLEEP_HOURS - $ALL_TIME_SITTING_IN_HOURS - $ALL_TIME_WALKING_IN_HOURS - $ALL_TIME_PA_MOD_IN_HOURS - $ALL_TIME_PA_VIG_IN_HOURS == 0) ? 0 : 24 - $ALL_TIME_SLEEP_HOURS - $ALL_TIME_SITTING_IN_HOURS - $ALL_TIME_WALKING_IN_HOURS - $ALL_TIME_PA_MOD_IN_HOURS - $ALL_TIME_PA_VIG_IN_HOURS;
    $ALL_NO_ACTIVITY            = $ALL_NO_ACTIVITY < 0 ? 0 : $ALL_NO_ACTIVITY  ;
  }else{
    $ALL_TIME_PA_MOD_IN_HOURS   = 0;
    $ALL_TIME_PA_VIG_IN_HOURS   = 0;
    $ALL_TIME_WALKING_IN_HOURS  = 0;
    $ALL_TIME_SITTING_IN_HOURS  = 0;
    $ALL_TIME_SLEEP_HOURS       = 0;
    $ALL_NO_ACTIVITY            = 0;
    $ALL_NO_ACTIVITY            = 0;
  }
  
  //CURRENT USERS VALUES
  $USER_TIME_PA_MOD_IN_HOURS  = 0;
  $USER_TIME_PA_VIG_IN_HOURS  = 0;
  $USER_TIME_WALKING_IN_HOURS = 0;
  $USER_TIME_SITTING_IN_HOURS = 0;
  $USER_TIME_SLEEP_HOURS      = 0;
  foreach($user_answers as $fieldname => $hhmm){
    if(!empty($hhmm)){
      if(strpos($fieldname,"hh") > -1){
        $answer_value = (int) $hhmm;
      }else if(strpos($fieldname,"mm") > -1){
        $answer_value = (float) $hhmm/60;
      }

      if(strpos($fieldname,"core_pa_mod") > -1){
        $USER_TIME_PA_MOD_IN_HOURS += $answer_value;
      }
      
      if(strpos($fieldname,"core_pa_vig") > -1){
        $USER_TIME_PA_VIG_IN_HOURS += $answer_value;
      }

      if(strpos($fieldname,"walking") > -1){
        $USER_TIME_WALKING_IN_HOURS += $answer_value;
      }
      
      if(strpos($fieldname,"sitting") > -1){
        $answer_value = strpos($fieldname,"nowrk") > -1 ? $answer_value : $answer_value/2;
        $USER_TIME_SITTING_IN_HOURS += $answer_value;
      }

      if(strpos($fieldname,"sleep") > -1){
        $USER_TIME_SLEEP_HOURS += $answer_value;
      }
    }
  }
  $USER_NO_ACTIVITY  = ($USER_TIME_SLEEP_HOURS - $USER_TIME_SITTING_IN_HOURS -$USER_TIME_WALKING_IN_HOURS - $USER_TIME_PA_MOD_IN_HOURS - $USER_TIME_PA_VIG_IN_HOURS == 0) ? 0 : 24 - $USER_TIME_SLEEP_HOURS - $USER_TIME_SITTING_IN_HOURS -$USER_TIME_WALKING_IN_HOURS - $USER_TIME_PA_MOD_IN_HOURS - $USER_TIME_PA_VIG_IN_HOURS;
  $USER_NO_ACTIVITY  = $USER_NO_ACTIVITY < 0 ? 0 : $USER_NO_ACTIVITY;
}else{
  //GATHER DATA FOR USERS SHORT SCORES
  $short_scores   = array();
  if($core_surveys_complete){
    $extra_params = array(
      'content'     => 'record',
      'records'     => array($loggedInUser->id) ,
      'fields'      => array("id","well_score"),
    );
    $user_ws      = RC::callApi($extra_params, true, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN); 
    $user_ws      = array_filter($user_ws,function($item){
      return !empty($item["well_score"]);
    });

    // ONLY WANT TO SHOW IT IF AT LEAST THE 1st anniversary WAS COMPLETED
    $min_well_score_show    = false;
    if( count($user_ws) > 1){
      $min_well_score_show  = true;
    }

    //GET ALL EVENT ARMS
    $extra_params   = array(
      'content'     => 'event',
      'format'      => 'json'
    );
    $result         = RC::callApi($extra_params, true, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
    $events         = array_column($result, 'unique_event_name');

    // GET ALL STORED WELLSCORES FOR EVERYONE, 
    // TODO : maybe some other day
    // $others_scores  = array();
    // foreach($events as $eventarm){
    //     $all_well_scores = $user_survey_data->getUserAnswers(NULL,array("well_score"),$eventarm, "[well_score] <> ''"); // , [id] <> '".$loggedInUser->id."'
    //     if(!empty($all_well_scores[0]["well_score"])){
    //       $others_scores[$eventarm] = array("well_score" => getAvgWellScoreOthers($all_well_scores) );
    //     }
    // };

    //CALCULATE WELL_SCORE FOR CURRENT USER IF NOT ALREADY STORED
    if(!$min_well_score_show){
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

      $arms_answers     = array();
      $long_survey_data = false;
      foreach($events as $eventarm){
        if($eventarm == "enrollment_arm_1"){
          $long_survey_data = new Project($loggedInUser, SESSION_NAME, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
          $user_answers     = $long_survey_data->getUserAnswers($loggedInUser->id,$short_q_fields,$eventarm);
        }elseif(strpos($eventarm,"short") > -1){
          //SHORT YEAR , CAUSE WE ALREADY DID it in surveys.php
          $user_answers   = $user_survey_data->getUserAnswers($loggedInUser->id,$short_q_fields,$eventarm);
        }

        if(!isset($user_answers[0])){
          continue;
        }

        $user_completed_keys        = array_filter(array_intersect_key( $user_answers[0],  array_flip($short_q_fields)),function($v){
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });
        $missing_data_keys          = array_diff_key($short_circuit_diff_ar,$user_completed_keys);
        $minimumData                = checkMinimumForShortScore($missing_data_keys);
        
        //ENOUGH DATA TO CALC SCORE
        $arms_answers[$eventarm]    = $minimumData ? $user_completed_keys : array();

        //THESE EVENTS ARE IN CHRONOLOGICAL ORDER LONGITUDINAL, 
        //SO NO NEED TO DO ANYMORE IF THE user_event_arm IS SAME AS THE EVENT ARM
        if($user_event_arm  == $eventarm){
          break;
        }
      };

      $short_scores = getShortScores($arms_answers);
      foreach($short_scores as $arm => $parts){
        $score  = round(array_sum($parts));
        $data[] = array(
          "record"            => $loggedInUser->id,
          "field_name"        => "well_score",
          "value"             => $score,
          "redcap_event_name" => $arm
        );
        $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN);
      }
    }else{
      foreach($user_ws as $idx => $well_score){
        $short_scores[$well_score["redcap_event_name"]] = array("junk" => $well_score["well_score"]);
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
      require_once('../PDF/fpdf181/fpdf.php');
      require_once('../PDF/FPDI-2.0.1/src/autoload.php');
      include_once("../PDF/generatePDFcertificate.php");
    
      $arm_year       = substr($loggedInUser->consent_ts,0,strpos($loggedInUser->consent_ts,"-"));
      $arm_year       = $arm_year + count($short_scores) - 1;
      $for_popup      = array_slice($short_scores, -1);

      $new_well_score = round((array_sum($for_popup[$user_event_arm])/50)*100);
      $success_msg    = $lang["CONGRATS_FRUITS"] . "<p>Your WELL Score for $arm_year is $new_well_score</p><a target='blank' href='$filename'>[Click here to download your certificate!]</a>";
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
