<?php
require_once("models/config.php");

// addSessionAlert("hehehehe");

$pg_title     = "$websiteName";
$body_classes = "index signinup";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
    <div id="main-content" class="col-md-8 col-md-offset-2" role="main">
      <div class="well">
        <h2>Well Registry</h2>
        <p>
          <a href="login.php" class="btn btn-success">Login Page</a>  
          <a href="register.php" class="btn btn-success">Register Page</a>
        </p>
      </div>  
    </div>
  </div>
</div>
<?php 
include("models/inc/gl_footer.php");
?>

