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
        $success_msg .= "Get the whole fruit basket!<br> <a class='takenext' href='$nextlink'>Take the next '".$surveys[$all_survey_keys[$index+1]]["label"]."' survey now!</a>";
      }else{
        $success_msg .= "Congratulations, you got the whole fruit basket! <br/>See how your data compares to others in your area.";
      }
      
      addSessionMessage( $success_msg , "success");
  }
}

//FOR THE PIE CHART
$health_behaviors_complete  = false;
$all_answers                = array();
$graph_fields               = array("core_sitting", "core_sitting_weekend", "core_walking");
$instrument_event           = $user_survey_data->getSingleInstrument("your_physical_activity");
$user_answers               = array();
foreach($graph_fields as $key){
  if($instrument_event["survey_complete"]){
    $health_behaviors_complete = true;
    if(array_key_exists($key, $instrument_event["completed_fields"])){
      $user_answers[$key] = $instrument_event["completed_fields"][$key];
    }
  }
}

// AGGREGATE OF ALL PARTICIPANTS
$ALL_TIME_WALKING_IN_MINUTES = 0;
$ALL_TIME_SITTING_IN_MINUTES = 0;

foreach($all_answers as $answers){
  foreach($answers as $fieldname => $answer){
    // $answer_value = intval($answer);
    if(empty($answer)){
      continue;
    }

    if(strpos($fieldname,"sitting") > -1 || strpos($fieldname,"core_walking") > -1){
      list($hour, $min) = explode(":",$answer);
      $hour_value   = (isset($hour) ? $hour : 0);
      $min_value    = (isset($min)  ? $min : 0);

      $answer_value = $min_value + $hour_value*60;
      $answer_value = $answer_value*60;
    }

    if(strpos($fieldname,"walking") > -1){
      $ALL_TIME_WALKING_IN_MINUTES += $answer_value;
    }
    if(strpos($fieldname,"sitting") > -1){
      $ALL_TIME_SITTING_IN_MINUTES += $answer_value;
    }
  }
}

//CURRENT USERS VALUES
$USER_TIME_WALKING_IN_MINUTES = 0;
$USER_TIME_SITTING_IN_MINUTES = 0;
if(isset($user_answers) && !empty($user_answers)){
  if(empty($answer)){
      continue;
  }
  
  foreach($user_answers as $index => $answer){
    // $answer_value = intval($answer);
    if(strpos($index,"hr_min") > -1){
      list($hour, $min) = explode(":",$answer);
      $hour_value   = (isset($hour) ? $hour : 0);
      $min_value    = (isset($min)  ? $min : 0);

      $answer_value = $min_value + $hour_value*60;
    }

    if(strpos($index,"walking") > -1){
      $USER_TIME_WALKING_IN_MINUTES += $answer_value;
    }
    if(strpos($index,"sitting") > -1){
      $USER_TIME_SITTING_IN_MINUTES += $answer_value;
    }
  }
}

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
                      $supplementalProject  = new Project($loggedInUser, 'https://redcap.stanford.edu/api/', '0CE775FBC63981B552D74EFFA5E6741D');
                      $supp_surveys         = $supplementalProject->getActiveAll();
                      $supp_survey_keys     = array_keys($supp_surveys);

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
                        $survey_link  = $supp_instrument["survey_link"];
                        $surveyname   = $supp_instrument["label"];
                        $news[]       = "<li class='list-group-item'>
                                            Please take <a href='$survey_link'>$surveyname</a> survey
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

                    <div class="col-md-6 bg-light dker chartone">
                      <section>
                        <header class="font-bold padder-v">
                          <div class="btn-group pull-right">
                          </div>
                          You Spent: 
                        </header>
                        <div class="panel-body flot-legend" style="min-height:270px">
                          <?php
                          $user_completed = count(array_filter($user_answers, function($item){
                            return !empty($item["user_answer"]);
                          }));

                          if(!$health_behaviors_complete){
                            echo  "Please complete the Surveys!";
                          }else{
                          ?>
                            <div id="piechart" style="height:240px"></div>
                          <?php
                          }
                          ?>
                        </div>
                      </section>

                    </div>
                    <div class="col-md-6 dker charttoo">
                      <section>
                        <header class="font-bold padder-v">
                          <div class="btn-group pull-right">
                          </div>
                          You vs All Participants: 
                        </header>
                        <div class="panel-body flot-legend">
                          <div id="colchart_all" style="height:240px"></div>
                        </div>
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
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
  // LOAD CHART PACKAGES FROM GOOGLE
  google.load("visualization", "1", {packages:["corechart", "bar"]});

  //SINGLE USER BAR CHART
  google.setOnLoadCallback(drawWalkingSittingChart);
  function drawWalkingSittingChart() {
    //https://google-developers.appspot.com/chart/interactive/docs/gallery/piechart
    var data = google.visualization.arrayToDataTable([
      ['Task',   'Minutes per Day'],
      ['Sitting', <?php echo $USER_TIME_SITTING_IN_MINUTES ?>],
      ['Walking', <?php echo $USER_TIME_WALKING_IN_MINUTES ?>],
    ]);

    var options = {
      is3d : true,
      pieStartAngle :-180,
      backgroundColor : '#E0E6F0',
      colors : ['#297B9F','#F8B300'],
      chartArea:{left:20,top:10,width:'90%',height:'90%'}
    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart'));

    chart.draw(data, options);
  }

  //VERTICAL COLUMN CHART COMPARE USER TO ALL OTHERS
  google.setOnLoadCallback(drawUserVsAllChart);
  function drawUserVsAllChart() {
      var data = google.visualization.arrayToDataTable([
          ['', 'Sitting','Walking'],
          ['You', <?php echo $USER_TIME_WALKING_IN_MINUTES ?>, <?php echo $USER_TIME_SITTING_IN_MINUTES ?>],
          ['All', <?php echo $ALL_TIME_WALKING_IN_MINUTES/count($all_answers) ?>, <?php echo $ALL_TIME_SITTING_IN_MINUTES/count($all_answers) ?>],
        ]);

        var options = {
          backgroundColor : '#E0E6F0',
          colors          : ['#297B9F','#F8B300'],
          chart: {
            // title: 'You VS All Participants',  
            // titleTextStyle: {color: '#FF0000'}  
          },
          vAxis:
            {
              title:'Losses',
              textStyle: {color: 'red'} // Axis 1
            }
         
        };

        var chart = new google.charts.Bar(document.getElementById('colchart_all'));

        chart.draw(data, options);
    }
</script>
