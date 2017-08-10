<?php
require_once("../models/config.php");

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) { 
  $destination = $websiteUrl . "login.php";
  header("Location: " . $destination);
  exit; 
}elseif(!isUserActive()) { 
  $destination = $websiteUrl . "consent.php";
  header("Location: " . $destination);
  exit; 
}else{
  if(empty($loggedInUser->user_bucket)){
    //USER NOT YET IN BUCKET, ASSIGN TO BUCKET "RANDOMLY"
    $user_bucket = time() % 2 == 0 ? "A" : "B"; //THIS IS ASININE, BUT OK
    $data[] = array(
      "record"            => $loggedInUser->id,
      "field_name"        => 'portal_user_bucket',
      "value"             => $user_bucket
    );
    $projects     = SurveysConfig::$projects;
    $API_TOKEN    = $projects["REDCAP_PORTAL"]["TOKEN"];
    $API_URL      = $projects["REDCAP_PORTAL"]["URL"];
    $result       = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
    $_SESSION[SESSION_NAME]['user']->user_bucket = $user_bucket;
  }else{
    $user_bucket  = $loggedInUser->user_bucket;
  }
  $variant = "A"; //THIS WILL DETERMINE THE BUCKETS I GUESS

  //if they are logged in and active
  //find survey completion and go there?
  // GET SURVEY LINKS
  include("../models/inc/surveys.php");
}

//NEEDS TO GO BELOW SUPPLEMENTALL PROJECTS WORK FOR NOW
if(isset($_GET["survey_complete"])){
  //IF NO URL PASSED IN THEN REDIRECT BACK
  $surveyid = $_GET["survey_complete"];
  
  if(array_key_exists($surveyid,$surveys)){
    $index  = array_search($surveyid, $all_survey_keys);
    $survey = $surveys[$surveyid];
    $success_msg  = $lang["YOUVE_BEEN_AWARDED"] . " : <span class='fruit " . $fruits[$index] . "'></span> " ;
      
      if(isset($all_survey_keys[$index+1])){
        $nextlink     = "survey.php?sid=". $all_survey_keys[$index+1];
        $success_msg .= $lang["GET_WHOLE_BASKET"]."<br> <a class='takenext' href='$nextlink'>".$lang["CONTINUE_SURVEY"]."</a>";
      }else{
        $success_msg .= $lang["CONGRATS_FRUITS"] . " <iframe width='100%' height='315' src='https://www.youtube.com/embed/NBDj5WJpSLM' frameborder='0' allowfullscreen></iframe>";
      }
      addSessionMessage( $success_msg , "success");
  }

  if(array_key_exists($surveyid,$supp_surveys)){
    $index  = array_search($surveyid, $supp_surveys_keys);
    $survey = $supp_surveys[$surveyid];
    $success_msg  = $lang["FITNESS_BADGE"]. ": <span class='fitness " . SurveysConfig::$fitness[$index] . "'></span>" ;
      
      if(isset($all_survey_keys[$index+1])){
        $success_msg .= $lang["GET_ALL_BADGES"]. "<br> ";
      }else{
        $success_msg .= $lang["CONGRATS_ALL_FITNESS_BADGES"];
      }
      
      addSessionMessage( $success_msg , "success");
  }
}

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

if(isset($_GET["irvin"])){
  foreach($all_answers as $users_answers){
    $u_ans = array_intersect_key( $users_answers,  array_flip($graph_fields) );
    foreach($u_ans as $fieldname => $hhmm){
      if(!empty($hhmm)){
        if(strpos($fieldname,"hh") > -1){
          $answer_value = (int) $hhmm;
        }else if(strpos($fieldname,"mm") > -1){
          $answer_value = (float) $hhmm/60;
        }

        if($answer_value <= 0 && strpos($fieldname,"sleep") > -1){
          continue;
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
          $ALL_TIME_SLEEP_HOURS[] = $answer_value;
        }
      }
    }
  }
}

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

$ALL_TIME_PA_MOD_IN_HOURS   = count($ALL_TIME_PA_MOD_IN_HOURS ) ? round(array_sum($ALL_TIME_PA_MOD_IN_HOURS )/count($ALL_TIME_PA_MOD_IN_HOURS ),2) : 0;
$ALL_TIME_PA_VIG_IN_HOURS   = count($ALL_TIME_PA_VIG_IN_HOURS ) ? round(array_sum($ALL_TIME_PA_VIG_IN_HOURS )/count($ALL_TIME_PA_VIG_IN_HOURS ),2) : 0;
$ALL_TIME_WALKING_IN_HOURS  = count($ALL_TIME_WALKING_IN_HOURS) ? round(array_sum($ALL_TIME_WALKING_IN_HOURS)/count($ALL_TIME_WALKING_IN_HOURS),2) : 0;
$ALL_TIME_SITTING_IN_HOURS  = count($ALL_TIME_SITTING_IN_HOURS) ? round(array_sum($ALL_TIME_SITTING_IN_HOURS)/$sitting_count,2) : 0;
$ALL_TIME_SLEEP_HOURS       = count($ALL_TIME_SLEEP_HOURS)      ? round(array_sum($ALL_TIME_SLEEP_HOURS)/count($ALL_TIME_SLEEP_HOURS),2) : 0;
$ALL_NO_ACTIVITY            = ($ALL_TIME_SLEEP_HOURS - $ALL_TIME_SITTING_IN_HOURS - $ALL_TIME_WALKING_IN_HOURS - $ALL_TIME_PA_MOD_IN_HOURS - $ALL_TIME_PA_VIG_IN_HOURS == 0) ? 0 : 24 - $ALL_TIME_SLEEP_HOURS - $ALL_TIME_SITTING_IN_HOURS - $ALL_TIME_WALKING_IN_HOURS - $ALL_TIME_PA_MOD_IN_HOURS - $ALL_TIME_PA_VIG_IN_HOURS;
$ALL_NO_ACTIVITY            = $ALL_NO_ACTIVITY < 0 ? 0 : $ALL_NO_ACTIVITY  ;


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

$shownavsmore   = true;
$survey_active  = ' class="active"';
$profile_active = '';
$studies_active = '';
$game_active    = '';
$assesments     = '';
$pg_title 		  = $lang["DASHBOARD"]. " : $websiteName";
$body_classes 	= "dashboard";
include("inc/gl_head.php");


$data = array(
    'token' => '379870900189B71CF04F47F6DC260835',
    'content' => 'metadata',
    'format' => 'json',
    'returnFormat' => 'json',
    'fields' => array(),
    'forms' => array('a_little_bit_about_you','about_you','contact_information','wellbeing_questions','your_diet','your_feedback','your_health','your_physical_activity','your_sleep_habits','your_social_and_neighborhood_environment','your_tobacco_and_alcohol_use')
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://redcap.irvins.loc/api/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
$output = curl_exec($ch);
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
                    <div class="col-sm-3 col_ipad_port col_ipad_land">
                      <h3 class="m-b-xs text-black"><?php echo  $lang["DASHBOARD"] ?></h3>
                      <small><?php echo  $lang["WELCOME_BACK"] ?>, <span class="fullname"><?php echo $firstname . " " . $lastname; ?></span>, <i class="fa fa-map-marker fa-lg text-primary"></i> <?php echo ucfirst($city) ?></small>
                    </div>
                    <div class="col-sm-8 col_ipad_port col_ipad_land">
                      <?php
                      //THIS STUFF IS FOR NEWS AND REMINDERS FURTHER DOWN PAGE
                      $news         = array();
                      $reminders    = array();
                      if($core_surveys_complete){
                        $reminders[]  = "<li class='list-group-item'>".$lang["DONE_CORE"]."</li>";
                      }else{
                        // $news[]       = "<li class='list-group-item'>No news yet.</li>";
                      }

                      $proj_name    = "foodquestions";
                      $ffq_project  = new PreGenAccounts($loggedInUser
                        , $proj_name , SurveysConfig::$projects[$proj_name]["URL"]
                        , SurveysConfig::$projects[$proj_name]["TOKEN"]);
                      $ffq = $ffq_project->getAccount();
                      if(!array_key_exists("error",$ffq)){
                        $nutrilink      = isset($portal_test) ? "#" : "https://www.nutritionquest.com/login/index.php?username=".$ffq["ffq_username"]."&password=".$ffq["ffq_password"]."&BDDSgroup_id=747&Submit=Submit";
                        $a_nutrilink    = "<a href='$nutrilink' class='nutrilink' title='".$lang["TAKE_BLOCK_DIET"]."' target='_blank'>".$lang["HOW_WELL_EAT"]." &#128150 </a>";
                        if($_SESSION["use_lang"] !== "sp"){
                          $news[]         = "<li class='list-group-item icon_update'>".$a_nutrilink."</li>";
                        }
                      }

                      //FIGURE OUT WHERE TO PUT THIS "NEWS" STUFF
                      //THIS COMES FROM THE models/inc/surveys.php file
                      $survey_alinks        = array();
                      $supp_part_1_complete = true;
                      if($user_bucket == $variant){
                        foreach($supp_instruments as $supp_instrument_id => $supp_instrument){
                          if($supp_instrument_id == "how_physically_mobile_are_you" || $supp_instrument_id == "how_fit_are_you"){
                            if(!$supp_instrument["survey_complete"]){
                              $supp_part_1_complete = false;
                            }
                          }else{
                            continue;
                          }
                        }
                      }

                      foreach($supp_instruments as $supp_instrument_id => $supp_instrument){
                        if($supp_instrument["survey_complete"]){
                          continue;
                        }

                        if(!$supp_part_1_complete && (
                               $supp_instrument_id == "how_resilient_are_you_to_stress" 
                            || $supp_instrument_id == "how_well_do_you_sleep" 
                            || $supp_instrument_id == "find_out_your_body_type_according_to_chinese_medic" 
                          )){
                          continue;
                        }

                        //if bucket is A make sure that three other ones are complete before showing.
                        $projnotes    = json_decode($supp_instrument["project_notes"],1);
                        $title_trans  = $projnotes["translations"];
                        $tooltips     = $projnotes["tooltips"];
                        $surveyname   = isset($title_trans[$_SESSION["use_lang"]][$supp_instrument_id]) ?  $title_trans[$_SESSION["use_lang"]][$supp_instrument_id] : $supp_instrument["label"];
                        
                        $titletext    = $core_surveys_complete ? $tooltips[$supp_instrument_id] : $lang["COMPLETE_CORE_FIRST"];
                        $surveylink   = $core_surveys_complete ? "survey.php?sid=". $supp_instrument_id. "&project=" . $supp_instrument["project"] : "#";
                        $icon_update  = " icon_update";
                        $survey_alinks[$supp_instrument_id] = "<a href='$surveylink' title='$titletext'>$surveyname</a>";
                    
                        $news[]       = "<li class='list-group-item $icon_update'>
                                            ".$survey_alinks[$supp_instrument_id]." survey 
                                        </li>";
                      }
                      
                      $firstonly      = true;
                      $showfruit      = array();

                      echo "<ul class='dash_fruits'>\n";
                      $projnotes      = json_decode($survey["project_notes"],1);
                      $title_trans    = $projnotes["translations"];
                       
                      foreach($surveys as $surveyid => $survey){
                        $index          = array_search($surveyid, $all_survey_keys);
                        $surveylink     = "survey.php?sid=". $surveyid;
                        $surveyname     = isset($title_trans[$_SESSION["use_lang"]][$surveyid]) ?  $title_trans[$_SESSION["use_lang"]][$surveyid] : $survey["label"];
                        $surveycomplete = $survey["survey_complete"];
                        $completeclass  = ($surveycomplete ? "completed":"");

                        //NEWS AND REMINDERS JUNK
                        if(!$surveycomplete){
                          $crap = ($firstonly ? $surveylink : "#");
                          if(!$core_surveys_complete){
                            if(in_array($surveyid,SurveysConfig::$core_surveys)){
                              $reminders[]  = "<li class='list-group-item'>
                                  ".$lang["PLEASE_COMPLETE"]." <a href='$crap'>$surveyname</a>
                              </li>";
                            }
                          }
                          $firstonly = false;
                        }

                        $showfruit[] = "<li class='nav'>
                            <a href='$surveylink' class='fruit ".$fruits[$index]." $completeclass' title='$surveyname'>                                                        
                              <span>$surveyname</span>
                            </a>
                          </li>";
                      }

                      foreach($supp_instruments as $supp_instrument_id => $supp_instrument){
                        $index          = array_search($supp_instrument_id, $supp_surveys_keys);
                        $surveyname     = $supp_instrument["label"];
                        $surveylink     = "survey.php?sid=". $supp_instrument_id;
                        $completeclass  = ($supp_instrument["survey_complete"] ? "completed":"");
                        $showfruit[]    = "<li class='nav'>
                            <a href='$surveylink' class='fitness ".SurveysConfig::$fitness[$index]." $completeclass' title='$surveyname'>                                                        
                              <span>$surveyname</span>
                            </a>
                          </li>";
                      }
                      echo implode($showfruit);
                      echo "<ul>\n";

                      //UI FIX FOR NEWS AND REMINDERS IF NOT VERTICALLY EQUAL
                      $cnt_reminders  = count($reminders);
                      $cnt_news       = count($news);
                      $diff           = abs($cnt_reminders - $cnt_news);

                      for($i = 0; $i < $diff; $i++){
                        if($cnt_reminders > $cnt_news){
                          $news[]       = "<li class='list-group-item'>&nbsp;</li>";
                        }else{
                          $reminders[]  = "<li class='list-group-item'>&nbsp;</li>";
                        }
                      }
                      ?>
                    </div>
                  </section>

                  <div class="row">
                    <div class="col-sm-6 col_ipad_port col_ipad_land">
                        <div id="slide_banner">
                          <ul>
                          <?php
                            $welcome_back = !$first_survey["survey_complete"] ? $lang["WELCOME_TO_WELL"] : $lang["WELCOME_BACK_TO"];
                            if( !isset($next_survey) ){
                              $next_survey = $nutrilink;
                            } 

                            $slides = array(
                               "slide_welcome"  => "<a href='$next_survey'>$welcome_back</a>" 
                              ,"slide_ffq"      => $a_nutrilink                                     
                              ,"slide_mat"      => !isset($survey_alinks["how_physically_mobile_are_you"])   ? null : $survey_alinks["how_physically_mobile_are_you"] 
                              ,"slide_pa"       => !isset($survey_alinks["how_fit_are_you"])                 ? null : $survey_alinks["how_fit_are_you"]                
                              ,"slide_grit"     => !isset($survey_alinks["how_resilient_are_you_to_stress"]) ? null : $survey_alinks["how_resilient_are_you_to_stress"]
                              ,"slide_sleep"    => !isset($survey_alinks["how_well_do_you_sleep"])           ? null : $survey_alinks["how_well_do_you_sleep"]
                              ,"slide_tcm"      => !isset($survey_alinks["find_out_your_body_type_according_to_chinese_medic"])           ? null : $survey_alinks["find_out_your_body_type_according_to_chinese_medic"]          
                            );

                            foreach($slides as $slideid => $link){
                              if(is_null($link)){
                                continue;
                              }
                              echo "<li id='$slideid'>$link</li>\r";
                            }
                          ?>
                                                      
                          </ul>
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col_ipad_port col_ipad_land">
                      <div id="weather"></div>
                      <script>
                        // Docs at http://simpleweatherjs.com
                        $(document).ready(function() {
                          var weathercodes = [];
                          weathercodes[0] = "c";  //tornado
                          weathercodes[1] = "c";  //tropical storm
                          weathercodes[2] = "c";  //hurricane
                          weathercodes[3] = "c";  //severe thunderstorms
                          weathercodes[4] = "c";  //thunderstorms
                          weathercodes[5] = "c";  //mixed rain and snow
                          weathercodes[6] = "c";  //mixed rain and sleet
                          weathercodes[7] = "c";  //mixed snow and sleet
                          weathercodes[8] = "c";  //freezing drizzle
                          weathercodes[9] = "c";  //drizzle
                          weathercodes[10] = "c"; //freezing rain
                          weathercodes[11] = "c"; //showers
                          weathercodes[12] = "c"; //showers
                          weathercodes[13] = "c"; //snow flurries
                          weathercodes[14] = "c"; //light snow showers
                          weathercodes[15] = "c"; //blowing snow
                          weathercodes[16] = "c"; //snow
                          weathercodes[17] = "c"; //hail
                          weathercodes[18] = "c"; //sleet
                          weathercodes[19] = "c"; //dust
                          weathercodes[20] = "c"; //foggy
                          weathercodes[21] = "c"; //haze
                          weathercodes[22] = "c"; //smoky
                          weathercodes[23] = "c"; //blustery
                          weathercodes[24] = "c"; //windy
                          weathercodes[25] = "c"; //cold
                          weathercodes[26] = "c"; //cloudy
                          weathercodes[27] = "c"; //mostly cloudy (night)
                          weathercodes[28] = "c"; //mostly cloudy (day)
                          weathercodes[29] = "";  //partly cloudy (night)
                          weathercodes[30] = "";  //partly cloudy (day)
                          weathercodes[31] = "";  //clear (night)
                          weathercodes[32] = "";  //sunny
                          weathercodes[33] = "";  //fair (night)
                          weathercodes[34] = "";  //fair (day)
                          weathercodes[35] = "";  //mixed rain and hail
                          weathercodes[36] = "";  //hot
                          weathercodes[37] = "c"; //isolated thunderstorms
                          weathercodes[38] = "c"; //scattered thunderstorms
                          weathercodes[39] = "c"; //scattered thunderstorms
                          weathercodes[40] = "c"; //scattered showers
                          weathercodes[41] = "c"; //heavy snow
                          weathercodes[42] = "c"; //scattered snow showers
                          weathercodes[43] = "c"; //heavy snow
                          weathercodes[44] = "c"; //partly cloudy
                          weathercodes[45] = "c"; //thundershowers
                          weathercodes[46] = "c"; //snow showers
                          weathercodes[47] = "c"; //isolated thundershowers
                          
                          $.simpleWeather({
                            location: '<?php echo $location ?>,USA',
                            unit: 'F',
                            success: function(weather) {
                              var imgurl    = weather.image;
                              var temp      = imgurl.split("/");
                              temp          = temp.pop();
                              temp          = temp.replace(weather.code, "");
                              daynight      = temp.substring(0, temp.indexOf("."));
                              
                              var backdrop  = daynight + weathercodes[weather.todayCode];

                              html = '<ul class="'+ backdrop +'">';
                              html += '<li class="locale">'+weather.city+', '+weather.region;
                              html += '<b class="temps">'+weather.temp+'&deg;</b>';
                              html += '<b class="hilo">lo : '+weather.low+'&deg; &nbsp; hi : '+weather.high+'&deg;</b>';
                              html += '<b class="conditions">' + weather.currently + '</b>';
                              html += '</li>';
                              html += '<li class="weatherimg" style="background-image:url('+weather.image+')"></li>';
                              html += '<li>';
                              html += '<b class="wind"> <?php $lang["WIND"] ?>: '+weather.wind.direction+' '+weather.wind.speed+' '+weather.units.speed+'</b>';
                              html += '<b><?php $lang["SUNRISE"] ?> : ' + weather.sunrise + '</b>';
                              html += '<b><?php $lang["SUNSET"] ?> : ' + weather.sunset + '</b></li>';
                              html += '</ul>';
                              $("#weather").html(html);
                            },
                            error: function(error) {
                              $("#weather").html('<p>'+error+'</p>');
                            }
                          });
                        });
                      </script>
                      <!-- <div class="weather"><?php echo $location ?></div> -->
                    </div>
                  </div>           
                  <div class="row dk m-b">
                    <div class="col-sm-6 col_ipad_port col_ipad_land">
                        <div class="panel panel-info portlet-item">
                          <header class="panel-heading">
                            <i class="fa fa-list-ul"></i> <?php echo $lang["REMINDERS"] ?>
                          </header>
                          <ul class="list-group alt">
                            <?php
                              echo implode("\n",$reminders);
                            ?>
                          </ul>
                        </div>
                    </div>
                    <div class="col-sm-6 col_ipad_port col_ipad_land">
                        <div class="panel panel-success portlet-item">
                          <header class="panel-heading">
                            <i class="glyphicon glyphicon-star-empty"></i> <?php echo $lang["ADDITIONAL_SURVEYS"] ?>
                          </header>
                          <ul class="list-group alt">
                            <?php
                              if($user_bucket == $variant){
                                //so not just every even one, but every other even number i guess
                                $re_add = array();
                                foreach($news as $k => $item){
                                  if(strpos($item,"&nbsp;") > -1){
                                    //purposely empty rows (for layout) need to be removed and re-added after shuffling
                                    $re_add[] = $item;
                                    unset($news[$k]);
                                  }
                                }
                                shuffle($news);
                                $news = array_merge($news,$re_add);
                              }
                              echo implode("\n",$news);
                            ?>
                          </ul>
                        </div>
                    </div>
  
                    <div class="col-md-6 bg-light dker datacharts chartone col_ipad_port col_ipad_land">
                      <section>
                        <?php 
                          if ($health_behaviors_complete) { 
                            echo '<div id="pieChart"></div>';
                          }else{
                            echo "<h6>".$lang["SEE_PA_DATA"]."</h4>";
                          }
                        ?>
                      </section>
                    </div>
                    <div class="col-md-6 dker datacharts charttoo col_ipad_port col_ipad_land">
                      <section>
                        <h3><?php echo $lang["HOW_DO_YOU_COMPARE"] ?></h3>
                        <p></p>
                        <canvas id="youvsall" ></canvas>
                      </section>
                    </div>
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
<script type="text/javascript">
$(document).ready(function () {
  $(".weather").weatherFeed({relativeTimeZone:true});

  $(".btn-success").click(function(){
    if($(".takenext").length > 0){
      location.href= $(".takenext").prop("href");
      return;
    }
  });
});
</script>
<script src="js/Chart.js"></script>
<script>
var ctx = $("#youvsall");
var myBarChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [ "<?php echo $lang["SITTING"] ?>"
                , "<?php echo $lang["WALKING"] ?>"
                , "<?php echo $lang["MODACT"] ?>"
                , "<?php echo $lang["VIGACT"] ?>"
                , "<?php echo $lang["NOACT"] ?>"
                , "<?php echo $lang["SLEEP"] ?>"],
        datasets: [{
            label: '<?php echo $lang["YOU_HOURS_DAY"] ?>',
            data: [
               <?php echo $USER_TIME_SITTING_IN_HOURS ?>
              ,<?php echo $USER_TIME_WALKING_IN_HOURS ?>
              ,<?php echo $USER_TIME_PA_MOD_IN_HOURS  ?>
              ,<?php echo $USER_TIME_PA_VIG_IN_HOURS ?>
              ,<?php echo $USER_NO_ACTIVITY ?>
              ,<?php echo $USER_TIME_SLEEP_HOURS ?>
            ],
            backgroundColor: "rgba(78, 163, 42, .9)",
            hoverBackgroundColor: "rgba(78, 163, 42, 1)",
          },{
            label: '<?php echo $lang["AVG_ALL_USERS"] ?>',
            data: [
               <?php echo $ALL_TIME_SITTING_IN_HOURS ?>
              ,<?php echo $ALL_TIME_WALKING_IN_HOURS ?>
              ,<?php echo $ALL_TIME_PA_MOD_IN_HOURS ?>
              ,<?php echo $ALL_TIME_PA_VIG_IN_HOURS ?>
              ,<?php echo $ALL_NO_ACTIVITY ?>
              ,<?php echo $ALL_TIME_SLEEP_HOURS ?>
            ],
            backgroundColor: "rgba(246, 210, 0, .9)",
            hoverBackgroundColor: "rgba(246, 210, 0, 1)",
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
</script>

<script src="js/d3.min.js"></script>
<script src="js/d3pie.min.js"></script>
<script>
//THIS IS FOR THE SLIDER BANNER ON THE INDEX PAGE
$("#slide_banner li").first().addClass("on");
setInterval(function(){
  var nextslide = $("#slide_banner li.on").next().length ? $("#slide_banner li.on").next() : $("#slide_banner li").first();
  $("#slide_banner li.on").addClass("off", function(){
    var _this = $(this);
    setTimeout(function(){
      _this.removeClass("on").removeClass("off");
    },500);
    nextslide.addClass("on");
  });
},8000);



var pieData = [
      {
        "label": "<?php echo $lang["NOACT"] ?>",
        "value": <?php echo $USER_NO_ACTIVITY ?>,
        "color": "#cccccc"
      },
      {
        "label": "<?php echo $lang["MODACT"] ?>",
        "value": <?php echo $USER_TIME_PA_MOD_IN_HOURS ?>,
        "color": "#009966"
      },
      {
        "label": "<?php echo $lang["VIGACT"] ?>",
        "value": <?php echo $USER_TIME_PA_VIG_IN_HOURS ?>,
        "color": "#006600"
      },
      {
        "label": "<?php echo $lang["WALKING"] ?>",
        "value": <?php echo $USER_TIME_WALKING_IN_HOURS ?>,
        "color": "#66CC33"
      },
      {
        "label": "<?php echo $lang["SITTING"] ?>",
        "value": <?php echo $USER_TIME_SITTING_IN_HOURS ?>,
        "color": "#ff3300"
      },
      {
        "label": "<?php echo $lang["SLEEP"] ?>",
        "value": <?php echo $USER_TIME_SLEEP_HOURS ?>,
        "color": "#C8A0D8"
      },
    ];

var pie = new d3pie("pieChart", {
  "header": {
    "title": {
      "text": "<?php echo $lang["HOW_YOU_SPEND_TIME"] ?>",
      "fontSize": 24,
      "font": "open sans"
    },
    "subtitle": {
      "text": "",
      "color": "#333",
      "fontSize": 14,
      "font": "open sans"
    }
  },
  "size": {
    "canvasWidth": 600,
    "pieOuterRadius": "70%"
  },
  "data": {
    "sortOrder": "value-desc",
    "content": pieData
  },
  "labels": {
    "outer": {
      "pieDistance": 22
    },
    "inner": {
      "hideWhenLessThanPercentage": 3
    },
    "mainLabel": {
      "fontSize": 15
    },
    "percentage": {
      "color": "#ffffff",
      "decimalPlaces": 0,
      "fontSize": 15
    },
    "value": {
      "color": "#333",
      "fontSize": 10
    },
    "lines": {
      "enabled": true
    },
    "truncation": {
      "enabled": true
    }
  },
  "effects": {
    "pullOutSegmentOnClick": {
      "effect": "linear",
      "speed": 400,
      "size": 8
    }
  },
  "misc": {
    "gradient": {
      "enabled": true,
      "percentage": 80
    }
  }
});
</script>
