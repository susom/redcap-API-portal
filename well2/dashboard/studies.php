<?php
require_once("../models/config.php");

if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"]){
  $proj_name      = "Studies";
  $study_project  = new Project($loggedInUser, $proj_name, SurveysConfig::$projects[$proj_name]["URL"], SurveysConfig::$projects[$proj_name]["TOKEN"]);
}

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) {
  $destination = $websiteUrl."login.php";
  header("Location: " . $destination);
  exit;
}else{
  //if they are logged in and active
  // GET SURVEY LINKS
  include("../models/inc/surveys.php");
}

$shownavsmore   = true;
$survey_active  = ' ';
$profile_active = '';
$studies_active = 'class="active"';
$game_active    = '';
$assesments     = '';
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
                      <h2>My Studies</h2>
                      <form class="customform">
                        <div class="invite_code">
                          <label>
                            <span>Invite Code</span>
                            <input type="text" name="invite_code"/>
                          </label>

                          <a href="#" class="btn btn-large block btn-info editprofile"><span>Go to Study</span></a>
                        </div>
                      </form>
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
function saveFormData(elem){
  var dataDump = "profile.php?ajax=1";

  if(!elem.val()){
    elem.val(null);
  }

  $.ajax({
    url:  dataDump,
    type:'POST',
    data: elem.serialize(),
    success:function(result){
      console.log("Data Saved",result);
      
      //REMOVE THE SPINNER
      setTimeout(function(){
        $(".hasLoading").removeClass("hasLoading");
      },250);
    }
  });
}

$(document).ready(function(){
  $(".invite_code input").on("click",function(){
    if($(this).is(":focus")){
      $(".invite_code label").addClass("focus");
    }
  });
  $(".invite_code label span").click(function(){
    $(".invite_code input").focus();
  });
});
</script>
