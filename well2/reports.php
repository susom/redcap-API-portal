<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");
//include("models/funcs.general.php");
//GET ALL DATA FOR SUPP INSTRUMENTS IN ALL AVAILABLE ARMS
$extra_params = array(
  'content'   => 'event',
);
$result = RC::callApi($extra_params, true, REDCAP_API_URL, REDCAP_API_TOKEN);
$events = array();
foreach($result as $event){
  if($event["unique_event_name"] == $loggedInUser->user_event_arm){
    //ALREADY HAVE THIS YEAR SO DONT WASTE RESOURCE GETTING IT
    $events[$event["unique_event_name"]] = $supp_instruments;
    break;
  }else{
    //WILL NEED TO GET RESULTS FROM PREVIOUS YEARS TO SHOW RESULTS/FEEDBACK
    $r_supplementalProject    = new Project($loggedInUser, "Supp", SurveysConfig::$projects["Supp"]["URL"], SurveysConfig::$projects["Supp"]["TOKEN"], $event["unique_event_name"]);
    $r_suppsurveys            = $r_supplementalProject->getActiveAll(); 
    $r_supp_surveys["Supp"]   = $r_supplementalProject;
  
    $r_supp_instruments       = array();
    foreach($r_supp_surveys as $projname => $supp_project){
      $r_supp_instruments   = array_merge( $r_supp_instruments, $supp_project->getActiveAll() );
    } 
    $events[$event["unique_event_name"]] = $r_supp_instruments;
  }
}
$supp_surveys_keys = array();
foreach($events as $arm){
  $supp_surveys_keys = array_merge($supp_surveys_keys, array_keys($arm));
};

//IF CORE SURVEY GET THE SURVEY ID
$navon          = array("home" => "", "reports" => "on", "game" => "");
$sid            = $current_surveyid = isset($_REQUEST["sid"]) ? $_REQUEST["sid"] : "";
$sid            = empty($sid) ? "wellbeing_questions" : $sid;
$surveyon       = array();
$surveynav      = array_merge(array_splice($available_instruments,0,1) , $supp_surveys_keys);
foreach($surveynav as $surveyitem){
    $surveyon[$surveyitem] = "";
}
if(!empty($sid)){
    if(!array_key_exists($sid,$surveyon)){
        $surveyon["brief_well_for_life_scale"] = "on";
        $surveyon["wellbeing_questions"] = "on";
    }else{
        $surveyon[$sid] = "on";   
    }
}

$core_gender    = $loggedInUser->gender == 5 || $loggedInUser->gender == 3 ? "m" : "f";

$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];

$pageTitle = "Well v2 Assessments";
$bodyClass = "reports";
include_once("models/inc/gl_head.php");
?>
    
    <div class="main-container">
        <div class="main wrapper clearfix">
            
            <aside>
                <h3>My Asessments</h3>
                <ul class="nav">
                    <li class="surveys">
                        <h4>Completed Surveys</h4>
                        <ol>
                            <?php
                            $suppsurvs        = array();
                            $firstyear        = $first_year;

                            // TODO WELLBEING SCORE (RADAR CHART)
                            $survey_alinks["wellbeing_questions"] = "<a class='assessments' href='reports.php?sid=wellbeing_questions'>Stanford WELL for Life Scale</a>";
                            $default_surveynav = isset($surveyon["wellbeing_questions"]) ? $surveyon["wellbeing_questions"] : $surveyon["brief_well_for_life_scale"];
                            $suppsurvs["wellbeing_questions"]     = "<li class='assesments fruits $default_surveynav'>
                                                ".$survey_alinks["wellbeing_questions"]." 
                                            </li>";

                            $viewlink = array();
                            $armnames = array_keys($events);
                            $armyears = array();

                            foreach($armnames as $armname){
                              $armyears[$armname] = $first_year;
                              $first_year++;
                            }

                            foreach($events as $armname => $supp_instruments_event){
                              $fitness  = SurveysConfig::$fitness;
                              $index    = -1;

                              foreach($supp_instruments_event as $supp_instrument_id => $supp_instrument){
                                $index++;
                                
                                // only want to show link if there are results available in any year
                                if(!$supp_instrument["survey_complete"]){
                                  continue;
                                }

                                //if bucket is A make sure that three other ones are complete before showing.
                                $projnotes    = json_decode($supp_instrument["project_notes"],1);
                                $title_trans  = $projnotes["translations"];
                                $tooltips     = $projnotes["tooltips"];
                                $surveyname   = isset($title_trans[$_SESSION["use_lang"]][$supp_instrument_id]) ?  $title_trans[$_SESSION["use_lang"]][$supp_instrument_id] : $supp_instrument["label"];
                                $iconcss      = $fitness[$index];
                                $titletext    = $tooltips[$supp_instrument_id];
                                $surveylink   = "?sid=". $supp_instrument_id ;
                                $icon_update  = " icon_update";
                                $completed    = json_encode($supp_instrument["completed_fields"]);
                                
                                $survey_alinks[$supp_instrument_id] = "<a href='$surveylink' title='$titletext' data-sid='$supp_instrument_id' data-completed='$completed'>$surveyname</a>";
                                $assessmentsclass = $supp_instrument_id !== "international_physical_activity_questionnaire" ? "assessments" :"";
                                $surveyonclass = isset($surveyon[$supp_instrument_id]) ? $surveyon[$supp_instrument_id] : "";
                                
                                $list         = "<li class='$assessmentsclass fitness  $icon_update $iconcss  $surveyonclass'>
                                                    ".$survey_alinks[$supp_instrument_id]." 
                                                </li>";
                                if(!array_key_exists($supp_instrument_id,$suppsurvs)){
                                  $suppsurvs[$supp_instrument_id]  = $list;
                                }

                                if($sid == $supp_instrument_id){
                                  $year = $armyears[$armname];
                                  $viewlink[] = "<a class='viewassessment' href='$surveylink' title='$titletext' data-sid='$supp_instrument_id' data-completed='$completed' target='theFrame'>$year $surveyname</a>";
                                }
                              }
                            }

                            if(count($suppsurvs)){
                                echo implode("",$suppsurvs);
                            }else{
                                echo "<i>None Available</i>";
                            }
                            ?>
                        </ol>

                        <h4>WEll Certificates</h4>
                        <ol>
                          <?php
                            $filename         = array();
                            $filename[]       = $loggedInUser->id;
                            $filename[]       = $loggedInUser->firstname;
                            $filename[]       = $loggedInUser->lastname;
                            $user_folder      = implode("_",$filename);

                            // var comes from surveys.php
                            while($firstyear <= $this_year){
                              $curyear        = $firstyear;
                              $file_cert      = "../PDF/certs/$user_folder/" . $user_folder . "_$curyear.pdf";
                              if(file_exists($file_cert)){
                                echo "<li class='nofruit'><a class='certcomplete' target='blank' href='$file_cert'>$curyear</a></li>";
                              }
                              $firstyear++;
                            }
                          ?>
                        </ol>
                    </li>
                </ul>
            </aside>
            <article>
                <script src="assets/js/custom_assessments.js"></script>
                <div id="results" class="assessments">
                



                <?php 
                $API_TOKEN    = SurveysConfig::$projects["Supp"]["TOKEN"];
                $API_URL      = SurveysConfig::$projects["Supp"]["URL"];
                if(count($suppsurvs)){
                    if(!empty($sid)){
                        echo "<div id='results'>";
                        switch($sid){
                            case "wellbeing_questions":
                              $extra_params = array(
                                'content'     => 'record',
                                'records'     => array($loggedInUser->id) ,
                                'fields'      => array("id","well_long_score"),
                              );
                              $user_ws      = RC::callApi($extra_params, true, $_CFG->REDCAP_API_URL, $_CFG->REDCAP_API_TOKEN); 
                              $long_scores  = json_decode($user_ws[0]["well_long_score"],1);
                              //createResultsFile(); put the following code within funcs general and include later
                              if(file_exists("../Results.csv")){
                                  file_put_contents("../Results.csv","");
                                  $file = "../Results.csv";
                                  $current = file_get_contents($file);
                                  $current .= "group, axis, value, description\n";
                                  foreach ($long_scores as $key => $value) 
                                    $current .= "User, ".$key.", ". $value.",\n";
                                  file_put_contents($file,$current);
                              }else
                                  print_rr("file doesnt exist");
                              ?>
                                <object type = "text/html" data = "../index.html" width = 100%></object>

                              <?php
                            break;

                            case "international_physical_activity_questionnaire":
                                $extra_params = array(
                                  'content'     => 'record',
                                  'records'     => [$loggedInUser->id] ,
                                  'fields'      => ["ipaq_total_overall"],
                                  'events'      => array(REDCAP_PORTAL_EVENT)
                                );
                                $result         = RC::callApi($extra_params, true, $API_URL, $API_TOKEN); 
                                $ipaq           = isset($result[0]["ipaq_total_overall"]) ? $result[0]["ipaq_total_overall"] : "N/A";
                                ?>
                                <div id="ipaq_results">
                                    <h3>Your physical activity MET-minutes/week score is: <b><?php echo $ipaq ?></b></h3>
                                </div>
                                <?php
                            break;

                            default:
                                foreach($viewlink as $link){
                                  echo "<p>$link</p>";
                                }
                            break;

                        }
                        echo "</div>";
                    }else{
                        echo "<p><i>Click on a survey link to view assessment.</i></p>";
                    }
                }else{
                    echo "<p><i>".lang("NO_ASSESSMENTS")."</i></p>";
                }
                ?>
                <iframe  width="700" height="800" name="theFrame" style="border:none"></iframe>

                </div> <!-- results -->
            </article>
        </div> <!-- #main -->
    </div> <!-- #main-container -->
<?php 
include_once("models/inc/gl_foot.php");
?>
<style>
.main > aside li ol {
  margin-bottom:50px;
}
.main > aside a.certcomplete {
    display:inline-block;
    vertical-align: bottom;
    padding-left:59px;
    height:50px;
    background:url(../PDF/ico_cert_completion.png) 0 0 no-repeat;
    background-size:49px 35px;
    line-height:200%;
}
.main > aside li li.nofruit{
  padding-left:10px;
}
.main > aside li li.nofruit:hover{
  background:none;
}
.main > aside li li.nofruit:before {
  display:none;
}

#ipaq_results{
    background:#f2f2f2;
    border-radius:10px;
    padding:20px;
    box-shadow: 2px 2px 5px #999;
}
#ipaq_results h3{
    color:#000;
}
.viewassessment {
  opacity:0;
  position:absolute;
  left:-5000px;
}
</style>
<script src="js/custom_assessments.js"></script>
<script>
<?php 
echo "var uselang   = '" . $_SESSION["use_lang"] . "';\n";
echo "var poptitle  = '".lang("YOUR_ASSESSMENT")."';\n";
?>
function centeredNewWindow(title,insertHTML,styles,scrips,bottom_scrips){
  var winwidth        = Math.round(.85 * $( window ).width() );
  var winheight       = Math.round(.85 * $( window ).height() );
  var dualScreenLeft  = window.screenLeft != undefined ? window.screenLeft : screen.left;
  var dualScreenTop   = window.screenTop != undefined ? window.screenTop : screen.top;
  var width           = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
  var height          = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
  var left            = ((width / 2) - (winwidth / 2)) + dualScreenLeft;
  var top             = ((height / 2) - (winheight / 2)) + dualScreenTop;
  
  var newwin          = window.open("",'theFrame','toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width='+winwidth+', height=' + winheight + ', top=' + top + ', left=' + left);
  newwin.document.write('<html><head>');
  newwin.document.write('<title>'+title+'</title>');
  for(var i in styles){
    var linkhref = styles[i].href;
    if(linkhref){
      newwin.document.write('<link rel="stylesheet" type="text/css" href="'+linkhref+'">');
    }
  }
  newwin.document.write('<style>#content { margin:20px; }</style>');
  newwin.document.write('</head><body>');
  newwin.document.write('<div id="content" style="color:#222">');
  newwin.document.write('<h2 style="margin:0 0 40px; padding-bottom:10px; color:#333; border-bottom:1px solid #999">'+poptitle+' : "'+title+'"</h2>');
  newwin.document.write(insertHTML);
  newwin.document.write('</div>');
  for(var i in scrips){
    var scriptsrc = scrips[i].src;
    var newscript = document.createElement('script');
    newscript.src = scriptsrc;
    if(scriptsrc){
      newwin.document.body.appendChild(newscript);
    }
  }
  newwin.document.write(bottom_scrips);
  newwin.document.write('</body></html>');
  return;
}

$(document).ready(function(){
  $(".viewassessment").click(function(){
    var sid       = $(this).data("sid");
    var udata     = $(this).data("completed");
    var title     = $(this).text();

    //get proper styles/ sripts and div containers for the pop up page
    var sheets    = document.styleSheets;
    var scrips    = document.scripts;
    var bottom_scrips = "";

    switch(sid){
      case "find_out_your_body_type_according_to_chinese_medic":
        showTCMScoring(udata, function(resultData){
          bottom_scrips += "<script>";
          bottom_scrips += "setTimeout(function(){";
          bottom_scrips += "$('.constitution dt').click(function(){";
          bottom_scrips += "if($(this).next('dd').is(':visible')){";
          bottom_scrips += "$(this).next('dd').slideUp();";
          bottom_scrips += "}else{";
          bottom_scrips += "$(this).next('dd').slideDown();";
          bottom_scrips += "}";
          bottom_scrips += "return false;";
          bottom_scrips += "});";
          bottom_scrips += "},1500);";
          bottom_scrips += "<\/script>";

          centeredNewWindow(title,resultData,sheets,scrips,bottom_scrips);
        });
      break;

      case "how_well_do_you_sleep":
        showSleepScoring(udata,function(resultData){
          bottom_scrips += "<script>";
          bottom_scrips += "setTimeout(function(){";
          bottom_scrips += "  $('#psqi_slider').roundSlider({";
          bottom_scrips += "    sliderType: 'min-range',";
          bottom_scrips += "    handleShape: 'square',";
          bottom_scrips += "    circleShape: 'half-top',";
          bottom_scrips += "    showTooltip: false,";
          bottom_scrips += "    handleSize: 0,";
          bottom_scrips += "    radius: 120,";
          bottom_scrips += "    width: 14,";
          bottom_scrips += "    min: 0,";
          bottom_scrips += "    max: 21,";
          bottom_scrips += "    value: 10";
          bottom_scrips += "  });";
          bottom_scrips += "},2000);";
          bottom_scrips += "<\/script>";

          centeredNewWindow(title,resultData,sheets,scrips,bottom_scrips);
        });
      break;

      case "how_fit_are_you":
        showMETScoring(udata,function(resultData){
          bottom_scrips += "<style>";
          bottom_scrips += "#met_results {opacity:1}";
          bottom_scrips += "<\/style>";

          bottom_scrips += "<script>";
          bottom_scrips += "$('a.moreinfo').click(function(){";
          bottom_scrips += "  var content   = $(this).parent().next();";
          bottom_scrips += "  content.slideDown('medium');";
          bottom_scrips += "});";
          bottom_scrips += "$('a.closeparent').click(function(){";
          bottom_scrips += "  $(this).parent().slideUp('fast');";
          bottom_scrips += "});";
          bottom_scrips += "<\/script>";
           
          centeredNewWindow(title,resultData,sheets,scrips,bottom_scrips);
        });
      break;

      case "how_resilient_are_you_to_stress":
        udata["core_gender"] = '<?php echo $core_gender ?>';
        showGRITScoring(udata,function(resultData){
          bottom_scrips += "<script>";
          bottom_scrips += "setTimeout(function(){";
          bottom_scrips += "var _this = $('#grit_results');";
          bottom_scrips += "customGRIT_BS(_this);";
          bottom_scrips += "},1500);";
          bottom_scrips += "<\/script>";
          centeredNewWindow(title,resultData,sheets,scrips,bottom_scrips);
        });
      break;

      case "how_physically_mobile_are_you":
        showMATScoring(udata,function(resultData){
          var mat_score_desc = {
             40  : '<?php echo lang("MAT_SCORE_40") ?>'
            ,50  : '<?php echo lang("MAT_SCORE_50") ?>'
            ,60  : '<?php echo lang("MAT_SCORE_60") ?>'
            ,70  : '<?php echo lang("MAT_SCORE_70") ?>'
          };

          var matscore  = resultData.value;
          if(matscore < 40){
              var picperc = 7;
              var desc = mat_score_desc[40];
          }else if(matscore < 50){
              var picperc = 5;
              var desc = mat_score_desc[50];
          }else if(matscore < 60){
              var picperc = 3;
              var desc = mat_score_desc[60];
          }else{
              var picperc = 0;
              var desc = mat_score_desc[70];
          }

          var makeHTML   = "<div id='mat_results'>";
          makeHTML      += "  <div id='matscore'></div>";
          makeHTML      += "  <div id='mat_pic'>";
          makeHTML      += "    <ul>";
          for(var i = 0;i < 10; i++){
            if(i < picperc){
              makeHTML      += "      <li class='dead'></li>";
            }else{
              makeHTML      += "      <li></li>";
            }
          }
          makeHTML      += "    </ul>";
          makeHTML      += "  </div>";
          makeHTML      += "  <div id='mat_text'>"+desc+"</div>";
          makeHTML      += "</div>";

          bottom_scrips += "<script>";
          bottom_scrips += "setTimeout(function(){";
          bottom_scrips += "var _this = $('#mat_results');";
          bottom_scrips += "customMAT_BS(_this);";
          bottom_scrips += "},1500);";
          bottom_scrips += "<\/script>";
          centeredNewWindow(title,makeHTML,sheets,scrips,bottom_scrips);
        });
      break;
    }
    return false;
  });


 $(".viewassessment").trigger("click");
});
</script>