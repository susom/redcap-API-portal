<?php
require_once("../models/config.php");

//DATA POSSTING
if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"]){
  if(isset($_REQUEST["surveycomplete"])){
    $result = RC::callApi(array(
        "hash" => $_REQUEST["hash"], 
        "format" => "csv"
      ),$custom_surveycomplet_API);

    print_r( $result );
    exit;
  }

  //WRITE TO API
  //ADD OVERIDE PARAMETER 
  $formdata = $_POST;

  $data   = array();
  foreach($_POST as $field_name => $value){
    if($value === 0){
      $value = "0";
    }

    if($value == ""){
      $value = NULL;
    }

    if(strpos("_complete", $field_name) > -1){
      //CALL andy CUSTOM API FOR set SurveyComplete
    }

    $data[] = array(
      "record"            => $_SESSION[SESSION_NAME]["user"]->id,
      "redcap_event_name" => $_SESSION[SESSION_NAME]["survey_context"]["event"],
      "field_name"        => $field_name,
      "value"             => $value,
    );
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"));
  

  echo json_encode($result);
  exit;
}

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

foreach($surveys as $survey){
  if($survey["survey_link"] == $iframe_src){
    $active_surveyid       = $survey["instrument_name"];
    $active_surveyname     = $survey["instrument_label"];
    $active_surveytotal    = $survey["total_questions"];
    $active_completed      = $survey["completed_fields"];
    $active_surveycomplete = $survey["survey_complete"];
    $active_surveypercent  = 0;
    $active_surveyevent    = $survey["instrument_arm"];
    $active_raw            = $survey["raw"];
    $active_surveylink     = $survey["survey_link"];

    //ON SURVEY PAGE STORE THIS FOR USE WITH THE AJAX EVENTS 
    $_SESSION[SESSION_NAME]["survey_context"] = array("event" => $active_surveyevent);

    if($active_surveycomplete){
      //SHOW HTML DATA INSTEAD
      $active_returncode = null;
    }

    //GET THE RAW  HTML DATA
    break;
  }
}

function processBranching($branch_logic){
  global $active_completed;
  $hideConditional  = true;

  //=,<,>,<=,>=,<>
  //and/or

  $condition_count = substr_count($branch_logic, "="); //multiple = signs means more than 1 condition 
  $sub_array_count = substr_count($branch_logic, "("); //if () exists that means it is a specific answer out of a set (checkbox)
                          
  if($sub_array_count == 0 && $condition_count == 1){
    $temp           = explode("=",$branch_logic);
    $effector_input = str_replace("]","",str_replace("[","",trim($temp[0])));
    $effector_value = str_replace("'","", trim($temp[1]));    
    if(array_key_exists($effector_input,$active_completed) && $active_completed[$effector_input] == $effector_value){
      $hideConditional = false;
    }
  }

  if($sub_array_count == 1 && $condition_count == 1){
    //[core_hispanic___4] => 1
    $temp           = explode("=",$branch_logic);
    $effector_input = str_replace("]","",str_replace("[","",trim($temp[0])));
    $effector_input = str_replace(")","",$effector_input);
    $effector_input = str_replace("(","___",$effector_input);
    if(array_key_exists($effector_input,$active_completed)){
      $hideConditional = false;
    }
  }

  // if($condition_count > 1){
  //   //[core_education_us] = '1' or [core_education_us] = '3'
  //   $or_count  = substr_count($branch_logic, " or "); 
  //   $and_count = substr_count($branch_logic, " and "); 
  //   $temp      = explode("=",$branch_logic);

  // }

  return $hideConditional;
}

$shownavsmore   = false;
$survey_active  = ' class="active"';
$profile_active = '';

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
                      <!-- <ul id="surveypagination" class="pagination pagination">
                        <li class="prev"><a href="#" data-panel="p"><i class="fa fa-chevron-left"></i></a></li>
                        <?php
                        $panels = array_filter($active_raw, function($field){
                          return !empty($field["section_header"]);
                        });
                        for($i = 0; $i < count($panels); $i++){
                          $pgcount  = $i+1;
                          echo "<li><a href='#' data-panel=$i>$pgcount</a></li>";
                        }
                        ?>
                        <li class="next"><a href="#" data-panel="n"><i class="fa fa-chevron-right"></i></a></li>
                      </ul> -->
                      <?php

function getLabelAnswer($fieldmeta){
  if(!empty($fieldmeta["user_answer"])){
    $user_answer = $fieldmeta["user_answer"];
    if($fieldmeta["field_type"] == "radio" || $fieldmeta["field_type"] == "checkbox" || $fieldmeta["field_type"] == "dropdown"){
      $possible_answers = explode("|",$fieldmeta["select_choices_or_calculations"]);
      foreach($possible_answers as $pa){
        $temp = explode(", ",$pa);
        if($user_answer == $temp[0]){
          $user_answer = $temp[1];
          break;
        }
      }
    }
    return array("field_label" => $fieldmeta["field_label"], "user_answer" => $user_answer);
  }
  return false; 
}

                        $yourAnswers = (!$active_surveycomplete ? "" : " : Your Answers");
                        echo "<h2 class='surveyHeader'>$active_surveyname $yourAnswers</h2>";
                        if($active_surveycomplete){
                          echo "<div class='survey_recap'>";
                          foreach($active_raw as $field){
                            if(!empty($field["section_header"])){
                              echo "<h4>" . $field["section_header"] ."</h4>";
                            }
                            if(!empty($field["user_answer"])){
                              $item = getLabelAnswer($field);
                              echo $item["field_label"] . " : " . $item["user_answer"] . "<br>";
                            }
                          }
                          echo "</div>";
                          echo "<style>.surveyFrame{ height:auto; }</style>";
                        }else{
                          echo "<form class='customform' id='customform' name='".$active_raw[0]["form_name"]."'>";
                          $branches = array_filter($active_raw, function($field){
                            return $field["branching_logic"] != "";
                          });

                          //AS LOOP THROUGH ANYTHING THAT MIGHT BE A BRANCHING POINT TOSS IN THIS ARRAY
                          $branches = array();
                          $sections = array();
                          $matrixes = array();
                          $first_section = true;
                          
                          $verify_map = array( 
                             "email"        => "email"
                            ,"integer"  => "number" 
                            ,"number" => "number"
                            ,"phone" => "phone"
                            ,"time" => "alphaNumeric"
                            ,"zipcode" => "number" 
                            ,"date_dmy" => "date"
                            ,"date_mdy" => "date"
                            ,"date_ymd" => "date"
                            ,"datetime_dmy" => "date"
                            ,"datetime_mdy" => "date"
                            ,"datetime_ymd" => "date"
                            ,"datetime_seconds_dmy" => "date"
                            ,"datetime_seconds_mdy" => "date"
                            ,"datetime_seconds_ymd" => "date"
                            );
    


                          foreach($active_raw as $field){
                            $html = "";
                            $show = true;
                            $branch_flag = false;
                            
                            $required_field                 = ($field["required_field"] == "y" ? "required" : "");
                            $field_name                     = $field["field_name"];
                            $section_header                 = $field["section_header"];
                            $field_type                     = $field["field_type"];
                            $field_note                     = $field["field_note"];
                            $field_label                    = $field["field_label"];
                            $select_choices_or_calculations = $field["select_choices_or_calculations"];
                            $branching_logic                = $field["branching_logic"];
                            $custom_alignment               = $field["custom_alignment"];
                            $matrix_group                   = $field["matrix_group_name"];
                            $validation_rules               = (array_key_exists($field["text_validation_type_or_show_slider_number"], $verify_map) ? $verify_map[$field["text_validation_type_or_show_slider_number"]] : "");

                            if($branching_logic != "") {
                              $branches[$field_name]  = $branching_logic;
                              $has_branching          = processBranching($branching_logic);
                              $branch_flag            = true;
                            }

                            //SECTION HEADERS CAN BE FORM INPUTS TOO
                            //SECTION HEADERS CAN BE DESCRIPTIVES TOO
                            if(!empty($section_header)){
                              if(!$first_section){
                                $html .= "</section>\n";
                                $makeactive = "";
                              }else{
                                // $makeactive = "active";
                              }

                              $html .= "<section class='section '>
                                          <h2>$section_header</h2>\n";
                              $first_section = false;
                            }


                            //DESCRIPTIVE CAN BE SECTION HEADER AS WELL BUT NOT FORM INPUT
                            if($field_type == "descriptive"){
                              $html .= "<h3>$field_label</h3>\n";
                            }

                            // $html   .= "<div class='sectionInputs'>\n";                       
                            //LETS JUST PRINT A REGULAR FIELD
                            if( $field_type !== "descriptive" ){
                              

                              if($matrix_group !== ""){
                                $html .= "<div class='table-responsive'>";
                                if(!in_array($matrix_group, $matrixes)){
                                  //THIS GETS ME ALL THE ITEMS WITH THIS MATRIX NAME AND THEIR PRESERVED KEYS 
                                  //SOME TIMES SOME ITEMS GET INSERTED IN BETWEEN MATRIX ROWS (WHY?!), SO INSTEAD OF FOREACH, DO A FOR FROM START Index TO END Index
                                  $all_matrix_group = array_filter($active_raw, function($item) use ($matrix_group){
                                    return $item["matrix_group_name"] == $matrix_group;
                                  });
                                  $matrix_range   = array_keys($all_matrix_group);
                                  $last_matrix    = array_pop($matrix_range);
                                  $first_matrix   = array_shift($matrix_range);

                                  $html .= "<div class='table-responsive'>\n";
                                  $html .= "<table class='table table-striped b-t b-light'>\n";
                                  $html .= "<thead>\n";
                                  $html .= "<th></th>";

                                  $options  = getAnswerOptions($select_choices_or_calculations);
                                  foreach($options as $val => $value){
                                    $html .= "<th class='text-center'>$value</th>\n";
                                  } 
                                  $html .= "</thead><tbody>\n";

                                  for($i = $first_matrix ; $i <= $last_matrix; $i++){
                                    $item       = $active_raw[$i];
                                    $field_name = $item["field_name"];
                                    $field_type = $item["field_type"];
                                    $field_label    = $item["field_label"];
                                    $required_field = ($item["required_field"] == "y" ? "required" : "");

                                    $html   .= "<tr>";
                                    $html   .= "<td>$field_label</td>";
                                    $options  = getAnswerOptions($item["select_choices_or_calculations"]);
                                    foreach($options as $val => $value){
                                      if($field_type == "radio"){
                                        $checked = (array_key_exists($field_name,$active_completed) && $active_completed[$field_name] == $val ? "checked" : "");
                                      }else{
                                        $altered_name = $field_name . "___" . $val;
                                        $checked = (array_key_exists($altered_name,$active_completed) ? "checked" : "");
                                      }
                                      $html .= "<td class='text-center'><label><input $required_field type='$field_type' name='$field_name' $checked value='$val'/></label></td>\n";
                                    } 
                                    
                                    $html .= "</tr>";
                                  }

                                  $html .= "</tbody></table>";
                                  array_push($matrixes,$matrix_group);
                                }

                              }else{
                                $has_branching = ($branch_flag ? "hasBranching" : "");
                                $html .= "<div class='inputwrap $field_name $custom_alignment $has_branching $required_field'>\n";
                                $html .= "<label class='q_label' for='$field_name'>$field_label</label>\n";

                                if($field_type == "dropdown"){
                                  $html .= "<select $required_field id='$field_name' name='$field_name' id='$field_name'>\n";
                                  $options = getAnswerOptions($select_choices_or_calculations);
                                  foreach($options as $val => $value){
                                    $selected  = (array_key_exists($field_name, $active_completed) && $active_completed[$field_name] == $val ? "selected" : "");
                                    $html     .= "<option $selected value='$val'>$value</option>\n";
                                  }
                                  $html .= "</select>\n";
                                }elseif($field_type == "notes"){
                                  $value = (array_key_exists($field_name,$active_completed) ? $active_completed[$field_name] : "");
                                  $html .= "<textarea $required_field id='$field_name' name='$field_name'>$value</textarea>\n";
                                }else{
                                  if($field_type == "text"){
                                    $value = (array_key_exists($field_name,$active_completed) ? $active_completed[$field_name] : "");
                                    $html .= "<input $required_field data-validate='$validation_rules' type='$field_type' id='$field_name' name='$field_name' value='$value'/>\n";
                                  }else{
                                    $options  = getAnswerOptions($select_choices_or_calculations);
                                    foreach($options as $val => $value){
                                      if($field_type == "radio"){
                                        $checked = (array_key_exists($field_name,$active_completed) && $active_completed[$field_name] == $val ? "checked" : "");
                                      }else{
                                        $altered_name = $field_name . "___" . $val;
                                        $checked = (array_key_exists($altered_name,$active_completed) ? "checked" : "");
                                      }
                                      $html .= "<label><input $required_field type='$field_type' name='$field_name' $checked value='$val'/> $value</label>\n";
                                    }
                                  }
                                }
                              }
                              if($field_note !== "") $html .= "<div class='fieldnote'>$field_note </div>";
                              $html .= "</div>\n";
                            }
      
                            if($show) echo $html; 
                          }
                          echo "</section></form>";

                          if(count($branches)){
                            echo "<script>\r";
                            echo "\$(document).ready(function(){\r";
                            $writeJsToPage     = array();
                            foreach($branches as $affected => $effector){
                              //NEED TO PARSE THE BRANCHES for $affected
                              //THEN ADD EVENTS TO PREVIOUS INPUTS $effectors
                              $condition_count = substr_count($effector, "="); //multiple = signs means more than 1 condition
                              $sub_array_count = substr_count($effector, "("); //if () exists that means it is a specific answer out of a set
                              
                              if($sub_array_count == 0 && $condition_count == 1){
                                //SINGLE ONE TO ONE BRANCHING
                                $temp           = explode("=",$effector);
                                $effector_input = str_replace("]","",str_replace("[","",trim($temp[0])));
                                $effector_value = str_replace("'","", trim($temp[1]));
                                $writeJsToPage[] = array("effector_input" => $effector_input, "effector_value" => $effector_value, "affected" => $affected);
                              }

                              if($sub_array_count == 1 && $condition_count == 1){
                                //[core_hispanic___4] => 1
                                $temp           = explode("=",$effector);
                                $effector_input = str_replace("]","",str_replace("[","",trim($temp[0])));
                                $effector_input = str_replace(")","",$effector_input);
                                $temp2          = explode("(",$effector_input);
                                $effector_input = $temp2[0];
                                $effector_value = str_replace("'","", trim($temp2[1]));
                                $writeJsToPage[] = array("effector_input" => $effector_input, "effector_value" => $effector_value, "affected" => $affected);
                              }

                            }

                            foreach($writeJsToPage as $item){
                              $effector_input = $item["effector_input"];
                              $effector_value = $item["effector_value"];
                              $affected       = $item["affected"];
                              ?>
                              $("input[name='<?php echo $effector_input?>'],select[name='<?php echo $effector_input ?>'],textarea[name='<?php echo $effector_input ?>']").change(function(){
                                if($(this).val() == '<?php echo $effector_value?>'){
                                  $('div.<?php echo $affected ?>').slideDown('fast');
                                }else{
                                  $('div.<?php echo $affected ?>').hide();
                                }
                              });
                              <?php
                            }
                            echo "});\r";
                            echo "</script>\r";
                          }
                        }
                      ?>
                    </div>
                    <div class="col-sm-1">&nbsp;</div>
                    

                    <div class="submits">
                      <?php
                        if(!$active_surveycomplete){
                        ?>
                        <div class='progress progress-striped  active'>
                          <div class='progress-bar bg-info lter' data-toggle='tooltip' data-original-title='<?php echo $active_surveypercent?>%' style='width: <?php echo $active_surveypercent?>%'></div>
                        </div>
                        <!-- <button class="btn btn-info btn-back" role="saveprevpage">Back</button>  -->
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
function checksize(){
  //SURVEY HEIGHT OVERFLOW AUTO THING
  var frameheight = $(".surveyFrame section.active").height();
  return;
}

function updateProgressBar(ref, perc){
  //UPDATE SURVEY PROGERSS BAR
  ref.attr("data-original-title",perc).css("width",perc);
}

function showBackBtn(){
  //SHOW SURVEY BACK BUTTON
  var activepanel = $("#customform section.active");
  var currentidx  = $("#customform section").index(activepanel);

  if(currentidx > 0){
    $("button.btn-back").fadeIn("slow");
  }else{
    $("button.btn-back").fadeOut("fast");
  }
}

function checkRequired(){
  //ANNOY USERS IF THEY DIDNT FILL OUT A FORM ITEM, PER SECTION!!!!
  var current_panel   = $("#surveypagination li.active a").data("panel");
  var required_fields = $("#customform section").eq(current_panel).find(".required");
  var confirm_empty   = false;
  required_fields.each(function(){
    if(!$(this).find(":input").val()){
      confirm_empty = true;
      
      if( !$("#customform section").eq(current_panel).hasClass("annoying_message") ){
        $("#customform section").eq(current_panel).addClass("annoying_message")
        var reqmsg  = $("<div>").addClass("required_message alert alert-danger").html("<ul><li>You have left required field(s) empty.  If this was intentional please click Submit again.<li></ul>");
        reqmsg.append($("<button>").addClass("btn btn-alert").text("Ok"));
        $("body").append(reqmsg);
        return false;
      }
    }
  });                

  return confirm_empty;
}

function checkValidation(){
  var validation_choices  = [ "date" ,"email" ,"integer" ,"number" ,"phone" ,"time" ,"zipcode" ,"date_dmy", "date_mdy", "date_ymd", "datetime_dmy", "datetime_mdy", "datetime_ymd", "datetime_seconds_dmy" ,"datetime_seconds_mdy", "datetime_seconds_ymd" ];
  var current_panel       = $("#surveypagination li.active a").data("panel");
  var verifyjs            = $("#customform section").eq(current_panel).find(".notifyjs-container");
  if(verifyjs.is(":visible")){
    return true;
  }

  return false;        
}
function saveFormData(elem){
  var dataDump = "survey.php?ajax=1";

  //FOR CHECKBOX TYPES
  if(elem.is(":checkbox")){
    var oldname     = elem.prop("name");
    var chkbx_name  = elem.prop("name");
    var optioncode  = elem.val();
    chkbx_name      += "___" + optioncode;
    var isChecked   = elem.is(":checked") ? 1 : 0;

    elem.prop("name", chkbx_name);
    elem.val(isChecked);
    elem.prop("checked",true);
  }

  if(!elem.val()){
    elem.val(null);
  }

  $.ajax({
    url:  dataDump,
    type:'POST',
    data: elem.serialize(),
    success:function(result){
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
      },250);
    }
  });
}

$(document).ready(function(){
  <?php
  $hash       = explode("s=", $active_surveylink);
  $surveyhash = array("hash"    => $hash[1]);
  // //PASS FORMS METADATA 
  echo "var form_metadata       = " . json_encode($active_raw) . ";\n";
  echo "var total_questions     = $active_surveytotal;\n";
  echo "var user_completed      = " . json_encode($active_completed) . ";\n";
  echo "var completed_count     = " . count($active_completed) . ";\n";
  echo "var surveyhash          = '".http_build_query($surveyhash)."'";
  ?>

  checksize();

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
    $("#surveypagination li").removeClass("active");

    var panel = $("#customform section").index(newactive);
    $("#surveypagination li").find("[data-panel="+panel+"]").parent().addClass("active");
    newactive.addClass("active");
  }else{
    $("#surveypagination li").find("[data-panel=0]").parent().addClass("active");
    $("#customform section").first().addClass("active");
  }

  //PAGINATION
  $("#surveypagination a").click(function(){
    //DO NOT ALLOW FOR PAGE JUMPING, HEIL!
    // return false; 
    if(checkValidation()){
      return;
    }

    //check to see if required fields are filled
    if(checkRequired()){
      return;
    }
    
    var panel = 0;
    if($(this).data("panel") === parseInt($(this).data("panel"))){
      panel = $(this).data("panel");
      $("#customform section").removeClass("active");
      $("#surveypagination li").removeClass("active");
      $(this).parent().addClass("active");
    }else{
      panel = $("#surveypagination li.active a").data("panel");
      if($(this).data("panel") == "p"){
        panel--;
      }else{
        panel++;
      }
      if(panel < 0 || panel > $("#customform section").length -1){
        return false;
      }else{
        $("#customform section").removeClass("active");
        $("#surveypagination li").removeClass("active");
        $("#surveypagination li").find("[data-panel="+panel+"]").parent().addClass("active");
      }   
    }
    $("#customform section").eq(panel).addClass("active");
    showBackBtn();
    checksize();
    return false;
  });

  //NEXT PREVIOUS SURVEY PANELS 
  $("button[role='saverecord']").click(function(){
    $("#customform section").each(function(idx){
      if($(this).next().length){
        //check to see if required fields are filled
        if(checkValidation()){
          return;
        }

        if(checkRequired()){
          return;
        }

        if($(this).hasClass("active")){
          $(this).removeClass("active").addClass("inactive");
          $(this).next().addClass("active");

          //in this case .eq 
          //the previous button counts as 0
          //idx = the current panel not the "next" one
          //so going forward direction it is idx + 2
          $("#surveypagination li").removeClass("active");
          $("#surveypagination li").eq(idx+2).addClass("active");
          showBackBtn();
          checksize();
          return false;
        }
      }else{
        //SUBMIT AN ALL COMPLETE
        //REDIRECT TO HOME WITH A MESSAGE

        var dataDump        = "survey.php?ajax=1&surveycomplete=1";
        var instrument_name = $("#customform").attr("name");
        $.ajax({
          url:  dataDump,
          type:'POST',
          data: surveyhash,
          success:function(result){
            location.href="index.php?survey_complete=" + instrument_name;
          }
        });
      }    
    });
  });

  $("button[role='saveprevpage']").click(function(){
    $($("#customform section").get().reverse()).each(function(idx){
      if($(this).hasClass("active")){
        if($(this).prev().length){
          //check to see if required fields are filled
          if(checkValidation()){
            return;
          }
          
          if(checkRequired()){
            return;
          }

          $(this).removeClass("active");
          $(this).prev().removeClass("inactive").addClass("active");
          
          //in this case .eq 
          //is really messed up cause its counting in reverse
          //so going backward direction its actually total count - idx
          var totalcount = $("#customform section").length;
          $("#surveypagination li").removeClass("active");
          $("#surveypagination li").eq(totalcount - idx - 1).addClass("active");
          showBackBtn();
          checksize();
        }
        return false;
      }
    });
  });

  //INPUT CHANGE ACTIONS
  $("#customform :input").change(function(){
    //SAVE JUST THIS INPUTS DATA
    $(this).closest(".inputwrap").find(".q_label").addClass("hasLoading");
    saveFormData($(this));

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

    var pbar              = $(".progress-bar");
    var percent_complete  = Math.round((completed_count/total_questions)*100,2) + "%";
    updateProgressBar(pbar, percent_complete);
  }); 
});
</script>
