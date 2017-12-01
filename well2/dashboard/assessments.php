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

$core_gender    = $surveys["a_little_bit_about_you"]["raw"][1]["user_answer"];

$shownavsmore   = true;
$survey_active  = '';
$studies_active = '';
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
                      <h2><?php echo lang("MY_ASSESSMENTS");?></h2>
                      <ul class="assessments">
                      <?php 

                      $assesment_links = array();
                      foreach($supp_instruments as $supp_instrument_id => $supp_instrument){
                        //ONLY THE SUPP INSTRUMENTS HAVE CUSTOM FEEDBACK PAGES
                        if(!$supp_instrument["survey_complete"]){
                          //ONLY SHOW LINK IF THEY ARE COMPLETE
                          continue;
                        }
                        $projnotes    = json_decode($supp_instrument["project_notes"],1);
                        $title_trans  = $projnotes["translations"];
                        $tooltips     = $projnotes["tooltips"];
                        $surveyname   = isset($title_trans[$_SESSION["use_lang"]][$supp_instrument_id]) ?  $title_trans[$_SESSION["use_lang"]][$supp_instrument_id] : $supp_instrument["label"];
                        $completed    = json_encode($supp_instrument["completed_fields"]);
                        $assesment_links[] = "<li><a href='#' data-sid='$supp_instrument_id' data-completed='$completed'>".$surveyname."</a></li>\r";
                      }

                      if(!count($assesment_links)){
                        $assesment_links[] = "<li>".lang("NO_ASSESSMENTS")."</li>";
                      }

                      echo implode("",$assesment_links);
                      ?>
                      </ul>
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
  
  var newwin    = window.open("",'targetWindow','toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width='+winwidth+', height=' + winheight + ', top=' + top + ', left=' + left);
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
  $(".assessments a").click(function(){
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
});
</script>
