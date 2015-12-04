<?php
require_once("../models/config.php");

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) { 
  $destination = $websiteUrl."login.php";
  header("Location: " . $destination);
  exit; 
}else{
  //if they are logged in and active
  //find survey completion and go there?
  // GET SURVEY LINKS
  include("../models/surveys.php");
}

$surveyurl  = $_GET["url"];
$iframe_src = urldecode(str_replace("local","loc",$surveyurl));

$navmini        = true;
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
                    
                  </section>

                  <div class="row">
                    <div class="col-sm-9">
                      <div class="row">
                        <div class="col-sm-1">&nbsp;</div>
                        <div class="col-sm-11 surveyFrame">
                          <iframe id="surveyFrame" frameborder="0" width="100%" height="1000" scrolling="auto" name="eligibilityScreener" 
                        src="<?php echo $iframe_src; ?>"></iframe>
                        </div>
                        <div class="submits">
                          <!-- <button class="btn btn-warning">Save & Exit</button> <button class="btn btn-primary">Submit</button> -->
                        </div>
                      </div>
                    </div>

                    <div class="col-sm-3">
                      <div class="well">
                        <h2>Survey Completion</h2>
                        <h3 class="percent_complete">0%! <span>0 of <?php count($surveys); ?> complete</span></h3>
                        <div class="footer-links">
                          <ul class="surveys">
                            <?php
                            $fruits = array("grapes","apple","orange","cherry","banana");
                            foreach($surveys as $idx => $survey){
                              $surveylink     = "survey.php?url=".urlencode($survey[3]);
                              $surveyname     = $survey[0];
                              $surveycomplete = $survey[4] +1;
                              print_r("<li >
                                  <a href='$surveylink' class='".$fruits[$idx]."'>                                                        
                                    <span >$surveyname</span>
                                  </a>
                                  <div class='progress progress-sm progress-striped  active'>
                                    <div class='progress-bar bg-info lter' data-toggle='tooltip' data-original-title='".$surveycomplete."%' style='width: ".$surveycomplete."%'></div>
                                  </div>
                                </li>\n");
                            }
                            ?>
                          </ul>
                        </div>
                      </div>
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
<script>
var ifr     = document.getElementById( "surveyFrame" );
var ifrDoc  = ifr.contentDocument || ifr.contentWindow.document;

var theForm = ifrDoc.getElementById( "form" );

console.log(theform);
</script>
