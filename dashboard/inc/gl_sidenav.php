<?php 
  // if(isset($_SESSION["elite_users"])){
  //   $elite        = $_SESSION["elite_users"];
  // }else{
  //   $elite        = getEliteUsers();
  //   $_SESSION["elite_users"]  = $elite;
  // }
  // $special_user = in_array($loggedInUser->id, $elite) ?  "special_user_icon" : "";  
  // if(in_array($loggedInUser->id, $elite)){
  //   foreach($elite as $uorder => $uids){
  //     if($uids == $loggedInUser->id){
  //       $elite_order = $uorder + 1;
  //     }
  //   }
  // }
?>
<aside class="bg-black aside-md hidden-print hidden-xs <?php echo (isset($navmini) ? "nav-xs" : ""); ?>" id="nav">          
  <section class="vbox">
    <section class="w-f scrollable">
      <div class="slim-scroll" data-height="auto" data-disable-fade-out="true" data-distance="0" data-size="10px" data-railOpacity="0.2">
        <div class="clearfix wrapper dk nav-user hidden-xs">
          <div class="dropdown">
            <!-- USER PROFILE PIC -->
            <a href="profile.php" >
              <?php 
              // echo $special_user 
              ?>
              <span class="thumb avatar pull-left m-r ">                        
              </span>
              <style>
                .thumb.avatar {
                  border:1px solid #ccc;
                  width:50px; height:50px; 
                  <?php
                  $conversion = 4.6;
                  if(!$_SESSION["REDCAP_PORTAL"]["user"]->portal_pic){
                    $smallsize  = "0 0";
                  }else{
                    $bigsize    = explode(" ",str_replace("px" ,"" , $_SESSION["REDCAP_PORTAL"]["user"]->portal_pic));
                    $smallx     = round($bigsize[0]/$conversion);
                    $smally     = round($bigsize[1]/$conversion);
                    $smallsize  = $smallx."px ".$smally."px";
                  }
                  ?>
                  background:#fff url(images/profile_icons.png) <?php echo $smallsize?> no-repeat;
                  background-size:500%;
                }
              </style>
              <span class="hidden-nav-xs clear">
                <span class="block m-t-xs">
                  <strong class="font-bold text-lt"><?php echo $firstname . " " . $lastname; ?></strong> 
                </span>
                <span class="text-muted text-xs block"></span>
              </span>
            </a>
          </div>
        </div>                

        <!-- nav -->                 
        <nav class="nav-primary hidden-xs ">
          <div class="text-muted text-sm hidden-nav-xs padder m-t-sm m-b-sm"></div>
          <ul class="nav nav-main" data-ride="collapse">
            <?php 
            if ($shownavsmore || 1) {
            ?>
              <li>
                <a href="index.php" class="auto">
                  <i class="i i-statistics icon"></i>
                  <span class="font-bold"><?php echo $lang["MY_DASHBOARD"]; ?></span>
                </a>
              </li>
              <script>
              //overide the default behavior of making it active
              $("a[href='index.php']").click(function(){
                location.href="index.php";
                return false;
              });
              </script>
            <?php
            }
            ?>
            <li <?php echo $survey_active ?>>
              <a href="#" class="auto">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-stack icon"></i>
                <span class="font-bold"><?php echo $lang["CORE_SURVEYS"] ?></span>
              </a>
              <ul class="nav dk">
                <?php
                $new = null;
                $core_surveys           = array();
                $supp_surveys           = array();

                foreach($surveys as $surveyid => $survey){
                  $projnotes      = json_decode($survey["project_notes"],1);
                  $title_trans    = $projnotes["translations"];
         
                  $index          = array_search($surveyid, $all_survey_keys);

                  $surveylink     = "survey.php?sid=" . $surveyid;
                  $surveyname     = isset($title_trans[$_SESSION["use_lang"]][$surveyid]) ?  $title_trans[$_SESSION["use_lang"]][$surveyid] : $survey["label"];
                  $surveycomplete = $survey["survey_complete"];

                  $completeclass  = ($surveycomplete ? "completed":"");
                  $hreflink       = (is_null($new) || $surveycomplete ? "href" : "rel");
                  $newbadge       = (is_null($new) && !$surveycomplete ? "<b class='badge bg-danger pull-right'>new!</b>" :null);

                  $fruitcss       = $user_short_scale ? "nofruit" : $fruits[$index];
                  if(!$surveycomplete && is_null($new)){
                    $new = $index;
                    $next_survey =  $surveylink;
                  }

                  if(in_array($surveyid, $available_instruments)){
                    array_push($core_surveys, "<li >
                        <a $hreflink='$surveylink' class='auto' title='".$survey["label"]."'>
                          $newbadge                                                   
                          <span class='fruit $completeclass $fruitcss'></span>
                          <span class='survey_name'>$surveyname</span>     
                        </a>
                      </li>\n");
                  }
                }
                echo implode("",$core_surveys);
                ?>
              </ul>
            </li>
            <?php 
            if($shownavsmore){
            ?>
            <li <?php echo $assesments ?>>
              <a href="assessments.php">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-docs icon"></i>
                <span class="font-bold"><?php echo $lang["MY_ASSESSMENTS"]; ?></span>
              </a>
            </li>

            <!-- <li <?php echo $studies_active ?>>
              <a href="studies.php">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-docs icon"></i>
                <span class="font-bold"><?php echo $lang["MY_STUDIES"] ?></span>
              </a>
            </li> -->
            
            <li <?php echo $profile_active ?>>
              <a href="profile.php">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-docs icon"></i>
                <span class="font-bold"><?php echo lang("MY_PROFILE") ?></span>
              </a>
            </li>
            
            <li <?php echo $game_active ?>>
              <!-- class="under_construction" -->
              <a href="game.php" class="">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-docs icon"></i>
                <span class="font-bold">Play Game</span>
              </a>
            </li>
            
            <li>
              <a href="mailto:wellforlife@stanford.edu?subject=<?php echo $lang["QUESTION_FOR_WELL"] ?>" class="nav dk">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-mail icon"></i>
                <span class="font-bold"><?php echo $lang["CONTACT_US"] ?></span>
              </a>
            </li>
            <!-- <li >
              <a href="#" class="auto">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-lab icon">
                </i>
                <span class="font-bold">Theme StyleGuide</span>
              </a>
              <ul class="nav dk">
                <li >
                  <a href="<?php echo $websiteUrl; ?>dashboard/theme_styleguide/docs.html" target="_blank">                                                        
                    <i class="i i-dot"></i>
                    <span>Help</span>
                  </a>
                </li>
                <li >
                  <a href="<?php echo $websiteUrl; ?>dashboard/theme_styleguide/" target="_blank">                            
                    <i class="i i-dot"></i>
                    <span>Example Site</span>
                  </a>
                </li>
              </ul>
            </li> -->
            <?php
            }
            ?>
            <li class="get_help">
              <div>
                <h3><?php echo $lang["GET_HELP"] ?></h3>
                <?php echo $lang["GET_HELP_TEXT"] ?>
              </div>
            </li>
          </ul>
          <div class="line dk hidden-nav-xs"></div>
        </nav>
        <!-- / nav -->
      </div>
    </section>
    <footer class="footer hidden-xs no-padder text-center-nav-xs">
      <a href="#nav" data-toggle="class:nav-xs" class="btn btn-icon icon-muted btn-inactive m-l-xs m-r-xs">
        <i class="i i-circleleft text"></i>
        <i class="i i-circleright text-active"></i>
      </a>
    </footer>
  </section>
</aside>