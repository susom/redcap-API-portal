<?php 
require_once("../models/config.php"); 

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

            $order = intval($event["well_cms_displayord"]);
            $cats[0][$order] = array(
                 "subject"  => $event["well_cms_subject"] 
                ,"content"  => $event["well_cms_content"] 
                ,"pic"      => $eventpic
                ,"link"     => $event["well_cms_event_link"] 
            );
        }
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

?>
<!doctype html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8">
<meta name="google" content="notranslate">
<meta http-equiv="Content-Language" content="en">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title></title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="css/normalize.min.css">
<link rel="stylesheet" href="css/main.css">

<script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
</head>
<body>
<div id="outter_rim">
<div id="inner_rim">
    <nav>
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">Reports</a></li>
            <li><a href="#">Game</a></li>
            <!-- <li><a href="#">Resources</a></li> -->
        </ul>
    </nav>

    <div class="header-container">
        <header class="wrapper clearfix">
            <h1 class="title">WELL for Life</h1>
            <a id="account_drop" href="#"><span></span> Irvin Szeto <b class="caret"></b></a>
            <ul id="drop_menu">
                <li><a href="#">Logout</a></li>
            </ul>
            <a href="#" class="hamburger"></a>
        </header>
    </div>
    
    <?php  
    if(isset($cats[1])){
    ?>
    <div class="splash-container">
        <div class="wrapper clearfix">
            <h2><?php echo $cats[1]["subject"]?></h2>
            <blockquote>
                <?php echo $cats[1]["content"]?>
            </blockquote>
            <style>
                .splash-container blockquote:before {
                    background: url(<?php echo $cats[1]["pic"] ?>) top left no-repeat;
                    background-size:contain;
                }
            </style>
        </div>
    </div>
    <?php 
    }
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

            <aside>
                <h3>MyWELL</h3>
                <ul>
                    <!-- <li class="notifs"><a href="#">Notifications</a></li>
                    <li class="rewards"><a href="#">Rewards</a></li> -->
                    <li class="surveys">
                        <a href="#">Surveys</a>
                        <ol>
                            <li class="strawberry"><a href="#">2017 Wellbeing</a></li>
                            <li class="grape"><a href="#">Nutrition</a></li>
                            <li class="apple"><a href="#">Fitness</a></li>
                            <li class="orange"><a href="#">Physical Mobility</a></li>
                            <li class="cherry"><a href="#">Resilience</a></li>
                            <li class="blueberry"><a href="#">TCM</a></li>
                        </ol>
                    </li>
                </ul>
            </aside>
        </div> <!-- #main -->
    </div> <!-- #main-container -->

    <div class="footer-container">
        <footer class="wrapper">
            <ul>
                <li class="fb"><a href="#">Facebook</a></li>
                <li class="tw"><a href="#">Twitter</a></li>
                <li class="in"><a href="#">Instagram</a></li>
                <li class="li"><a href="#">LinkedIn</a></li>
            </ul>
        </footer>
    </div>
</div>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
<script src="js/main.js"></script>
</body>
</html>
