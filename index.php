<?php
require_once("models/config.php");

$lang_req = isset($_GET["lang"]) ? "?lang=".$_GET["lang"] : "";

$pg_title     = "$websiteName";
$body_classes = "index signinup";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
    <div id="main-content" class="col-md-8 col-md-offset-2" role="main">
      <div class="well">
        <p class="login_reg">
          <a href="login.php<?php echo $lang_req ?>" class="btn btn-success"><?php echo lang("ACCOUNT_LOGIN_PAGE") ?></a>  
          <a href="register.php<?php echo $lang_req ?>" class="btn btn-success"><?php echo lang("ACCOUNT_REGISTER_PAGE") ?></a>
        </p>
      </div>  
    </div>
  </div>
</div>
<?php 
include("models/inc/gl_footer.php");
?>

