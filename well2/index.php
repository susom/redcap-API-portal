<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");

$nav    = isset($_REQUEST["nav"]) ? $_REQUEST["nav"] : "home";
$navon  = array("home" => "", "reports" => "", "game" => "");
$navon[$nav] = "on";

$surveyon       = array();
$surveynav      = array_merge(array_splice($available_instruments,0,1), $supp_surveys_keys);
foreach($surveynav as $surveyitem){
    $surveyon[$surveyitem] = "";
}

$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];
$extra_params   = array();
$loc            = !isset($_REQUEST["loc"])  ? 1 : 2; //1 US , 2 Taiwan
$cats           = array(0,1);
foreach($cats as $cat){
    $filterlogic                    = array();
    $filterlogic[]                  = '[well_cms_loc] = "'.$loc.'"';
    $filterlogic[]                  = '[well_cms_catagory] = "'.$cat.'"';
    $filterlogic[]                  = '[well_cms_active] = "1"';
    $extra_params["filterLogic"]    = implode(" and ", $filterlogic);
    $events                         = RC::callApi($extra_params, true, $API_URL, $API_TOKEN); 
    if($cat == 0){
        //is events
        $cats[0] = array();
        foreach($events as $event){
            $recordid   = $event["id"];
            $eventpic   = "";
            $file_curl  = RC::callFileApi($recordid, "well_cms_pic", null, $API_URL,$API_TOKEN);
            if(strpos($file_curl["headers"]["content-type"][0],"image") > -1){
              $split    = explode("; ",$file_curl["headers"]["content-type"][0]);
              $mime     = $split[0];
              $split2   = explode('"',$split[1]);
              $imgname  = $split2[1];
              $eventpic = '<img class="event_img" src="data:'.$mime.';base64,' . base64_encode($file_curl["file_body"]) . '">';
            }

            $order = intval($event["well_cms_displayord"]) - 1;
            if($order == 0 && $core_surveys_complete){
                //first event is only for core survey incomplete people
                continue;
            }
            $cats[0][$order] = array(
                 "subject"  => $event["well_cms_subject"] 
                ,"content"  => $event["well_cms_content"] 
                ,"pic"      => $eventpic
                ,"link"     => $event["well_cms_event_link"] 
            );
        }
        ksort($cats[0]);
    }else{
        $recordid   = $events[0]["id"];
        $eventpic   = "";
        $file_curl  = RC::callFileApi($recordid, "well_cms_pic", null, $API_URL,$API_TOKEN);
        if(strpos($file_curl["headers"]["content-type"][0],"image") > -1){
          $split    = explode("; ",$file_curl["headers"]["content-type"][0]);
          $mime     = $split[0];
          $split2   = explode('"',$split[1]);
          $imgname  = $split2[1];
          $eventpic = "data:".$mime.";base64,". base64_encode($file_curl["file_body"]);
        }
        $cats[1] = array(
             "subject"  => $events[0]["well_cms_subject"] 
            ,"content"  => $events[0]["well_cms_content"] 
            ,"pic"      => $eventpic 
        );
    }
}

//NEEDS TO GO BELOW SHORTSCALE WORK FOR NOW
if(isset($_GET["survey_complete"])){
  //IF NO URL PASSED IN THEN REDIRECT BACK
  $surveyid = $_GET["survey_complete"];
  if(array_key_exists($surveyid,$surveys)){
    $index  = array_search($surveyid, $all_survey_keys);
    $survey = $surveys[$surveyid];

    if(!isset($all_survey_keys[$index+1])){ 
      if(strpos($user_event_arm,"enrollment") > -1){

        // require_once('../FPDI-2.0.1/fpdf181/fpdf.php');
        // require_once('../FPDI-2.0.1/src/autoload.php');
        
        // // initiate FPDI
        // $pdf = new Fpdi();
        // // add a page
        // $pdf->AddPage();
        // // set the source file
        // $pdf->setSourceFile('cert_of_completion.pdf');
        // // import page 1
        // $tplIdx = $pdf->importPage(1);
        // // // use the imported page and place it at position 10,10 with a width of 100 mm
        // $pdf->useTemplate($tplIdx, 10, 10, 100);

        // // // now write some text above the imported page
        // $pdf->SetFont('Helvetica');
        // $pdf->SetTextColor(255, 0, 0);
        // $pdf->SetXY(30, 30);
        // $pdf->Write(0, 'This is just a simple text');

        // $pdf->Output();


        $success_msg    = $lang["CONGRATS_FRUITS"] . " <iframe width='100%' height='315' src='https://www.youtube.com/embed/NBDj5WJpSLM' frameborder='0' allowfullscreen></iframe>";
      }else{
        $arm_year       = substr($loggedInUser->consent_ts,0,strpos($loggedInUser->consent_ts,"-"));
        $arm_year       = $arm_year + count($short_scores) - 1;
        $for_popup      = array_slice($short_scores, -1);

        //THIS SHOULD BE THE MOST RECENT ONE
        $new_well_score = round((array_sum($for_popup[$user_event_arm])/50)*100);
        $scale          = 2*array_sum($for_popup[$user_event_arm])+100;
        $extracss       = "width: ".$scale."px; height: ".$scale."px";
        $success_msg    = "Thank you for completing this year's WELL surveys. <br> Your WELL being Score for $arm_year is: <ul class='eclipse_well_score'><li class='eclipse' style='$extracss' data-size='$new_well_score'><div><b></b><i>$new_well_score<em>%</em></i></div></li></ul>";
      }

      addSessionMessage( $success_msg , "success");
    }
  }
}

$pageTitle = "Well v2 Home Page";
$bodyClass = "home";
include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
            <article>
                <h3>How can I enhance my wellbeing?</h3>
                <?php  
                if(isset($cats[0])){
                    foreach($cats[0] as $event){
                ?>
                    <section>
                        <figure>
                            <?php echo $event["pic"] ?>
                            <figcaption>
                                <h2><?php echo $event["subject"] ?></h2>
                                <p><?php echo $event["content"] ?></p>
                                <a href="<?php echo $event["link"] ?>">Read More</a>
                            </figcaption>
                        </figure>
                    </section>
                <?php 
                    }
                }
                ?>
            </article>

            <?php 
            include_once("models/inc/gl_surveynav.php");
            ?>
        </div> <!-- #main -->
    </div> <!-- #main-container -->
<?php 
include_once("models/inc/gl_foot.php");
?>
