<?php
require_once("../models/config.php");

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) { 
  $destination = "login.php";
  header("Location: " . $destination);
  exit; 
}else{
  //if they are logged in and active
  //find survey completion and go there?
  // GET SURVEY LINKS
  include("../models/inc/surveys.php");
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
                          <div class="pull-right">
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-sm btn-rounded btn-default dropdown-toggle">
                                <span class="dropdown-label">Week</span> 
                                <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu dropdown-select">
                                  <li><a href="#"><input type="radio" name="b">Month</a></li>
                                  <li><a href="#"><input type="radio" name="b">Week</a></li>
                                  <li><a href="#"><input type="radio" name="b">Day</a></li>
                              </ul>
                            </div>
                            <a href="#" class="btn btn-default btn-icon btn-rounded btn-sm">Go</a>
                          </div>
                          Statistics
                        </header>
                        <div class="panel-body">
                          <div id="flot-sp1ine" style="height:210px"></div>
                        </div>
                        <div class="row text-center no-gutter">
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">5,860</span>
                            <small class="text-muted m-b block">Orders</small>
                          </div>
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">10,450</span>
                            <small class="text-muted m-b block">Sellings</small>
                          </div>
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">21,230</span>
                            <small class="text-muted m-b block">Items</small>
                          </div>
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">7,230</span>
                            <small class="text-muted m-b block">Customers</small>                        
                          </div>
                        </div>
                      </section>
                    </div>
                    <div class="col-md-6">
                      <section>
                        <header class="font-bold padder-v">
                          <div class="btn-group pull-right">
                            <button data-toggle="dropdown" class="btn btn-sm btn-rounded btn-default dropdown-toggle">
                              <span class="dropdown-label">Last 24 Hours</span> 
                              <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-select">
                                <li><a href="#"><input type="radio" name="a">Today</a></li>
                                <li><a href="#"><input type="radio" name="a">Yesterday</a></li>
                                <li><a href="#"><input type="radio" name="a">Last 24 Hours</a></li>
                                <li><a href="#"><input type="radio" name="a">Last 7 Days</a></li>
                                <li><a href="#"><input type="radio" name="a">Last 30 days</a></li>
                                <li><a href="#"><input type="radio" name="a">Last Month</a></li>
                                <li><a href="#"><input type="radio" name="a">All Time</a></li>
                            </ul>
                          </div>
                          Analysis
                        </header>
                        <div class="panel-body flot-legend">
                          <div id="flot-pie-donut"  style="height:240px"></div>
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
