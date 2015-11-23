<?php
require_once("models/config.php");
$pg_title = "$websiteName";

include("models/inc/gl_header.php");
?>
<body class="index">
<div id="su-wrap">
<div id="su-content">

    <div id="brandbar"></div> 

    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
        <div id="main-content" class="col-md-4 col-md-offset-4" role="main">
          <img src="assets/img/Stanford_Medicine_logo-web-CS.png" id="logo"/>
          <?php
            print getSessionMessages();
          ?>
          
          <div class="well signinup">
            <h2>Well Registry Flows</h2>
            <p>
              <a href="login.php" class="btn btn-primary">Login Page</a>  <a href="register.php" class="btn btn-info">Register Page</a>
            </p>
          </div>  
        </div>
      </div>
    </div>

</div>
</div>
</body>
<?php 
include("models/inc/gl_footer.php");
?>

