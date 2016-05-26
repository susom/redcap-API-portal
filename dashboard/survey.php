<?php
require_once("../models/config.php");

//SPECIAL CUSTOM MET SCORECAPTURE
if(isset($_REQUEST["met"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = $_REQUEST["sid"] ?: null;
  $value        = $_REQUEST["met_score"] ?: null;

  $data[] = array(
      "record"            => $record_id,
      "field_name"        => 'met_score',
      "value"             => $value
    );

  if($event_name){
    $data[0]["redcap_event_name"] = $event_name;
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
  print_r($data);
  exit;
}

//POSTING DATA TO REDCAP API
if(isset($_REQUEST["ajax"])){
  $project_name = $_REQUEST["project"] ?: null;
  $projects     = SurveysConfig::$projects;
  $API_TOKEN    = $projects[$project_name]["TOKEN"];
  $API_URL      = $projects[$project_name]["URL"];

  $data         = array();
  $record_id    = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
  $event_name   = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

  $survey_id    = $_REQUEST["sid"] ?: null;
  
  //IF DOING A END OF SURVEY FINAL SUBMIT
  if(isset($_REQUEST["surveycomplete"])){
    $result     = RC::callApi(array(
        "hash"    => $_REQUEST["hash"], 
        "format"  => "csv"
      ), true, $custom_surveycomplete_API, $API_TOKEN);
  }

  //WRITE TO API
  //ADD OVERIDE PARAMETER 
  
  unset($_POST["project"]);
  foreach($_POST as $field_name => $value){
    if($value === 0){
      $value = 0;
    }else if($value == ""){
      $value = NULL;
    }

    $record_id  = $project_name !== $_CFG->SESSION_NAME ? $loggedInUser->{$project_name} : $loggedInUser->id;
    $event_name = $project_name !== $_CFG->SESSION_NAME ? null : $_SESSION[$_CFG->SESSION_NAME]["survey_context"]["event"];

    $is_date    = preg_match('/^\d{2}-\d{2}\-\d{4}$/', $value);
    if($is_date){
      list($mm,$dd,$yyyy) = explode("-",$value);
      $value = "$yyyy-$mm-$dd";
    }

    $data[] = array(
      "record"            => $record_id,
      "field_name"        => $field_name,
      "value"             => $value
    );

    if($event_name){
      $data[0]["redcap_event_name"] = $event_name;
    }
    $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
    print_r($data);
    print_r($result);
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
    $supp_project = $supp_surveys[$project]->getSingleInstrument($surveyid);
    $surveys      = $supp_surveys[$project]->getActiveAll();
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
$assesments     = '';
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

                    <cite class="redcap">Powered by REDCap</cite>
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
  $isMET = "met_physical_activity" ? "true" : "false";
  echo "var isMET               = $isMET ;\n";
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
      $andor      = $branch["andor"];
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

      $branching_function .= "if((".implode(" $andor ", $ef_only).") && (".implode(" $andor ", $effectors).")){\n";
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
          var reqmsg  = $("<div>").addClass("required_message alert alert-danger").html("<ul><li>You have left some fields empty.  If this was intentional please click Submit/Next again or go back and provide the missing information.<li></ul>");
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
        // console.log("result from save:",result);

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

  //CUSTOM WORK FOR MET SURVEY
  if(isMET){
    showMETScoring();
  }

  //SUBMIT/NEXT
  $("button[role='saverecord']").click(function(){
    $("#customform section.active").each(function(idx){
      //IF THERE IS ANOTHER SECTION THEN ITS A "NEXT" ACTION OTHERWISE, FINAL SUBMIT
      if(checkValidation()){
        return;
      }

      if(checkRequired()){
        return;
      }

      if($(this).next().length){
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
        var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
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
    // console.log($(this));
    //SAVE JUST THIS INPUTS DATA
    $(this).closest(".inputwrap").find(".q_label").addClass("hasLoading");
    
    saveFormData($(this));
    if(isMET){
      showMETScoring();
    }



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
    var nextEl  = $(this).closest(".inputwrap").nextAll(':visible:first');
    if(nextEl){
      var nextpos = nextEl.position();
      if(nextpos !== undefined && nextpos.top){
        var nexttop       = nextpos.top;
        $("#customform").animate({ scrollTop :  nexttop},800);
      }
    }
    

    var pbar              = $(".progress-bar");
    var percent_complete  = Math.round((completed_count/total_questions)*100,2) + "%";
    updateProgressBar(pbar, percent_complete);
    return;
  }); 


  
  //CUSTOM SCORING FOR MET / MAT SURVEYS
  function getBMI(met_weight_pound, met_height_total_inch){
    var BMI = (met_weight_pound * 703)/(Math.pow(met_height_total_inch,2));
    return Math.round(BMI,2);
  }

  function getMETScore(gender,age,bmi,isSmoker,PA_level){
    //HARD CONSTANTS
    PA_SCORE = [];
    if(gender == "male"){
      PA_SCORE[1] = 0.37;
      PA_SCORE[2] = 0.51;
      PA_SCORE[3] = 1.03;
      PA_SCORE[4] = 1.48;
    }else{
      PA_SCORE[1]   = 0.27;
      PA_SCORE[2]   = 0.36;
      PA_SCORE[3]   = 0.77;
      PA_SCORE[4]   = 1.22;
    }
    phys_act_score = PA_SCORE[PA_level];
    
    //LINEAR WEIGHTs
    var x_age    = gender == "male" ? .10    : .16;
    var x_bmi    = gender == "male" ? .20    : .32;
    var x_smoker = gender == "male" ? .29    : .41;
    var x_const  = gender == "male" ? 12.77  : 12.26;

    var MetScore = (age*x_age) - .002*(Math.pow(age,2)) - (bmi*x_bmi) + phys_act_score - x_smoker*isSmoker + x_const;
    return Math.round(MetScore);
  }

  function showMETScoring(){
    //GATHER ALL AND IF THEY ARE ALL FILLED OUT SHOW THE SCORE
    var age       = $('#met_age').val();

    var foot      = $('#met_height_ft :selected').val();
    var inch      = $('#met_height_inch :selected').val();
    var weight    = $('#met_weigh_pound :selected').val();
    var height    = parseInt(foot)*12 + parseInt(inch);
    var bmi       = getBMI(weight, height);
    var gender    = $('.met_gender input:checked').val();
    var ughgender = gender == 2 || gender == 4 ? "female" : "male";
    var isSmoker  = $('.met_smoker input:checked').val();
    var PA_level  = $('.met_pa_level input:checked').val();

    if(age > 0 && bmi > 0 && !isEmpty(gender) && !isEmpty(isSmoker) && !isEmpty(PA_level)) {
      var METScore    =  getMETScore(ughgender,age,bmi,isSmoker,PA_level);
      
      var dataURL         = "survey.php?met=1";
      var instrument_name = $("#customform").attr("name");
      var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
      $.ajax({
        url:  dataURL,
        type:'POST',
        data: project + "&met_score=" + METScore,
        success:function(result){
          console.log(result);
        }
      });

      var nextSection = $("#customform section.active").next();
      var dataURL         = "MET_detail.php?gender=" + ughgender;
      $.ajax({
        url:  dataURL,
        type:'POST',
        data: null,
        success:function(result){
          nextSection.prepend(result);
          $("#met_score").text(METScore);
        }
      });
    }
  }

  function isEmpty($v){
    return $v == null || $v == undefined;
  }

  // $.mask.definitions['M'] = "[0|1|\s]";
});
</script>
