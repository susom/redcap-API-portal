<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");

$radar_domains = array(
  "0" => "Exploration and Creativity",
  "1" => "Lifestyle Behaviors",
  "2" => "Social Connectedness",
  "3" => "Stress and Resilience",
  "4" => "Experience of Emotions",
  "5" => "Sense of Self",
  "6" => "Physical Health",
  "7" => "Purpose and Meaning",
  "8" => "Financial Security",
  "9" => "Spirituality and Religion"
);

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
  $filterlogic[]                  = '[well_cms_catagory] = "'."2".'"';
  $filterlogic[]                  = '[well_cms_active] = "1"';
  $extra_params["filterLogic"]    = implode(" and ", $filterlogic);
  $events                         = RC::callApi($extra_params, true, $API_URL, $API_TOKEN); 
      //is resources
      $cats[2] = array();

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
            $order = intval($event["well_cms_displayord"]);

        
          $cats[2][$order] = array(
              "pic"      => $eventpic
              ,"link"     => $event["well_cms_event_link"] 
              ,"domain"   => $event["well_cms_domain"]
          );
      }
      ksort($cats[2]);

// if( then render)
// $default = "?domain=creativity" 
$url = $_SERVER['REQUEST_URI'];
$domain_page = $url[strlen($url)-1];


include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
          <div class = domains>
                <?php  
                if(!is_numeric($domain_page)){
                    foreach($radar_domains as $key=>$val){
                  ?>
                    <section>
                        <figure>
                            <img src = assets/img/0<?php echo $key;?>-domain.jpg> 
                        </figure>
                        <a href = "resources.php?nav=resources-<?php echo $key; ?>" style = "font-size: 13px;"> 
                          <?php echo $radar_domains[$key]; ?>
                        </a>
                    </section>
                <?php 
                    }//foreach
                }//if isset
                else{
                  print_rr("Implement second page here");
                  switch($domain_page){
                    case 0:
                      ?> <li>nice</li> <?php
                      break;
                    case 1:
                      break;
                    case 2:
                      break;
                    case 3:
                      break;
                    case 4:
                      break;
                    case 5:
                      break;
                    case 6:
                      break;
                    case 7:
                      break;
                    case 8:
                      break;
                    case 9:
                      break;
                    default:

                  }
                }
                ?>
          

            <?php 
           // include_once("models/inc/gl_surveynav.php");
            ?>
          </div>
        </div> <!-- #main -->
    </div> <!-- #main-container -->
<?php 
include_once("models/inc/gl_foot.php");
?>

<style>
li{
  display: block;
  font-size: 12px;
}
section{
  width:200px;
  height: 100px;
  display: inline-block;
}
img{
  max-width: 126px;
  max-height: 168px;
}
</style>
