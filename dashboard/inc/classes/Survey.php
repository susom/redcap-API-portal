<?php
//LOAD UP THE SURVEY HERE AND PRINT OUT THE HTML
class Survey {
  PUBLIC $surveyname;
  PUBLIC $surveytotal;
  PUBLIC $completed;
  PUBLIC $surveycomplete;
  PUBLIC $surveypercent;
  PUBLIC $raw;
  PUBLIC $hash;
  PUBLIC $project;
  PRIVATE $fieldtype_map;

//name, project(connection stuff), 
  public function __construct( $survey_data ){
    $this->surveyname     = $survey_data["label"];
    $this->surveytotal    = $survey_data["total_questions"];
    $this->completed      = $survey_data["completed_fields"];
    $this->surveycomplete = $survey_data["survey_complete"];
    $this->surveypercent  = 0;
    $this->raw            = $survey_data["raw"];
    $this->project        = $survey_data["project"];
    $hash                 = explode("s=", $survey_data["survey_link"]);
    $this->hash           = array("hash" => $hash[1]);
  }

  private function processGameBranching($target, $branch_logic){
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
        $field_type = ($field_type == "yesno" || $field_type == "truefalse" ? "radio" : $field_type);
        $radioCheck = ($field_type == "radio" || $field_type == "checkbox" ? true: false );
        $formtype   = ($radioCheck ? "\$(\":input[name='$effector_input']:checked\").val() $effector_operator '$effector_value'" : "\$(\":input[name='$effector_input']\").val() $effector_operator '$effector_value'");

        $condition[] = $formtype;
      }

      $jsaction   = "";
      $jsaction .= "\tif(". implode(" || ", $condition) ."){\n";
      $jsaction .= "\t\t\$('dl.".$affected."').addClass('showBranch');\n";
      $jsaction .= "\t}else{\n";
      $jsaction .= "\t\t\$('dl.".$affected."').removeClass('showBranch');\n";
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
      $field_type = ($field_type == "yesno" || $field_type == "truefalse" ? "radio" : $field_type);
      $radioCheck = ( $field_type == "radio" || $field_type == "checkbox" ? true: false );
      $formtype   = ($radioCheck ? "\$(\":input[name='$effector_input']:checked\").val() == '$effector_value'" : "\$(\":input[name='$effector_input']\").val() =='$effector_value'");
      
      $jsaction   = "";
      $jsaction .= "\tif($formtype){\n";
      $jsaction .= "\t\t\$('dl.".$affected."').addClass('showBranch');\n";
      $jsaction .= "\t}else{\n";
      $jsaction .= "\t\t\$('dl.".$affected."').removeClass('showBranch');\n";
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
      $field_type = ($field_type == "yesno" || $field_type == "truefalse" ? "radio" : $field_type);
      $radioCheck = ( $field_type == "radio" || $field_type == "checkbox" ? true: false );
      $formtype   = ($radioCheck ? "\$(\":input[name='$effector_input']:checked\").val() == '$effector_value'" : "\$(\":input[name='$effector_input']\").val() =='$effector_value'");

      $jsaction   = "";
      $jsaction .= "\tif($formtype){\n";
      $jsaction .= "\t\t\$('dl.".$affected."').addClass('showBranch');\n";
      $jsaction .= "\t}else{\n";
      $jsaction .= "\t\t\$('dl.".$affected."').removeClass('showBranch');\n";
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
          $temp         = explode(", ",$pa);
          $answervalue  = array_shift($temp);
          if($user_answer == $answervalue){
            $user_answer = implode(",",$temp);
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
        $key  = array_shift($temp);
        $select_choices[trim($key)] = implode(",",$temp);
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

      if(strpos($tag,"@TODAY") > -1){
        $actions["field_value"] = Date("Y-m-d");
        continue;
      }else if(strpos($tag,"@NOW") > -1){
        $actions["field_value"] = Date("Y-m-d H:i:s");
        continue;
      }

      if(strpos($tag,"@inputmask") > -1){
        $actions["placeholder"] = $v["placeholder"];
        $actions["mask"]        = $v["mask"];
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
    $theHTML[]    =  "<h2 class='surveyHeader'>".$this->surveyname."</h2>";
    
    if($this->surveycomplete){
      //IF THE SURVEY HAS ALREADY BEEN COMPLETED JUST DUMP OUT THE ANSWERED BITS ON SCREEN
      $theHTML[]      = "<div class='survey_recap'>";
      foreach($this->raw as $field){
        if(!empty($field["section_header"])){
          $theHTML[]  = "<h4>" . str_replace("\n","<br>",$field["section_header"]) ."</h4>";
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
      $theHTML[]  = "<form class='customform' id='customform' name='".$this->raw[0]["form_name"]."' data-project='".$this->project."'>";
      
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

      $mask_js  = array();
      $type_arr = array();

      // print_rr($this->raw,1);
      foreach($this->raw as $field){
        // print_rr($this->raw,1);
        $section_html = array();
        $show         = true;
        $mask         = null;
        $placeholder  = null;

        $required_field                 = ($field["required_field"] == "y" ? "required" : "");
        $field_name                     = $field["field_name"];
        $section_header                 = $field["section_header"];
        $section_header                 = str_replace("\r","",$section_header);
        $section_header                 = str_replace("\n","<br>",$section_header);
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

        if(isset($mask)){  
          $mask_js[]    = "$(\"#$field_name\").attr(\"placeholder\",\"$placeholder\");\n";
          $mask_js[]    = "$(\"#$field_name\").mask(\"$mask\",{placeholder:\"$placeholder\"});\n";
        }

        if($branching_logic != "") {
          // print_rr($branching_logic . " - " . $field_name);
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
          $has_branching  = (array_key_exists($field_name,$branches) ? "hasBranching" : "");
          $section_html[] = "<div class='$field_name $has_branching'>";
          $section_html[] = "<h3>$field_label</h3>";
          $section_html[] = "</div>";
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
            $section_html[] = "<label class='q_label' for='$field_name'>$field_label<i></i></label>";

            if($field_type == "dropdown"){
              $dropdown         = SELF::makeDropdown($field_name, $required_field, $select_choices_or_calculations, $field_value); 
              $section_html     = array_merge($section_html, $dropdown);
            }elseif($field_type == "yesno" || $field_type == "truefalse"){
              $select_choices_or_calculations = ($field_type == "yesno" ? "1, Yes | 0, No": "1, True | 0, False");
              $radioOrCheck     = SELF::makeRadioOrCheck($field_name, $required_field, $select_choices_or_calculations, "radio", $field_value);
              $section_html     = array_merge($section_html, $radioOrCheck);
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
      
      $theHTML[]  = "<script>";
      $theHTML[]  = "\$(document).ready(function(){";
      $theHTML    = array_merge($theHTML,$mask_js);
      $theHTML[]  = "});";
      $theHTML[]  = "</script>";
    }

    // DUMP IT OUT HTML
    print_r(implode("\r",$theHTML));
  }

  public function printGameHTML(){
    $theHTML      = array();
    $theHTML[]    =  "<form id='survey_questions'>";
    
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

    $mask_js  = array();
    $type_arr = array();

    // print_rr($this->raw,1);
    foreach($this->raw as $field){
      // print_rr($this->raw,1);
      $section_html = array();
      $show         = true;
      $mask         = null;
      $placeholder  = null;

      $required_field                 = ($field["required_field"] == "y" ? "required" : "");
      $field_name                     = $field["field_name"];
      $field_type                     = $field["field_type"];
      $type_arr[$field_name]          = $field_type ;
      $field_note                     = $field["field_note"];
      $field_label                    = $field["field_label"];
      $field_label                    = str_replace("\r","",$field_label);
      $field_label                    = str_replace("\n","<br>",$field_label);
      $select_choices_or_calculations = $field["select_choices_or_calculations"];
      $branching_logic                = $field["branching_logic"];
      $validation_rules               = (array_key_exists($field["text_validation_type_or_show_slider_number"], $verify_map) ? $verify_map[$field["text_validation_type_or_show_slider_number"]] : "");
      $action_tags                    = SELF::getActionTags($field);
      $field_value                    = null;

      foreach($action_tags as $k => $v){
        $$k = $v;
      }

      if(isset($mask)){  
        $mask_js[]    = "$(\"#$field_name\").attr(\"placeholder\",\"$placeholder\");\n";
        $mask_js[]    = "$(\"#$field_name\").mask(\"$mask\",{placeholder:\"$placeholder\"});\n";
      }

      if($branching_logic != "") {
        $branches[$field_name] = $branching_logic;
      }

      //HIDDEN INPUTS
      if($field_type == "hidden"){
        $altered_input    = SELF::makeHidden($field_name, $field_type, $field_value); 
        $section_html     = array_merge($section_html, $altered_input);
      }

      //LETS JUST PRINT A REGULAR FIELD
      if( $field_type !== "descriptive" && $field_type !== "hidden" ){
        $has_branching  = (array_key_exists($field_name,$branches) ? "hasBranching" : "");
        $section_html[] = "<dl class='inputwrap $field_name $has_branching $required_field'>";
        $section_html[] = "<dt class='question'><span>Q:</span><label class='qlabel' for='$field_name'>$field_label</label></dt>";

        $section_html[] = "<dd class='answer'><span>A:</span><div class='inputs'>";
        $required_field = "";

        if($field_type == "dropdown"){
          $dropdown         = SELF::makeDropdown($field_name, $required_field, $select_choices_or_calculations, $field_value); 
          $section_html     = array_merge($section_html, $dropdown);
        }elseif($field_type == "yesno" || $field_type == "truefalse"){
          $select_choices_or_calculations = ($field_type == "yesno" ? "1, Yes | 0, No": "1, True | 0, False");
          $radioOrCheck   = SELF::makeRadioOrCheck($field_name, $required_field, $select_choices_or_calculations, "radio", $field_value);
          $section_html   = array_merge($section_html, $radioOrCheck);
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
        
        if($field_note !== "") $section_html[] = "<div class='fieldnote'>$field_note </div>";
        $section_html[] = "</div></dl>";
      }

      if($show){
        $theHTML  = array_merge($theHTML,$section_html);
      } 
    }
    $theHTML[]            = "<div class='submitit'><button class='btn btn-info'>Submit Answer</button></div></form>";
    $this->fieldtype_map  = $type_arr;
    
    $theHTML[]            = "<script>";
    $theHTML[]            = "\$(document).ready(function(){";
    if(count($branches)){
      foreach($branches as $affected => $effector){
        //NEED TO PARSE THE BRANCHES for $affected
        //THEN ADD EVENTS TO PREVIOUS INPUTS $effectors
        $watchingjs = self::processGameBranching($affected,$effector);
        $theHTML[]  = $watchingjs;
      }
    }
    $theHTML    = array_merge($theHTML,$mask_js);

    $theHTML[]  = "});";
    $theHTML[]  = "</script>";


    // DUMP IT OUT HTML
    print_r(implode("\r",$theHTML));
  }
}