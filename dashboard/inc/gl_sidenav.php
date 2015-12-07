<aside class="bg-black aside-md hidden-print hidden-xs <?php echo (isset($navmini) ? "nav-xs" : ""); ?>" id="nav">          
  <section class="vbox">
    <section class="w-f scrollable">
      <div class="slim-scroll" data-height="auto" data-disable-fade-out="true" data-distance="0" data-size="10px" data-railOpacity="0.2">
        <div class="clearfix wrapper dk nav-user hidden-xs">
          <div class="dropdown">
            
            <!-- USER PROFILE PIC -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <span class="thumb avatar pull-left m-r">                        
                <img src="images/a0.png" class="dker" alt="...">
                
              </span>
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
          <div class="text-muted text-sm hidden-nav-xs padder m-t-sm m-b-sm">Start</div>
          <ul class="nav nav-main" data-ride="collapse">
            <li  class="active">
              <a href="index.php" class="auto">
                <i class="i i-statistics icon"></i>
                <span class="font-bold">My Home</span>
              </a>
            </li>
            <?php 
            if(!isset($hidenavs)){
            ?>
            <li >
              <a href="#" class="auto">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <b class="badge bg-danger pull-right">4</b>
                <i class="i i-stack icon"></i>
                <span class="font-bold">My Surveys</span>
              </a>
              <ul class="nav dk">
                <?php
                foreach($surveys as $survey){
                  $surveylink = "survey.php?url=".urlencode($survey[3]);
                  $surveyname = $survey[0];
                  print_r("<li >
                      <a href='$surveylink' class='auto'>                                                        
                        <i class='i i-dot'></i>
                        <span>$surveyname</span>
                      </a>
                    </li>\n");
                }
                ?>
              </ul>
            </li>
            <li >
              <a href="#" class="auto disabled">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-lab icon">
                </i>
                <span class="font-bold">My Progress</span>
              </a>
              <!-- <ul class="nav dk">
                <li >
                  <a href="buttons.html" class="auto">                                                        
                    <i class="i i-dot"></i>

                    <span>Buttons</span>
                  </a>
                </li>
                <li >
                  <a href="icons.html" class="auto">                            
                    <b class="badge bg-info pull-right">3</b>                                                        
                    <i class="i i-dot"></i>

                    <span>Icons</span>
                  </a>
                </li>
              </ul> -->
            </li>
            <li >
              <a href="#" class="auto disabled">
                <span class="pull-right text-muted">
                  <i class="i i-circle-sm-o text"></i>
                  <i class="i i-circle-sm text-active"></i>
                </span>
                <i class="i i-docs icon"></i>
                <span class="font-bold">My Profile</span>
              </a>
              <!-- <ul class="nav dk">
                <li >
                  <a href="profile.html" class="auto">                                                        
                    <i class="i i-dot"></i>
                    <span>Profile</span>
                  </a>
                </li>
                <li >
                  <a href="profile-2.html" class="auto">                                                        
                    <i class="i i-dot"></i>
                    <span>Account Settings</span>
                  </a>
                </li>
              </ul> -->
            </li>
            
            <li >
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
            </li>
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