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
      addSessionMessage( "You've completed the '" . $instrument_event["instrument_label"] . "' Survey.<br> You've been rewarded a : <span class='fruit " . $fruits[$index] . "'></span>  Get the whole fruit basket!" , "success");
    }
  }
}

$pg_title 		= "Dashboard : $websiteName";
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
                      foreach($surveys as $idx => $survey){
                        $surveyname     = $survey["instrument_label"];
                        $surveytotal    = $survey["total_questions"];
                        $surveycomplete = $survey["completed_fields"];
                        $completeclass  = ($surveycomplete >= $surveytotal ? "completed":"");

                        $percent_complete = round(($surveycomplete/$surveytotal)*100,2);
                        print_r("<li class='surveys'>
                            <a href='$surveylink' class='".$fruits[$idx]." $completeclass' title='$surveyname : $percent_complete% Complete'>                                                        
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
                      <div class="weather"><?php echo $location ?></div>
                    </div>
                  </div>           
                  <div class="row bg-light dk m-b">
                    <div class="col-md-6 dker">
                      <section>
                        <header class="font-bold padder-v">
                          <div class="btn-group pull-right">
                            
                          </div>
                          You Spent: 
                        </header>
                        <div class="panel-body flot-legend">
                          <div id="piechart" style="height:240px"></div>
                        </div>
                      </section>
                    </div>
                    <div class="col-md-6">
                      
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
  google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback(drawChart);
  function drawChart() {

    var data = google.visualization.arrayToDataTable([
      ['Task', 'Minutes per Day'],
      
      ['Walking',     25],
      ['Sitting',     75],
      
    ]);

    //https://google-developers.appspot.com/chart/interactive/docs/gallery/piechart
    var options = {
      is3d : 'true',
      pieStartAngle :45,
      backgroundColor : '#E0E6F0',
      colors : ['#F8B300', '#297B9F'],
      chartArea:{left:20,top:0,width:'100%',height:'100%'}


    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart'));

    chart.draw(data, options);
  }
</script>
