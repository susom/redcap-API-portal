<aside class="bg-black aside-md hidden-print hidden-xs <?php echo (isset($navmini) ? "nav-xs" : ""); ?>" id="nav">          
  <section class="vbox">
    <section class="w-f scrollable">
      <div class="slim-scroll" data-height="auto" data-disable-fade-out="true" data-distance="0" data-size="10px" data-railOpacity="0.2">
        <div class="clearfix wrapper dk nav-user hidden-xs">
          <div class="dropdown">
            <!-- USER PROFILE PIC -->
            <a href="profile.php" >
              <span class="thumb avatar pull-left m-r">                        
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
            if($shownavsmore || 1){
            ?>
            <li>
              <a href="index.php" class="auto">
                <i class="i i-statistics icon"></i>
                <span class="font-bold">My Dashboard</span>
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
                <span class="font-bold">My Surveys</span>
              </a>
              <ul class="nav dk">
                <?php
                $new = null;
                $core_surveys     = array();
                $supp_surveys     = array();
                foreach($surveys as $surveyid => $survey){
                  $index          = array_search($surveyid, $all_survey_keys);
                  $surveylink     = "survey.php?sid=" . $surveyid;
                  $surveyname     = $survey["label"];
                  $surveycomplete = $survey["survey_complete"];

                  $completeclass  = ($surveycomplete ? "completed":"");
                  $hreflink       = (is_null($new) || $surveycomplete ? "href" : "rel");
                  $newbadge       = (is_null($new) && !$surveycomplete ? "<b class='badge bg-danger pull-right'>new!</b>" :null);
                  
                  if(!$surveycomplete && is_null($new)){
                    $new = $index;
                  }

                  if(in_array($surveyid, SurveysConfig::$core_surveys)){
                    array_push($core_surveys, "<li >
                        <a $hreflink='$surveylink' class='auto' title='".$survey["label"]."'>
                          $newbadge                                                   
                          <span class='fruit $completeclass ".$fruits[$index]."'></span>
                          <span class='survey_name'>$surveyname</span>     
                        </a>
                      </li>\n");
                  }else{
                    array_push($supp_surveys, "<li >
                        <a $hreflink='$surveylink' class='auto' title='".$survey["label"]."'>
                          $newbadge                                                 
                          <span class='fruit $completeclass ".$fruits[$index]."'></span>
                          <span class='survey_name'>$surveyname</span>     
                        </a>
                      </li>\n");
                  }
                }

                echo implode("",$core_surveys);
                
                //SHOW NON CORE SURVEYS ONCE THE CORE ARE COMPLETE
                if($core_surveys_complete){
                  echo implode("",$supp_surveys);
                }
                ?>
              </ul>
            </li>
            <?php 
            if($shownavsmore){
            ?>
            <li <?php echo $profile_active ?>>
              <a href="profile.php">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-docs icon"></i>
                <span class="font-bold">My Profile</span>
              </a>
            </li>
            <li <?php echo $game_active ?>>
              <a href="game.php">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-docs icon"></i>
                <span class="font-bold">Play Game</span>
              </a>
            </li>
            <li>
              <a href="mailto:wellforlife@stanford.edu?subject=Question for WELL" class="nav dk">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-mail icon"></i>
                <span class="font-bold">Contact Us</span>
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