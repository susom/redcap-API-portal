<?php
require_once("../models/config.php");

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) { 
  $destination = $websiteUrl."login.php";
  header("Location: " . $destination);
  exit; 
}elseif(!isset($_GET["url"])){
  //IF NO URL PASSED IN THEN REDIRECT BACK
  $destination = $websiteUrl."login.php";
  header("Location: " . $destination);
  exit; 
}else{
  //if they are logged in and active
  //find survey completion and go there?
  // GET SURVEY LINKS
  include("../models/inc/surveys.php");
}

$surveyurl              = $_GET["url"];
$iframe_src             = urldecode($surveyurl);

$active_surveyname      = null;
$active_surveytotal     = null;
$active_surveycomplete  = null;
$active_surveypercent   = null;
$active_surveyevent     = null;
$active_returncode      = null; 

foreach($surveys as $survey){
  if($survey["survey_link"] == $iframe_src){
    $active_surveyid       = $survey["instrument_name"];
    $active_surveyname     = $survey["instrument_label"];
    $active_surveytotal    = $survey["total_questions"];
    $active_surveycomplete = $survey["completed_fields"];
    $active_surveypercent  = 0;

    $active_surveyevent    = $survey["instrument_arm"];
    $active_returncode     = $survey["return_code"];
    $active_metadata       = $survey["meta_data"];
    break;
  }
}

$hidenavs       = true;
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
                    <h2 class="surveyHeader"><?php /* $active_surveyname */ ?></h2>
                  </section>
                  <div class="row">
                    <div class="col-sm-1">&nbsp;</div>
                    <div class="col-sm-10 surveyFrame">
                      <iframe id="surveyFrame" frameborder="0" width="100%" scrolling="auto"></iframe>
                    </div>
                    <div class="col-sm-1">&nbsp;</div>
                    <div class="submits">
                      <div class='progress progress-striped  active'>
                        <div class='progress-bar bg-info lter' data-toggle='tooltip' data-original-title='<?php echo $active_surveypercent?>%' style='width: <?php echo $active_surveypercent?>%'></div>
                      </div>
                      <button class="btn btn-primary" role="saverecord">Submit</button>
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
function redirectReturn(surveyUrl,returnCode){
  var ipt = $('<input>').attr('name','__code').attr('type','hidden').val(returnCode);
  var frm = $('<form>').attr('action',surveyUrl).attr('method','POST').append(ipt);
  $('#surveyFrame').contents().find('body').append(frm);
  frm.submit();
  return;
}

function updateProgressBar(ref, perc){
  ref.attr("data-original-title",perc).css("width",perc);
}

<?php
  //ALLOWED CROSS ORIGIN DOMAINS
  echo "var allowed_child_origin = '$websiteAllowedChildOrigin';\n";

  //IF THERE IS A RETURN CODE
  if($active_returncode){
    echo "redirectReturn('$iframe_src','$active_returncode')\n";
  }

  //PASS FORMS METADATA 
  echo "var instrument_metadata = " . json_encode($active_metadata) . ";\n";
  echo "var unbranched_count    = $active_surveytotal;\n";
?>

//THIS ALLOWS CROSS SITE COMMUNICATION BETWEEN SITES WE BOTH OWN
var frame = document.getElementById("surveyFrame").contentWindow;
setTimeout(function(){
  //RACE CONDITION ~ 400ms ??!! GOTTA LOAD THE CHILD ALL THE WAY BEFORE DOING STUFF TO IT
  
  //ADD LISTENERS TO SAVE/RETURN BUTTONS
  $(".submits button").click(function(){
    var command = "submit-btn-" + $(this).attr("role");
    frame.postMessage({"action" : command}, allowed_child_origin);

    location.href="index.php?survey_complete=" + "<?php echo $active_surveyid?>";
    return false; 
  });

  //PASS SOME SELF INFO TO THE CHILD FRAME
  frame.postMessage({"metadata" : instrument_metadata, "unbranched_count" : unbranched_count}, allowed_child_origin);
},400);

//SET UP EVENT LISTENER TO LISTEN TO MESSAGES FROM CHILD
window.addEventListener('message', function(event) {
    // IMPORTANT: Check the origin of the data!
    //~ converts -1 to 0, which saves you having to do "!= -1" on the result of the indexOf
    if (~event.origin.indexOf(allowed_child_origin)) {
        // The data has been sent from your site
        // The data sent with postMessage is stored in event.data

        var payload = event.data;
        if(payload.hasOwnProperty("percent_complete")){
          //UPDATE THE PROGRESS BAR
          var pbar = $(".progress-bar");
          updateProgressBar(pbar, payload.percent_complete);
        }
        return;
    } else {
        // The data hasn't been sent from your site!
        // Be careful! Do not use it.
        return;
    }
});
</script>
