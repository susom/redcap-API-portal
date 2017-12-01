<header class="bg-white header header-md navbar navbar-fixed-top-xs box-shadow">
  <div class="navbar-header aside-md dk">
    <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen,open" data-target="#nav,html">
      <i class="fa fa-bars"></i>
    </a>
    <a href="index.php" class="navbar-brand">
      <img src="images/well_logo_treeonly.png" class="m-r-sm" alt="scale">
      <span class="hidden-nav-xs"><?php echo $lang["WELL_FOR_LIFE"] ?></span>
    </a>
    <a class="btn btn-link visible-xs" data-toggle="dropdown" data-target=".user">
      <i class="fa fa-cog"></i>
    </a>
  </div>

  <ul class="nav navbar-nav navbar-right m-n hidden-xs nav-user user">
    <!-- <li class="hidden-xs">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="i i-chat3"></i>
        <span class="badge badge-sm up bg-danger count" style="display: inline-block;">3</span>
      </a>
      <section class="dropdown-menu aside-xl animated flipInY">
        <section class="panel bg-white">
          <div class="panel-heading b-light bg-light">
            <strong>You have <span class="count" style="display: inline;">3</span> notifications</strong>
          </div>
          <div class="list-group list-group-alt"><a href="#" class="media list-group-item" style="display: block;"><span class="pull-left thumb-sm text-center"><i class="fa fa-envelope-o fa-2x text-success"></i></span><span class="media-body block m-b-none">Sophi sent you a email<br><small class="text-muted">1 minutes ago</small></span></a>
            <a href="#" class="media list-group-item">
              <span class="pull-left thumb-sm">
                <img src="images/a0.png" alt="..." class="img-circle">
              </span>
              <span class="media-body block m-b-none">
                Use awesome animate.css<br>
                <small class="text-muted">10 minutes ago</small>
              </span>
            </a>
            <a href="#" class="media list-group-item">
              <span class="media-body block m-b-none">
                1.0 initial released<br>
                <small class="text-muted">1 hour ago</small>
              </span>
            </a>
          </div>
          <div class="panel-footer text-sm">
            <a href="#" class="pull-right"><i class="fa fa-cog"></i></a>
            <a href="#notes" data-toggle="class:show animated fadeInRight">See all the notifications</a>
          </div>
        </section>
      </section>
    </li> -->
    <li>
      <?php
        // include("../models/inc/language_select.php");
        // $lang_query = "&lang=".$_SESSION["use_lang"];
      ?>
    </li>
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <span class="thumb-sm avatar pull-left">
        </span>
        <?php echo $firstname . " " . $lastname; ?> <b class="caret"></b>
        <style>
          .thumb-sm.avatar {
            border:1px solid #ccc;
            width:40px; height:40px; 
            <?php
            $conversion = 6;
            if(!$_SESSION["REDCAP_PORTAL"]["user"]->portal_pic){
              $smallsize  = "0 0";
            }else{
              $bigsize    = explode(" ",str_replace("px" ,"" , $_SESSION["REDCAP_PORTAL"]["user"]->portal_pic));
              $smallx     = round($bigsize[0]/$conversion);
              $smally     = round($bigsize[1]/$conversion);
              $smallsize  = $smallx."px ".$smally."px";
            }
            ?>
            background: url(images/profile_icons.png) <?php echo $smallsize?> no-repeat;
            background-size:500%;
          }
        </style>
      </a>
      <ul class="dropdown-menu animated fadeInRight">            
        <!-- <li>
          <span class="arrow top"></span>
          <a href="#">Account Settings</a>
        </li> -->
        <li class="divider"></li>
        <li>
          <a href="<?php echo $websiteUrl ?>index.php?logout=1"><?php echo $lang["LOGOUT"] ?></a>
        </li>
      </ul>
    </li>
  </ul>      
</header>