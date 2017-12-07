<aside>
    <h3>MyWELL</h3>
    <ul>
        <li class="surveys">
            <a href="#">Surveys</a>
            <ol>
                <?php
                $new = null;
                $core_surveys           = array();
                $supp_surveys           = array();

                foreach($surveys as $surveyid => $survey){
                  if($core_surveys_complete){
                    break;
                  }
                  $surveycomplete = $survey["survey_complete"];
                  $index          = array_search($surveyid, $all_survey_keys);
                  $projnotes      = json_decode($survey["project_notes"],1);
                  $title_trans    = $projnotes["translations"];
                  $surveylink     = "survey.php?sid=" . $surveyid;
                  $surveyname     = isset($title_trans[$_SESSION["use_lang"]][$surveyid]) ?  $title_trans[$_SESSION["use_lang"]][$surveyid] : $survey["label"];
                  $completeclass  = ($surveycomplete ? "completed":"");
                  $hreflink       = (is_null($new) || $surveycomplete ? "href" : "rel");
                  $newbadge       = (is_null($new) && !$surveycomplete ? "<b class='badge bg-danger pull-right'>new!</b>" :null);
                  if(!$surveycomplete && is_null($new)){
                    $new = $index;
                    $next_survey =  $surveylink;
                  }

                  if(in_array($surveyid, $available_instruments)){
                    array_push($core_surveys, "<li class='".$surveyon[$surveyid]."'>
                        <a $hreflink='$surveylink' class='auto' title='".$survey["label"]."'>
                          $newbadge                                                   
                          <span class='fruit $completeclass'></span>
                          <span class='survey_name'>$surveyname</span>     
                        </a>
                      </li>\n");
                  }
                  break;
                }
                echo implode("",$core_surveys);
  
                $index  = 0;
                $fruits = array("strawberry","grapes","apple","orange","cherry","blueberry","bananas","longans","pineapple");
                foreach($supp_instruments as $supp_instrument_id => $supp_instrument){
                    $index++;
                    if($supp_instrument["survey_complete"]){
                      continue;
                    }

                    //if bucket is A make sure that three other ones are complete before showing.
                    $projnotes    = json_decode($supp_instrument["project_notes"],1);
                    $title_trans  = $projnotes["translations"];
                    $tooltips     = $projnotes["tooltips"];
                    $surveyname   = isset($title_trans[$_SESSION["use_lang"]][$supp_instrument_id]) ?  $title_trans[$_SESSION["use_lang"]][$supp_instrument_id] : $supp_instrument["label"];
                    $fruitcss     = $fruits[$index];
                    
                    $titletext    = $core_surveys_complete ? $tooltips[$supp_instrument_id] : $lang["COMPLETE_CORE_FIRST"];
                    $surveylink   = $core_surveys_complete ? "survey.php?sid=". $supp_instrument_id. "&project=" . $supp_instrument["project"] : "#";
                    $icon_update  = " icon_update";
                    $survey_alinks[$supp_instrument_id] = "<a href='$surveylink' title='$titletext'>$surveyname</a>";
                
                    $news[]       = "<li class='list-group-item $icon_update $fruitcss  ".$surveyon[$supp_instrument_id]."'>
                                        ".$survey_alinks[$supp_instrument_id]." 
                                    </li>";
                  }
                echo implode("",$news);
                ?>  
            </ol>
        </li>
    </ul>
</aside>