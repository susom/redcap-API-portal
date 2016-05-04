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
  //if they are logged in and active
  //find survey completion and go there?
  // GET SURVEY LINKS
  include("../models/inc/surveys.php");
}

if(isset($_GET["survey_complete"])){
  //IF NO URL PASSED IN THEN REDIRECT BACK
  $surveyid = $_GET["survey_complete"];
  
  if(array_key_exists($surveyid,$surveys)){
    $index  = array_search($surveyid, $all_survey_keys);
    $survey = $surveys[$surveyid];
    $success_msg  = "You've been awarded a : <span class='fruit " . $fruits[$index] . "'></span> " ;
      
      if(isset($all_survey_keys[$index+1])){
        $nextlink     = "survey.php?sid=". $all_survey_keys[$index+1];
        $success_msg .= "Get the whole fruit basket!<br> <a class='takenext' href='$nextlink'>Continue the rest of survey.</a>";
      }else{
        $success_msg .= "Congratulations, you got all the fruits! <br/> Check out some of the new modules under 'News'. ";
      }
      
      addSessionMessage( $success_msg , "success");
  }
}

//FOR THE PIE CHART
$graph_fields               = array(
                                 "core_sitting"
                                ,"core_sitting_nowrk"
                                ,"core_sitting_weekend"
                                ,"core_walking"
                                ,"core_pa_mod"
                                ,"core_pa_vig"
                                ,"core_sleep"
                              );
$instrument_event           = $user_survey_data->getSingleInstrument("your_physical_activity");

//GET ANSWERS FOR ALL USERS
$all_answers                = $user_survey_data->getUserAnswers(NULL,$graph_fields);

//GATHER UP THIS USERS ANSWERS
$health_behaviors_complete  = $instrument_event["survey_complete"] ?: false;
$user_answers               = array_intersect_key( $all_completed,  array_flip($graph_fields) );

// AGGREGATE OF ALL PARTICIPANTS
$ALL_TIME_PA_MOD_IN_HOURS   = array();
$ALL_TIME_PA_VIG_IN_HOURS   = array();
$ALL_TIME_WALKING_IN_HOURS  = array();
$ALL_TIME_SITTING_IN_HOURS  = array();
$ALL_TIME_SLEEP_HOURS       = array();

$sitting_count = 0;
foreach($all_answers as $users_answers){
  $u_ans = array_intersect_key( $users_answers,  array_flip($graph_fields) );
  foreach($u_ans as $fieldname => $hhmm){
    if(!empty($hhmm)){
      list($hour, $min) = explode(":",$hhmm);
      $hour_value   = (isset($hour) ? $hour : 0);
      $min_value    = (isset($min)  ? $min  : 0);
      $answer_value = ($min_value/60) + $hour_value;

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
$ALL_TIME_PA_MOD_IN_HOURS   = count($ALL_TIME_PA_MOD_IN_HOURS ) ? round(array_sum($ALL_TIME_PA_MOD_IN_HOURS )/count($ALL_TIME_PA_MOD_IN_HOURS ),2) : 0;
$ALL_TIME_PA_VIG_IN_HOURS   = count($ALL_TIME_PA_VIG_IN_HOURS ) ? round(array_sum($ALL_TIME_PA_VIG_IN_HOURS )/count($ALL_TIME_PA_VIG_IN_HOURS ),2) : 0;
$ALL_TIME_WALKING_IN_HOURS  = count($ALL_TIME_WALKING_IN_HOURS) ? round(array_sum($ALL_TIME_WALKING_IN_HOURS)/count($ALL_TIME_WALKING_IN_HOURS),2) : 0;
$ALL_TIME_SITTING_IN_HOURS  = count($ALL_TIME_SITTING_IN_HOURS) ? round(array_sum($ALL_TIME_SITTING_IN_HOURS)/$sitting_count,2) : 0;
$ALL_TIME_SLEEP_HOURS       = count($ALL_TIME_SLEEP_HOURS)      ? round(array_sum($ALL_TIME_SLEEP_HOURS)/count($ALL_TIME_SLEEP_HOURS),2) : 0;
$ALL_NO_ACTIVITY            = 24 - $ALL_TIME_SLEEP_HOURS - $ALL_TIME_SITTING_IN_HOURS - $ALL_TIME_WALKING_IN_HOURS - $ALL_TIME_PA_MOD_IN_HOURS - $ALL_TIME_PA_VIG_IN_HOURS;
$ALL_NO_ACTIVITY            = $ALL_NO_ACTIVITY < 0 ? 0 : $ALL_NO_ACTIVITY  ;

//CURRENT USERS VALUES
$USER_TIME_PA_MOD_IN_HOURS  = 0;
$USER_TIME_PA_VIG_IN_HOURS  = 0;
$USER_TIME_WALKING_IN_HOURS = 0;
$USER_TIME_SITTING_IN_HOURS = 0;
$USER_TIME_SLEEP_HOURS      = 0;
foreach($user_answers as $fieldname => $hhmm){
  if(!empty($hhmm)){
    list($hour, $min) = explode(":",$hhmm);
    $hour_value   = (isset($hour) ? $hour : 0);
    $min_value    = (isset($min)  ? $min  : 0);
    $answer_value = ($min_value/60) + $hour_value;

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
$USER_NO_ACTIVITY  = 24 - $USER_TIME_SLEEP_HOURS - $USER_TIME_SITTING_IN_HOURS -$USER_TIME_WALKING_IN_HOURS - $USER_TIME_PA_MOD_IN_HOURS - $USER_TIME_PA_VIG_IN_HOURS;
$USER_NO_ACTIVITY  = $USER_NO_ACTIVITY < 0 ? 0 : $USER_NO_ACTIVITY;
//SUPPLEMENTAL PROJECTS
$supp_surveys = array();
$supp_proj    = SurveysConfig::$projects;
foreach($supp_proj as $proj_name => $project){
  if($proj_name == $_CFG->SESSION_NAME){
    continue;
  }

  $supplementalProject  = new Project($loggedInUser, $proj_name, SurveysConfig::$projects[$proj_name]["URL"], SurveysConfig::$projects[$proj_name]["TOKEN"]);
  $supp_surveys         = array_merge($supp_surveys,$supplementalProject->getActiveAll());
}
// print_rr($supp_surveys,1);
// $_SESSION["Supp_Surveys"] = $supp_surveys;

$shownavsmore   = true;
$survey_active  = ' class="active"';
$profile_active = '';
$game_active    = '';
$pg_title 		  = "Dashboard : $websiteName";
$body_classes 	= "dashboard";
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
                    <div class="col-sm-3">
                      <h3 class="m-b-xs text-black">Dashboard</h3>
                      <small>Welcome back, <?php echo $firstname . " " . $lastname; ?>, <i class="fa fa-map-marker fa-lg text-primary"></i> <?php echo ucfirst($city) ?></small>
                    </div>
                    <div class="col-sm-8">
                      <?php
                      //THIS STUFF IS FOR NEWS AND REMINDERS FURTHER DOWN PAGE
                      $news         = array();
                      $reminders    = array();
                      if($core_surveys_complete){
                        $reminders[]  = "<li class='list-group-item'>All done with core surveys!</li>";
                      }else{
                        // $news[]       = "<li class='list-group-item'>No news yet.</li>";
                      }

                      //FIGURE OUT WHERE TO PUT THIS "NEWS" STUFF
                      foreach($supp_surveys as $supp_instrument_id => $supp_instrument){
                        $surveylink   = $core_surveys_complete ? "survey.php?sid=". $supp_instrument_id. "&project=" . $supp_instrument["project"] : "#";
                        $icon_update  = $core_surveys_complete ? " icon_update" : "";
                        $surveyname   = $supp_instrument["label"];

                        $projnotes    = json_decode($supp_instrument["project_notes"],1);
                        $tooltip      = $projnotes[$supp_instrument_id];

                        $titletext    = !$core_surveys_complete ? $tooltip : "You may come back to these surveys once you complete the Core Surveys!";
                        $news[]       = "<li class='list-group-item $icon_update'>
                                            Please take <a href='$surveylink' title='$titletext'>$surveyname</a> survey
                                        </li>";
                      }

                      $firstonly      = true;
                      $showfruit      = array();

                      echo "<ul class='dash_fruits'>\n";
                      foreach($surveys as $surveyid => $survey){
                        $index          = array_search($surveyid, $all_survey_keys);
                        $surveylink     = "survey.php?sid=". $surveyid;
                        $surveyname     = $survey["label"];
                        $surveycomplete = $survey["survey_complete"];
                        $completeclass  = ($surveycomplete ? "completed":"");

                        //NEWS AND REMINDERS JUNK
                        if(!$surveycomplete){
                          $crap = ($firstonly ? $surveylink : "#");
                          if($core_surveys_complete){
                            // $news[]       = "<li class='list-group-item'>
                            //     Please take <a href='$crap'>$surveyname</a> survey
                            // </li>";
                          }else{
                            if(in_array($surveyid,SurveysConfig::$core_surveys)){
                              $reminders[]  = "<li class='list-group-item'>
                                  Please complete <a href='$crap'>$surveyname</a> survey
                              </li>";
                            }
                          }
                          $firstonly = false;
                        }

                        $showfruit[] = "<li class='nav'>
                            <a rel='$surveylink' class='fruit ".$fruits[$index]." $completeclass' title='$surveyname'>                                                        
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
                    <div class="col-sm-6">
                        <div id="coming_soon">
                          coming soon...
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
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
                            location: '<?php echo $location ?>',
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
                              html += '<b class="wind"> wind: '+weather.wind.direction+' '+weather.wind.speed+' '+weather.units.speed+'</b>';
                              html += '<b>Sunrise : ' + weather.sunrise + '</b>';
                              html += '<b>Sunset  : ' + weather.sunset + '</b></li>';
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
                    <div class="col-sm-6">
                        <div class="panel panel-info portlet-item">
                          <header class="panel-heading">
                            <i class="fa fa-list-ul"></i> Reminders
                          </header>
                          <ul class="list-group alt">
                            <?php
                              echo implode("\n",$reminders);
                            ?>
                          </ul>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel panel-success portlet-item">
                          <header class="panel-heading">
                            <i class="glyphicon glyphicon-star-empty"></i> News
                          </header>
                          <ul class="list-group alt">
                            <?php
                              echo implode("\n",$news);
                            ?>
                          </ul>
                        </div>
                    </div>
  
                    <div class="col-md-6 bg-light dker datacharts chartone">
                      <section>
                        <?php 
                          if ($health_behaviors_complete) { 
                            echo '<div id="pieChart"></div>';
                          }else{
                            echo "<h6>Fill out the 'Your Physical Activity' part of the survey to see your data graphed here!</h4>";
                          }
                        ?>
                      </section>
                    </div>
                    <div class="col-md-6 dker datacharts charttoo">
                      <section>
                        <h3>How Do You Compare With Other Survey Takers?</h3>
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
        labels: ["Sitting", "Walking", "Moderate Activity", "Vigorous Activity", "Light/No Activity" , "Sleep"],
        datasets: [{
            label: 'You (Hours/Day)',
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
            label: 'Average All Users (Hours/Day)',
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
var pieData = [
      {
        "label": "Light/No Activity",
        "value": <?php echo $USER_NO_ACTIVITY ?>,
        "color": "#cccccc"
      },
      {
        "label": "Moderate Activity",
        "value": <?php echo $USER_TIME_PA_MOD_IN_HOURS ?>,
        "color": "#009966"
      },
      {
        "label": "Vigorous Activity",
        "value": <?php echo $USER_TIME_PA_VIG_IN_HOURS ?>,
        "color": "#006600"
      },
      {
        "label": "Walking",
        "value": <?php echo $USER_TIME_WALKING_IN_HOURS ?>,
        "color": "#66CC33"
      },
      {
        "label": "Sitting",
        "value": <?php echo $USER_TIME_SITTING_IN_HOURS ?>,
        "color": "#ff3300"
      },
      {
        "label": "Sleeping",
        "value": <?php echo $USER_TIME_SLEEP_HOURS ?>,
        "color": "#C8A0D8"
      },
    ];

var pie = new d3pie("pieChart", {
  "header": {
    "title": {
      "text": "How You Spend Your Time Each Day",
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
