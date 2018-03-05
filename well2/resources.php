<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");

//SITE NAV
$navon = array("home" => "", "reports" => "", "game" => "", "articles" => "on");

$avail_surveys      = $available_instruments;
$first_core_survey  = array_splice($avail_surveys,0,1);
$surveyon           = array();
$surveynav          = array_merge($first_core_survey, $supp_surveys_keys);
foreach($surveynav as $surveyitem){
    $surveyon[$surveyitem] = "";
}

$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];
$extra_params   = array();
$loc            = !isset($_REQUEST["loc"])  ? 1 : 2; //1 US , 2 Taiwan
$cats           = array();


  $filterlogic                    = array();
  $filterlogic[]                  = '[well_cms_loc] = "'.$loc.'"';
  $filterlogic[]                  = '[well_cms_catagory] = "'.$cat.'"';
  $filterlogic[]                  = '[well_cms_active] = "1"';
  $extra_params["filterLogic"]    = implode(" and ", $filterlogic);
  $events                         = RC::callApi($extra_params, true, $API_URL, $API_TOKEN); 
  
      //is resources
      $cats[2] = array();
      print_rr($cats);
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

        
          $cats[2][$order] = array(
               "subject"  => $event["well_cms_subject"] 
              ,"content"  => $event["well_cms_content"] 
              ,"pic"      => $eventpic
              ,"link"     => $event["well_cms_text_link"] 
              ,"domain"   => $event["well_cms_domain"]
          );
      }
      ksort($cats[2]);
      print_rr($cats);





include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
            <article>
                <h3>How can I enhance my wellbeing?</h3>
                <?php  
                if(isset($cats[2])){
                    foreach($cats[2] as $event){
                ?>
                    <section>
                        <figure>
                            <?php echo $event["pic"] ?>
                            <figcaption>
                                <h2><?php echo $event["subject"] ?></h2>
                                <p><?php echo $event["content"] ?></p>
                                <?php
                                if(!empty($event["link"])){
                                ?>
                                <a href="<?php echo $event["link"] ?>">Read More</a>
                                <?php
                                }
                                ?>
                            </figcaption>
                        </figure>
                    </section>
                <?php 
                    }
                }
                ?>
            </article>

            <?php 
           // include_once("models/inc/gl_surveynav.php");
            ?>
        </div> <!-- #main -->
    </div> <!-- #main-container -->
<?php 
include_once("models/inc/gl_foot.php");
?>
