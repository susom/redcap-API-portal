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
  include("../models/inc/surveys.php");
}

$shownavsmore   = true;
$survey_active  = '';
$profile_active = '';
$game_active    = '';
$assesments     = ' class="active"';
$pg_title       = "Profile : $websiteName";
$body_classes   = "dashboard profile";
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
                    <h2></h2>
                  </section>
                  <div class="row">
                    <div class="col-sm-1">&nbsp;</div>
                    <div class="col-sm-10">
                      <h2>My Assessments</h2>
                      provide links that will link to appropritate results in a pop up?
                      
                      <?
                        $_REQUEST["gender"] = "female";

                        $met_survey = $supp_surveys["Supp2"]->getSingleInstrument("met_physical_activity");
                        
                        print_rr($met_survey["completed_fields"]);


                        include("MET_detail.php");



                      ?>
                      
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

