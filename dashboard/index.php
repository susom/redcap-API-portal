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
  foreach($surveys as $index => $instrument_event){
    if($instrument_event["instrument_name"] != $surveyid){
      continue;
    }

    if($instrument_event["completed_fields"] >= $instrument_event["total_questions"]){
      $success_msg  = "Thanks! You've completed the survey: <strong class='surveyname'>'" . $instrument_event["instrument_label"] . "'.</strong> You've been awarded a : <span class='fruit " . $fruits[$index] . "'></span> " ;
      if(isset($surveys[$index+1])){
        $nextlink     = "survey.php?url=". urlencode($surveys[$index+1]["survey_link"]);
        $success_msg .= "Get the whole fruit basket!<br> <a class='takenext' href='$nextlink'>Take the next '".$surveys[$index+1]["instrument_label"]."' survey now!</a>";
      }else{
        $success_msg .= "Congratulations, you got the whole fruit basket! <br/>See how your data compares to others in your area.";
      }
      
      addSessionMessage( $success_msg , "success");
    }
  }
}

if(isset($_GET["survey_pause"])){
  //IF NO URL PASSED IN THEN REDIRECT BACK
  $surveyid = $_GET["survey_pause"];
  foreach($surveys as $index => $instrument_event){
    if($instrument_event["instrument_name"] != $surveyid){
      continue;
    }
    addSessionMessage( "Come back later to complete the '" . $instrument_event["instrument_label"] . "' Survey.<br> And collect your reward : <span class='fruit " . $fruits[$index] . "'></span>  Get the whole fruit basket!" , "notice");
  }
}

//FOR THE PIE CHART
$graph_fields = array("walking_time_hours", "walking_time_minutes", "sitting_time_hours", "sitting_time_minutes");
foreach($surveys as $index => $instrument_event){
  if($instrument_event["instrument_name"] !== "your_health_behaviors"){
    continue;
  }
  $all_answers  = getUserAnswers(null,$graph_fields);
  $user_answers = array_filter($instrument_event["meta_data"],function($item) use ($graph_fields) {
    return in_array($item["fieldname"],$graph_fields);
  });
}

// AGGREGATE OF ALL PARTICIPANTS
$ALL_TIME_WALKING_IN_MINUTES = 0;
$ALL_TIME_SITTING_IN_MINUTES = 0;
foreach($all_answers as $answers){
  foreach($answers as $fieldname => $answer){
    $answer_value = intval($answer);
    if(strpos($fieldname,"hours") > -1){
      $answer_value = $answer_value*60;
    }

    if(strpos($fieldname,"walking") > -1){
      $ALL_TIME_WALKING_IN_MINUTES += $answer_value;
    }else{
      $ALL_TIME_SITTING_IN_MINUTES += $answer_value;
    }
  }
}


//CURRENT USERS VALUES
$USER_TIME_WALKING_IN_MINUTES = 0;
$USER_TIME_SITTING_IN_MINUTES = 0;
if(isset($user_answers) && !empty($user_answers)){
  foreach($user_answers as $index => $answer){
    $answer_value = intval($answer["user_answer"]);
    if(strpos($answer["fieldname"],"hours") > -1){
      $answer_value = $answer_value*60;
    }

    if(strpos($answer["fieldname"],"walking") > -1){
      $USER_TIME_WALKING_IN_MINUTES += $answer_value;
    }else{
      $USER_TIME_SITTING_IN_MINUTES += $answer_value;
    }
  }
}

$shownavsmore   = true;
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
                      echo "<ul class='dash_fruits'>\n";
                      foreach($surveys as $index => $survey){
                        $surveylink     = "survey.php?url=". urlencode($survey["survey_link"]);
                        $surveyname     = $survey["instrument_label"];
                        $surveytotal    = $survey["total_questions"];
                        $surveycomplete = $survey["completed_fields"];
                        $completeclass  = ($surveycomplete >= $surveytotal ? "completed":"");

                        $percent_complete = round(($surveycomplete/$surveytotal)*100,2);
                        print_r("<li class='nav'>
                            <a rel='$surveylink' class='fruit ".$fruits[$index]." $completeclass' title='$surveyname : $percent_complete% Complete'>                                                        
                              <span>$surveyname</span>
                            </a>
                          </li>\n");
                      }
                      echo "<ul>\n";
                      ?>
                    </div>
                  </section>

                  <div class="row">
                    <div class="col-sm-6">
                      <div class="panel b-a corefour">
                        <div class="row m-n">
                          <div class="col-md-6 b-b b-r">
                            <a href="#" class="block padder-v hover">
                              <span class="clear">
                                <span class="h3 block m-t-xs text-danger">Core</span>
                              </span>
                            </a>
                          </div>
                          <div class="col-md-6 b-b disabled">
                            <a href="#" class="block padder-v hover ">
                              <span class="clear">
                                <span class="h3 block m-t-xs text-success">Tobacco Use</span>
                              </span>
                            </a>
                          </div>
                          <div class="col-md-6 b-b b-r disabled">
                            <a href="#" class="block padder-v hover ">
                              <span class="clear">
                                <span class="h3 block m-t-xs text-info">Diet</span>
                              </span>
                            </a>
                          </div>
                          <div class="col-md-6 b-b disabled">
                            <a href="#" class="block padder-v hover">
                              <span class="clear">
                                <span class="h3 block m-t-xs text-primary">Physical Activity</span>
                              </span>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-sm-6">
                    <?php
                      // <a href="https://www.accuweather.com/en/us/new-york-ny/10007/weather-forecast/349727" class="aw-widget-legal"></a>
                      // <div id="awcc1450204337398" class="aw-widget-current"  data-locationkey="" data-unit="f" data-language="en-us" data-useip="true" data-uid="awcc1450204337398"></div>
                      // <script type="text/javascript" src="http://oap.accuweather.com/launch.js"></script>
                    ?>
                      <div class="weather"><?php echo $location ?></div>
                    </div>
                  </div>           
                  <div class="row bg-light dk m-b">
                    <div class="col-md-6 dker chartone">
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

                          if(!$user_completed){
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
                          Average of All Participants Spent: 
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
      ['Walking', <?php echo $USER_TIME_WALKING_IN_MINUTES ?>],
      ['Sitting', <?php echo $USER_TIME_SITTING_IN_MINUTES ?>],
    ]);

    var options = {
      is3d : 'true',
      pieStartAngle :45,
      backgroundColor : '#E0E6F0',
      colors : ['#F8B300', '#297B9F'],
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
          colors : ['#297B9F','#F8B300'],
          chart: {
            title: 'Time Walking vs Sitting',
            subtitle: 'You VS All Participants',
          }
        };

        var chart = new google.charts.Bar(document.getElementById('colchart_all'));

        chart.draw(data, options);
    }
</script>
