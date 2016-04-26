<?php
require_once("../models/config.php");

//POSTING DATA TO REDCAP API
if(isset($_REQUEST["ajax"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  //IF DOING A END OF SURVEY FINAL SUBMIT
  if(isset($_REQUEST["surveycomplete"])){
    $result     = RC::callApi(array(
        "hash"    => $_REQUEST["hash"], 
        "format"  => "csv"
      ), true, $custom_surveycomplete_API, $API_TOKEN);
    exit;
  }

  //WRITE TO API
  //ADD OVERIDE PARAMETER 
  $data       = array();
  foreach($_POST as $field_name => $value){
    if($value === 0){
      $value = (string) "0";
    }else if($value == ""){
      $value = NULL;
    }

    $record_id  = $project_name !== SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
    $event_name = $project_name !== SESSION_NAME ? null : $_SESSION[SESSION_NAME]["survey_context"]["event"];

    $data[] = array(
      "record"            => $record_id,
      "field_name"        => $field_name,
      "value"             => $value
    );
    if($event_name){
      $data["redcap_event_name"] = $event_name;
    }
    $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
  }
  exit;
}

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) { 
  $destination = $websiteUrl."login.php";
  header("Location: " . $destination);
  exit; 
}else{
  // GET $surveys
  include("../models/inc/surveys.php");
  include("inc/classes/Survey.php");
}

//THIS PAGE NEEDS A SURVEY ID
$surveyid = $_GET["sid"];
$project  = (isset($_GET["project"])? $_GET["project"]:null);

if($project){
  if(array_key_exists($project, SurveysConfig::$projects)){
    $supp_project = new Project($loggedInUser, $project, SurveysConfig::$projects[$project]["URL"], SurveysConfig::$projects[$project]["TOKEN"]);
    $surveys = $supp_project->getActiveAll();
  }
}

if(array_key_exists($surveyid, $surveys)){
  $survey_data    = $surveys[$surveyid];

  //ON SURVEY PAGE STORE THIS FOR USE WITH THE AJAX EVENTS 
  $_SESSION[SESSION_NAME]["survey_context"] = array("event" => $survey_data["event"]);

  //LOAD UP THE SURVEY PRINTER HERE
  $active_survey  = new Survey($survey_data);
}else{
  //IF BAD SURVEY ID PASSED, REDIRECT BACK TO DASHBOARD
  $destination = $websiteUrl."dashboard/index.php";
  header("Location: " . $destination);
  exit; 
}

//SOME PAGE SET UP
$shownavsmore   = false;
$survey_active  = ' class="active"';
$profile_active = '';
$game_active    = '';

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
                    <h2></h2>
                  </section>
                  <div class="row">
                    <div class="col-sm-1">&nbsp;</div>
                    <div class="col-sm-10 surveyFrame">
                    <?php
                      //PRINT OUT THE HTML FOR THIS SURVEY
                      $active_survey->printHTML();
                    ?>
                    </div>
                    <div class="col-sm-1">&nbsp;</div>
                    <div class="submits">
                      <?php
                        if(!$active_survey->surveycomplete){
                          ?>
                          <div class='progress progress-striped active'>
                            <div class='progress-bar bg-info lter' data-toggle='tooltip' data-original-title='<?php echo $active_survey->surveypercent?>%' style='width: <?php echo $active_survey->surveypercent?>%'></div>
                          </div>
                          <a href="index.php" class="btn btn-info" role="savereturnlater">Save and Exit</a> 
                          <button class="btn btn-primary" role="saverecord">Submit/Next</button>
                          <?php    
                        }
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
<script>
$(document).ready(function(){
<?php
  // //PASS FORMS METADATA 
  echo "var form_metadata       = " . json_encode($active_survey->raw) . ";\n";
  echo "var total_questions     = " . $active_survey->surveytotal . ";\n";
  echo "var user_completed      = " . json_encode($active_survey->completed) . ";\n";
  echo "var all_completed       = " . json_encode($all_completed) .";\n";
  echo "var all_branching       = " . json_encode($all_branching).";\n";
  echo "var completed_count     = " . count($active_survey->completed) . ";\n";
  echo "var surveyhash          = '".http_build_query($active_survey->hash)."';\n";
  
  $branching_function =  "function checkGeneralBranching(){\n";
    foreach($all_branching as $branch){
      $affected   = $branch["affected"];
      $effectors  = array();
      $ef_only    = array();
      foreach($branch["effector"] as $ef => $values){
        array_push($ef_only, "all_completed.hasOwnProperty('$ef')");
        
        $temp_arr = array();
        foreach($values as $value){
          $temp_arr[] = " all_completed['$ef'] == $value ";
        }
        $effectors[] = "(".implode(" || ",$temp_arr).")";
      }

      $branching_function .= "if((".implode(" && ", $ef_only).") && (".implode(" && ", $effectors).")){\n";
      $branching_function .= "\$('.$affected').show();\n";
      $branching_function .= "}else{\n";
      $branching_function .= "\$('.$affected').hide();\n";
      $branching_function .= "}\n";
    }
  $branching_function .= "return;\n";
  $branching_function .= "}\n";

  echo $branching_function;
?>
  //LAUNCH IT INITIALLY TO CHECK IF PAGE HAS BRANCHING
  checkGeneralBranching();

  function updateProgressBar(ref, perc){
    //UPDATE SURVEY PROGERSS BAR
    ref.attr("data-original-title",perc).css("width",perc);
    return;
  }

  function checkRequired(){
    //ANNOY USERS IF THEY DIDNT FILL OUT A FORM ITEM, PER SECTION!
    var required_fields = $("#customform section.active .required");
    var req_missing     = false;

    required_fields.each(function(){
      if( $(this).is(":visible") && (    ($(this).find(":input").is(':text')  && $(this).find(":input").val().length == 0)
          || ($(this).find(":input").is('select') && $(this).find(":input").val() == "-")
          || ($(this).find(":input").is(':radio') && $(this).find(":input:checked").length == 0) )
        ){
        //ONLY SHOW THE ANNOYING MESSAGE ONCE
        if( !$("#customform section.active").hasClass("annoying_message") ){
          req_missing = true;

          $("#customform section.active").addClass("annoying_message")
          var reqmsg  = $("<div>").addClass("required_message alert alert-danger").html("<ul><li>You have left required fields empty.  If this was intentional please click Submit again.<li></ul>");
          reqmsg.append($("<button>").addClass("btn btn-alert").text("Close"));
          $("body").append(reqmsg);
          return;
        }
      }
    });                

    return req_missing;
  }

  function checkValidation(){
    var validation_choices  = [ "date" ,"email" ,"integer" ,"number" ,"phone" ,"time" ,"zipcode" ,"date_dmy", "date_mdy", "date_ymd", "datetime_dmy", "datetime_mdy", "datetime_ymd", "datetime_seconds_dmy" ,"datetime_seconds_mdy", "datetime_seconds_ymd" ];
    var verifyjs            = $("#customform section.active").find(".notifyjs-container");
    if(verifyjs.is(":visible")){
      return true;
    }

    return false;        
  }

  function saveFormData(elem){
    var dataURL = "survey.php?ajax=1";
    var for_branch_name = elem.prop("name");
    var for_branch_val  = elem.val();

    //FOR CHECKBOX TYPES
    if(elem.is(":checkbox")){
      //REDCAP SEES THESE DIFFERENTLY, MUST TEMPORARILY ALTER INPUT ATTRIBUTES TO SUBMIT PROPERLY
      var optioncode  = elem.val();
      for_branch_val  = optioncode;
      var oldname     = elem.prop("name");
      var chkbx_name  = oldname + "___" + optioncode;;
      var isChecked   = elem.is(":checked") ? 1 : 0;

      elem.prop("name", chkbx_name);
      elem.prop("checked",true);
      elem.val(isChecked);
    }

    if(!elem.val()){
      elem.val(null);
    }

    //NOW UPDATE THE INMEMORY COMPLETED THING AND RUN THE PAGE BRANCHING CHECK
    all_completed[for_branch_name] = for_branch_val;
    checkGeneralBranching();

    //CHECK PROJECT
    var project = "&project=" + $("#customform").data("project");
    $.ajax({
      url:  dataURL,
      type:'POST',
      data: elem.serialize() + project,
      success:function(result){
        console.log(result);

        if(elem.is(":checkbox")){
          //GOTTA RESET THE checkbox properties haha
          elem.prop("name",oldname);
          elem.val(optioncode);

          if(!isChecked){
            elem.prop("checked",false);
          }
        } 

        //REMOVE THE SPINNER
        setTimeout(function(){
          $(".hasLoading").removeClass("hasLoading");
        },550);
      }
    });
  }

  //SET THE INTIAL PROGRESS BAR
  var pbar              = $(".progress-bar");
  var percent_complete  = Math.round((completed_count/total_questions)*100,2) + "%";
  updateProgressBar(pbar, percent_complete);

  //FIND THE PAGE OF THE LAST QUESTION SAVED AND JUMP TO THAT PANEL
  var answered_keys     = Object.keys(user_completed); 
  var last_answered     = answered_keys[completed_count - 1];
  var newactive         = $("div."+last_answered).closest("section");
  if(newactive.length){
    $("#customform section").removeClass("active");
    var panel = $("#customform section").index(newactive);
    newactive.addClass("active");
  }else{
    $("#customform section").first().addClass("active");
  }

  //SUBMIT/NEXT
  $("button[role='saverecord']").click(function(){
    $("#customform section.active").each(function(idx){
      //IF THERE IS ANOTHER SECTION THEN ITS A "NEXT" ACTION OTHERWISE, FINAL SUBMIT
      if($(this).next().length){
        if(checkValidation()){
          return;
        }

        if(checkRequired()){
          return;
        }

        $(".required_message").remove();
        if($(this).hasClass("active")){
          $(this).removeClass("active").addClass("inactive");
          $(this).next().addClass("active", function(){});
          $("#customform").animate({ scrollTop : 0}, function(){});
          return false;
        }
      }else{
        //SUBMIT ALL THOSE HIDDEN FORMS NOW
        $("#customform input[type='hidden']").each(function(){
          saveFormData($(this));
        });

        //SUBMIT AN ALL COMPLETE
        //REDIRECT TO HOME WITH A MESSAGE
        var dataURL         = "survey.php?ajax=1&surveycomplete=1";
        var instrument_name = $("#customform").attr("name");
        var project         = "&project=" + $("#customform").data("project");
        $.ajax({
          url:  dataURL,
          type:'POST',
          data: surveyhash + project,
          success:function(result){
            // console.log(result);
            location.href="index.php?survey_complete=" + instrument_name;
          }
        });
      }    
    });
    return;
  });

  //INPUT CHANGE ACTIONS
  $("#customform :input").change(function(){
    //SAVE JUST THIS INPUTS DATA
    $(this).closest(".inputwrap").find(".q_label").addClass("hasLoading");
    saveFormData($(this));

    //THE REST IS JUST FIGURING OUT THIS PROGRESS BAR
    var completed_count = 0;
    var total_questions = 0;
    for(var i in form_metadata){
      //UPDATE THE user_answer FIELD IN form_metadata
      if(form_metadata[i]["field_name"] == $(this).attr("name")){
        form_metadata[i]["user_answer"] = $(this).val();
      }

      //NOW DO A RUNNING COUNT
      if(form_metadata[i]["field_type"] !== "descriptive"){
        if(form_metadata[i]["branching_logic"] == ""){
          total_questions++;
        }

        if(form_metadata[i]["user_answer"] !== ""){
          completed_count++;
          if(form_metadata[i]["branching_logic"] !== ""){
            total_questions++;
          }
        }
      }
    }

    //IF THERES A NEXT QUESTION SCROLL DOWN TO IT!
    if($(this).closest(".inputwrap").nextAll(':visible:first')){
      var nextpos = $(this).closest(".inputwrap").nextAll(':visible:first').position();
      if(nextpos !== undefined && nextpos.top){
        // console.log("scroll man",nextpos.top);
        $("#customform").animate({ scrollTop : nextpos.top + "px"});
      }else{
        // console.log("maybe next input is hidden?");
      }
    }
    

    var pbar              = $(".progress-bar");
    var percent_complete  = Math.round((completed_count/total_questions)*100,2) + "%";
    updateProgressBar(pbar, percent_complete);
    return;
  }); 

  // $.mask.definitions['M'] = "[0|1|\s]";
});
</script>
