<?php
require_once("../models/config.php");

//POSTING DATA TO REDCAP API
if(isset($_REQUEST["ajax"])){
  if(isset($_REQUEST["surveycomplete"])){
    $result = RC::callApi(array(
        "hash"    => $_REQUEST["hash"], 
        "format"  => "csv"
      ),$custom_surveycomplet_API);
    exit;
  }

  //WRITE TO API
  //ADD OVERIDE PARAMETER 
  $data = array();
  foreach($_POST as $field_name => $value){
    if($value === 0){
      $value = "0";
    }

    if($value == ""){
      $value = NULL;
    }

    $data[] = array(
      "record"            => $_SESSION[SESSION_NAME]["user"]->id,
      "redcap_event_name" => $_SESSION[SESSION_NAME]["survey_context"]["event"],
      "field_name"        => $field_name,
      "value"             => $value
    );
    $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"));
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
}

//THIS PAGE NEEDS A SURVEY ID
$surveyid = $_GET["sid"];

//LOAD UP THE SURVEY HERE AND PRINT OUT THE HTML
class Survey {
  PUBLIC $surveyname;
  PUBLIC $surveytotal;
  PUBLIC $completed;
  PUBLIC $surveycomplete;
  PUBLIC $surveypercent;
  PUBLIC $raw;
  PUBLIC $hash;
  PRIVATE $fieldtype_map;

  public function __construct( $survey_data ){
    $this->surveyname     = $survey_data["label"];
    $this->surveytotal    = $survey_data["total_questions"];
    $this->completed      = $survey_data["completed_fields"];
    $this->surveycomplete = $survey_data["survey_complete"];
    $this->surveypercent  = 0;
    $this->raw            = $survey_data["raw"];

    $hash                 = explode("s=", $survey_data["survey_link"]);
    $this->hash           = array("hash" => $hash[1]);
  }

  private function processBranching($target, $branch_logic){
    $hideConditional  = true;
    //=,<,>,<=,>=,<>
    //and/or

    $condition_count  = substr_count($branch_logic, "="); //multiple = signs means more than 1 condition 
    $sub_array_count  = substr_count($branch_logic, "("); //if () exists that means it is a specific answer out of a set (checkbox)

    if($condition_count > 1 && $sub_array_count == 0){
      //THIS MEANS THERE ARE MULTIPLE CONDITIONS BUT NO MIXED COMBOS
      $or_match = '/\[(?<effector>\w+)\] (?<operator>=|<|>|<>|!=|<=|>=) \'(?<value>\w+)\'\s?(?:or)?/';
      preg_match_all($or_match, $branch_logic, $matches);
      
      $affected   = $target;
      $condition  = array();
      foreach($matches["effector"] as $idx => $effector_input){
        $effector_operator  = $matches["operator"][$idx];
        $effector_value     = $matches["value"][$idx];
        $effector_operator  = ($effector_operator == "=" ? "==" : $effector_operator);

        $field_type = $this->fieldtype_map[$effector_input];
        $radioCheck = ( $field_type == "radio" || $field_type == "checkbox" ? true: false );
        $formtype   = ($radioCheck ? "\$(\":input[name='$effector_input']:checked\").val() $effector_operator '$effector_value'" : "\$(\":input[name='$effector_input']\").val() $effector_operator '$effector_value'");

        $condition[] = $formtype;
      }

      $jsaction   = "";
      $jsaction .= "\tif(". implode(" || ", $condition) ."){\n";
      $jsaction .= "\t\t\$('div.".$affected."').slideDown('fast');\n";
      $jsaction .= "\t}else{\n";
      $jsaction .= "\t\t\$('div.".$affected."').hide();\n";
      $jsaction .= "\t}\n";

      $returnHTML = $jsaction;
      $returnHTML .= "\t\$(\"input[name='$effector_input'],select[name='$effector_input'],textarea[name='$effector_input']\").change(function(){";
      //NEED TO CHECK FOR RANGES TOO >= <= > < != 
      $returnHTML .= $jsaction;
      $returnHTML .= "\t});";

      return $returnHTML;
    }

    if($sub_array_count == 0 && $condition_count == 1){
      //SINGLE ONE TO ONE BRANCHING a == b

      $temp           = explode("=",$branch_logic);
      $effector_input = str_replace("]","",str_replace("[","",trim($temp[0])));
      $effector_value = str_replace("'","", trim($temp[1]));  
      $affected   = $target;
      
      $field_type = $this->fieldtype_map[$effector_input];
      $radioCheck = ( $field_type == "radio" || $field_type == "checkbox" ? true: false );
      $formtype   = ($radioCheck ? "\$(\":input[name='$effector_input']:checked\").val() == '$effector_value'" : "\$(\":input[name='$effector_input']\").val() =='$effector_value'");
      
      $jsaction   = "";
      $jsaction .= "\tif($formtype){\n";
      $jsaction .= "\t\t\$('div.".$affected."').slideDown('fast');\n";
      $jsaction .= "\t}else{\n";
      $jsaction .= "\t\t\$('div.".$affected."').hide();\n";
      $jsaction .= "\t}\n";
      
      $returnHTML = $jsaction;
      $returnHTML .= "\t\$(\":input[name='$effector_input'],select[name='$effector_input'],textarea[name='$effector_input']\").change(function(){";
      //NEED TO CHECK FOR RANGES TOO >= <= > < != 
      $returnHTML .= $jsaction;
      $returnHTML .= "\t});";

      return $returnHTML;
    }

    if($sub_array_count == 1 && $condition_count == 1){
      //SINGLE ONE TO ONE BRANCHING a(9) == b  (radio or checkbox)
      $temp             = explode("=",$branch_logic);
      $effector_input   = str_replace("]","",str_replace("[","",trim($temp[0])));
      $effector_input   = str_replace(")","",$effector_input);
      $affected   = $target;
      $temp2            = explode("(",$effector_input);
      $effector_input   = $temp2[0];
      $effector_value   = str_replace("'","", trim($temp2[1]));

      $field_type = $this->fieldtype_map[$effector_input];
      $radioCheck = ( $field_type == "radio" || $field_type == "checkbox" ? true: false );
      $formtype   = ($radioCheck ? "\$(\":input[name='$effector_input']:checked\").val() == '$effector_value'" : "\$(\":input[name='$effector_input']\").val() =='$effector_value'");

      $jsaction   = "";
      $jsaction .= "\tif($formtype){\n";
      $jsaction .= "\t\t\$('div.".$affected."').slideDown('fast');\n";
      $jsaction .= "\t}else{\n";
      $jsaction .= "\t\t\$('div.".$affected."').hide();\n";
      $jsaction .= "\t}\n";
      $returnHTML = $jsaction;
      $returnHTML .= "\t\$(\"input[name='$effector_input'],select[name='$effector_input'],textarea[name='$effector_input']\").change(function(){";
      //NEED TO CHECK FOR RANGES TOO >= <= > < != 
      $returnHTML .= $jsaction;
      $returnHTML .= "\t});";

      return $returnHTML;
    }

    return $hideConditional;
  }

  private function makeDropdown($field_name,$required_field, $select_choices_or_calculations, $field_value = null){
    $section_html     = array();

    $section_html[]   = "<select $required_field name='$field_name' id='$field_name'>";
    $section_html[]   = "<option>-</option>";
    $options          = SELF::getAnswerOptions($select_choices_or_calculations);
    foreach($options as $val => $value){
      $selected       = (array_key_exists($field_name, $this->completed) && $this->completed[$field_name] == $val ? "selected" : "");
      $section_html[] = "<option $selected value='$val'>$value</option>";
    }
    $section_html[]   = "</select>";
    return $section_html;
  }

  private function makeTextarea($field_name,$required_field, $field_value = null){
    $section_html     = array();

    $value            = (array_key_exists($field_name,$this->completed) ? $this->completed[$field_name] : "");
    $section_html[]   = "<textarea $required_field id='$field_name' name='$field_name'>$value</textarea>";
    return $section_html;
  }

  private function makeHidden($field_name, $field_type, $field_value){
    $section_html   = array();
    if(!is_null($field_value)){
      $section_html[] = "<input type='$field_type' id='$field_name' name='$field_name' value='$field_value'/>";
    }
    return $section_html;
  }
  
  private function makeReadonly($field_name, $field_type, $field_value){
    $section_html   = array();
    if(!is_null($field_value)){
      $section_html[] = "<input type='text' $field_type id='$field_name' name='$field_name' value='$field_value'/>";
    }
    return $section_html;
  }

  private function makeTextinput($field_name, $required_field, $field_type, $validation_rules, $field_value = null){
    $section_html   = array();
    $value          = (array_key_exists($field_name,$this->completed) ? $this->completed[$field_name] : "");
    $section_html[] = "<input $required_field data-validate='$validation_rules' type='text' id='$field_name' name='$field_name' value='$value'/>";
    return $section_html;
  }

  private function makeRadioOrCheck($field_name,$required_field, $select_choices_or_calculations, $field_type, $field_value = null){
    $section_html   = array();
    $options        = SELF::getAnswerOptions($select_choices_or_calculations);
    foreach($options as $val => $value){
      if($field_type == "radio"){
        $checked      = (array_key_exists($field_name,$this->completed) && $this->completed[$field_name] == $val ? "checked" : "");
      }else{
        $altered_name = $field_name . "___" . $val;
        $checked      = (array_key_exists($altered_name,$this->completed) ? "checked" : "");
      }
      $section_html[] = "<label><input $required_field type='$field_type' name='$field_name' $checked value='$val'/> $value</label>\n";
    }
    return $section_html;
  }

  public function getLabelAnswer($fieldmeta){
    if(!empty($fieldmeta["user_answer"])){
      $user_answer = $fieldmeta["user_answer"];
      if(  $fieldmeta["field_type"] == "radio" 
        || $fieldmeta["field_type"] == "checkbox" 
        || $fieldmeta["field_type"] == "dropdown"){
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

  public function getAnswerOptions($choices){
    //GET PRE BAKED ANSWER FROM USER CHOICE #
    $answer_choices = explode(" | ",$choices);
    $select_choices = array();

    foreach($answer_choices as $qa){
      if($qa){
        $temp = explode("," , $qa);
        $select_choices[trim($temp[0])] = trim($temp[1]);
      }
    }

    return $select_choices;
  }

  private function doActionTags($tags){
    $actions = array();
    foreach($tags as $tag => $v){
      if(strpos($tag,"@READONLY") > -1){
        $actions["field_type"] = "readonly";
        continue;
      }
      
      if(strpos($tag,"@HIDDEN") > -1){
        $actions["field_type"] = "hidden";
        continue;
      }

      if(strpos($tag,"@NOW") > -1){
        $actions["field_value"] = Date("Y-m-d");
        continue;
      }else if(strpos($tag,"@TODAY") > -1){
        $actions["field_value"] = Date("Y-m-d H:i:s");
        continue;
      }
    }

    return $actions;

  }

  public function getActionTags($fieldmeta){
    $re = "/  (?(DEFINE)
         (?<number>    -? (?= [1-9]|0(?!\\d) ) \\d+ (\\.\\d+)? ([eE] [+-]? \\d+)? )    
         (?<boolean>   true | false | null )
         (?<string>    \" ([^\"\\\\\\\\]* | \\\\\\\\ [\"\\\\\\\\bfnrt\\/] | \\\\\\\\ u [0-9a-f]{4} )* \" )
         (?<array>     \\[  (?:  (?&json)  (?: , (?&json)  )*  )?  \\s* \\] )
         (?<pair>      \\s* (?&string) \\s* : (?&json)  )
         (?<object>    \\{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \\s* \\} )
         (?<json>      \\s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) )  ) \\s*
         (?<tag>       \\@(?:[[:alnum:]])*)
      )
      
      (?'actiontag'
        (?:\\@(?:[[:alnum:]_-])*)
      )
      (?:\\=
        (?:
         (?:
          (?'params_json'(?&json))
         )
         |
         (?:
           (?'params'(?:[[:alnum:]_-]+))
         )
        )
      )?/ixm"; 

    $str      = $fieldmeta["field_annotation"];
    preg_match_all($re, $str, $matches);

    $results  = array();
    foreach($matches["actiontag"] as $key => $tag){
      $params = false;
      if(!empty($matches["params_json"][$key])){
        $params = json_decode($matches["params_json"][$key],1);
      }elseif(!empty($matches["params"][$key])){
        $params = $matches["params"][$key];
      }
      $results[$tag] = $params;
    }
    
    return SELF::doActionTags($results);
  }

  public function printHTML(){
    $theHTML      = array();
    $yourAnswers  = (!$this->surveycomplete ? "" : " : Your Answers");
    $theHTML[]    =  "<h2 class='surveyHeader'>".$this->surveyname." $yourAnswers</h2>";
    
    if($this->surveycomplete){
      //IF THE SURVEY HAS ALREADY BEEN COMPLETED JUST DUMP OUT THE ANSWERED BITS ON SCREEN
      $theHTML[]      = "<div class='survey_recap'>";
      foreach($this->raw as $field){
        if(!empty($field["section_header"])){
          $theHTML[]  = "<h4>" . $field["section_header"] ."</h4>";
        }
        if(!empty($field["user_answer"])){
          $action_tags = SELF::getActionTags($field);

          if(array_key_exists("field_type",$action_tags) && $action_tags["field_type"] == "hidden"){
            //do nothing
          }else{
            $item       = SELF::getLabelAnswer($field);
            $field_label= $item["field_label"];
            $field_label= str_replace("\r","",$field_label);
            $field_label= str_replace("\n","<br>",$field_label);
            $theHTML[]  = $item["field_label"] . " : " . $item["user_answer"] . "<br>";
          }
        }
      }
      $theHTML[]      = "</div>";
      $theHTML[]      = "<style>.surveyFrame{ height:auto; }</style>";
    }else{
      $theHTML[]  = "<form class='customform' id='customform' name='".$this->raw[0]["form_name"]."'>";
      
      //CONTAINERS FOR BUILDING FORM COMPONENTS
      $sections       = array();
      $matrixes       = array();
      $branches       = array();
      $first_section  = true;
      
      $verify_map     = array( 
         "email"                => "email"
        ,"integer"              => "number" 
        ,"number"               => "number"
        ,"phone"                => "phone"
        ,"time"                 => "alphaNumeric"
        ,"zipcode"              => "number" 
        ,"date_dmy"             => "date"
        ,"date_mdy"             => "date"
        ,"date_ymd"             => "date"
        ,"datetime_dmy"         => "date"
        ,"datetime_mdy"         => "date"
        ,"datetime_ymd"         => "date"
        ,"datetime_seconds_dmy" => "date"
        ,"datetime_seconds_mdy" => "date"
        ,"datetime_seconds_ymd" => "date"
      );

      $type_arr = array();
      foreach($this->raw as $field){
        // print_rr($this->raw,1);
        $section_html = array();
        $show         = true;
        
        $required_field                 = ($field["required_field"] == "y" ? "required" : "");
        $field_name                     = $field["field_name"];
        $section_header                 = $field["section_header"];
        $field_type                     = $field["field_type"];
        $type_arr[$field_name]          = $field_type ;
        $field_note                     = $field["field_note"];
        $field_label                    = $field["field_label"];
        $field_label                    = str_replace("\r","",$field_label);
        $field_label                    = str_replace("\n","<br>",$field_label);
        $select_choices_or_calculations = $field["select_choices_or_calculations"];
        $branching_logic                = $field["branching_logic"];
        $custom_alignment               = $field["custom_alignment"];
        $matrix_group                   = $field["matrix_group_name"];
        $validation_rules               = (array_key_exists($field["text_validation_type_or_show_slider_number"], $verify_map) ? $verify_map[$field["text_validation_type_or_show_slider_number"]] : "");
        $action_tags                    = SELF::getActionTags($field);
        $field_value                    = null;

        foreach($action_tags as $k => $v){
          $$k = $v;
        }
        if($branching_logic != "") {
          $branches[$field_name]        = $branching_logic;
        }
        
        //SECTION HEADERS CAN BE FORM INPUTS TOO
        //SECTION HEADERS CAN BE DESCRIPTIVES TOO
        if(!empty($section_header)){
          if(!$first_section){
            $section_html[] = "</section>";
          }

          //OPEN UP A SECTION
          $section_html[]   = "<section class='section'>";
          $section_html[]   = "<h2>$section_header</h2>";
          $first_section    = false;
        }

        //DESCRIPTIVE CAN BE SECTION HEADER AS WELL BUT NOT FORM INPUT
        if($field_type == "descriptive"){
          $section_html[]   = "<h3>$field_label</h3>";
        }

        //HIDDEN INPUTS
        if($field_type == "hidden"){
          $altered_input    = SELF::makeHidden($field_name, $field_type, $field_value); 
          $section_html     = array_merge($section_html, $altered_input);
        }

        //LETS JUST PRINT A REGULAR FIELD
        if( $field_type !== "descriptive" && $field_type !== "hidden" ){
          if($matrix_group !== ""){
            $section_html[] = "<div class='table-responsive'>";
            if(!in_array($matrix_group, $matrixes)){
              //THIS GETS ME ALL THE ITEMS WITH THIS MATRIX NAME AND THEIR PRESERVED KEYS 
              //SOME TIMES SOME ITEMS GET INSERTED IN BETWEEN MATRIX ROWS (WHY?!), SO INSTEAD OF FOREACH, DO A FOR FROM START Index TO END Index
              $all_matrix_group = array_filter($this->raw, function($item) use ($matrix_group){
                return $item["matrix_group_name"] == $matrix_group;
              });
              $matrix_range     = array_keys($all_matrix_group);
              $last_matrix      = array_pop($matrix_range);
              $first_matrix     = array_shift($matrix_range);

              $section_html[]   = "<div class='table-responsive'>";
              $section_html[]   = "<table class='table table-striped b-t b-light'>";
              $section_html[]   = "<thead>";
              $section_html[]   = "<th></th>";
              $options  = getAnswerOptions($select_choices_or_calculations);
              foreach($options as $val => $value){
                $section_html[] = "<th class='text-center'>$value</th>";
              } 
              $section_html[]   = "</thead><tbody>";

              for($i = $first_matrix ; $i <= $last_matrix; $i++){
                $item           = $this->raw[$i];
                $field_name     = $item["field_name"];
                $field_type     = $item["field_type"];
                $field_label    = $item["field_label"];
                $field_label    = str_replace("\r","",$field_label);
                $field_label    = str_replace("\n","<br>",$field_label);
                $required_field = ($item["required_field"] == "y" ? "required" : "");

                $section_html[] = "<tr>";
                $section_html[] = "<td>$field_label</td>";
                $options        = SELF::getAnswerOptions($item["select_choices_or_calculations"]);
                foreach($options as $val => $value){
                  if($field_type == "radio"){
                    $checked      = (array_key_exists($field_name,$this->completed) && $completed[$field_name] == $val ? "checked" : "");
                  }else{
                    $altered_name = $field_name . "___" . $val;
                    $checked      = (array_key_exists($altered_name,$this->completed) ? "checked" : "");
                  }
                  $section_html[] = "<td class='text-center'><label><input $required_field type='$field_type' name='$field_name' $checked value='$val'/></label></td>";
                } 
                $section_html[]   = "</tr>";
              }
              $section_html[]     = "</tbody></table>";
              array_push($matrixes,$matrix_group);
            }
          }else{
            $has_branching  = (array_key_exists($field_name,$branches) ? "hasBranching" : "");
            $section_html[] = "<div class='inputwrap $field_name $custom_alignment $has_branching $required_field'>";
            $section_html[] = "<label class='q_label' for='$field_name'>$field_label</label>";

            if($field_type == "dropdown"){
              $dropdown         = SELF::makeDropdown($field_name, $required_field, $select_choices_or_calculations, $field_value); 
              $section_html     = array_merge($section_html, $dropdown);
            }elseif($field_type == "notes"){
              $textarea         = SELF::makeTextarea($field_name, $required_field, $field_value); 
              $section_html     = array_merge($section_html, $textarea);
            }elseif($field_type == "readonly"){
              $altered_input    = SELF::makeReadonly($field_name, $field_type,  $field_value); 
              $section_html     = array_merge($section_html, $altered_input);
            }elseif($field_type == "custom"){
              // $textarea         = SELF::makeReadonly($field_name, $field_value); 
              // $section_html     = array_merge($section_html, $textarea);
            }else{
              if($field_type == "text"){
                $textinput      = SELF::makeTextinput($field_name, $required_field, $validation_rules, $field_type, $field_value); 
                $section_html   = array_merge($section_html, $textinput);
              }else{
                $radioOrCheck   = SELF::makeRadioOrCheck($field_name, $required_field, $select_choices_or_calculations, $field_type, $field_value);
                $section_html   = array_merge($section_html, $radioOrCheck);
              }
            }
          }
          if($field_note !== "") $section_html[] = "<div class='fieldnote'>$field_note </div>";
          $section_html[] = "</div>";
        }

        if($show){
          $theHTML  = array_merge($theHTML,$section_html);
        } 
      }
      $theHTML[]    = "</section></form>";
      $this->fieldtype_map = $type_arr;

      if(count($branches)){
        $theHTML[]            = "<script>";
        $theHTML[]            = "\$(document).ready(function(){";

        foreach($branches as $affected => $effector){
          //NEED TO PARSE THE BRANCHES for $affected
          //THEN ADD EVENTS TO PREVIOUS INPUTS $effectors
          $watchingjs   = self::processBranching($affected,$effector);
          $theHTML[]    = $watchingjs;
        }

        $theHTML[] = "});";
        $theHTML[] = "</script>";
      }
    }

    // DUMP IT OUT HTML
    print_r(implode("\r",$theHTML));
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
                    <script>
                      $(document).ready(function(){
                        
                      });
                    </script>
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
    if(    ($(this).find(":input").is(':text') && $(this).find(":input").val().length == 0)
        || ($(this).find(":input").is('select') && $(this).find(":input").val() == "-")
        || ($(this).find(":input").is(':radio') && $(this).find(":input:checked").length == 0)
      ){
      //ONLY SHOW THE ANNOYING MESSAGE ONCE
      if( !$("#customform section.active").hasClass("annoying_message") ){
        req_missing = true;

        $("#customform section.active").addClass("annoying_message")
        var reqmsg  = $("<div>").addClass("required_message alert alert-danger").html("<ul><li>You have left required fields empty.  If this was intentional please click Submit again.<li></ul>");
        // reqmsg.append($("<button>").addClass("btn btn-alert").text("Close"));
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

  //FOR CHECKBOX TYPES
  if(elem.is(":checkbox")){
    //REDCAP SEES THESE DIFFERENTLY, MUST TEMPORARILY ALTER INPUT ATTRIBUTES TO SUBMIT PROPERLY
    var optioncode  = elem.val();
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

  $.ajax({
    url:  dataURL,
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
  // //PASS FORMS METADATA 
  echo "var form_metadata       = " . json_encode($active_survey->raw) . ";\n";
  echo "var total_questions     = " . $active_survey->surveytotal . ";\n";
  echo "var user_completed      = " . json_encode($active_survey->completed) . ";\n";
  echo "var completed_count     = " . count($active_survey->completed) . ";\n";
  echo "var surveyhash          = '".http_build_query($active_survey->hash)."'";
?>

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
          $(this).next().addClass("active");
          return false;
        }
      }else{
        //SUBMIT AN ALL COMPLETE
        //REDIRECT TO HOME WITH A MESSAGE
        var dataURL         = "survey.php?ajax=1&surveycomplete=1";
        var instrument_name = $("#customform").attr("name");
        $.ajax({
          url:  dataURL,
          type:'POST',
          data: surveyhash,
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

    var pbar              = $(".progress-bar");
    var percent_complete  = Math.round((completed_count/total_questions)*100,2) + "%";
    updateProgressBar(pbar, percent_complete);
    return;
  }); 
});
</script>
