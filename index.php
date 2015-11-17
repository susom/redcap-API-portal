<?php
require_once("models/config.php");
$pg_title = "Login | $websiteName";

if(isUserLoggedIn()){
  include(PORTAL_INC_PATH."/learn_functions.php");

  // requireActiveUserAccount(); // Only allow 'active' users that presumably have met all requirements

  $activeuser = $loggedInUser->getEmail();
  $record     = $loggedInUser->user_id;
}

$username_label       = $portal_config['useEmailAsUsername'] ? "Email" : "Username";
$username_validation  = $portal_config['useEmailAsUsername'] ? "required: true, email: true" : "required: true";
$username_block       = $validation_rules = '';
$bad_login            = (!empty($_SESSION[SESSION_NAME]['new_username']) ? $_SESSION[SESSION_NAME]['new_username'] : null);

include("models/inc/gl_header.php");
?>
<!-- Customization Options:                       
     body class:   "home", "nav-1", "nav-2", etc. - specifies which item in the top nav to underline
                   "site-slogan" - display a site slogan in the header signature
     logo, h1  :   "hide" - hides the logo or H1 element, eg <h1 class="hide">
 -->
<body class="site-slogan">
<div id="su-wrap">
  <div id="su-content">
    <div id="brandbar">
      <div class="container"> 
        <a class="pull-left som_logo" href="http://www.stanford.edu"><img src="assets/lagunita/images/brandbar_logo_som.png" alt="Stanford University" width="176" height="23"></a> 
        
        <nav class="pull-right">
          <ul class="list-unstyled pull-right">
            <?php
            if(isUserLoggedIn()){
              $isactive = (isUserActive() ? "Acount is active." : "Account is inactive.");
              $emailver = ($loggedInUser->isEmailVerified() ? "Email verified on " . date('d-M-Y', strtotime($loggedInUser->email_verified_ts)) . "." : "Email not verified.");
            ?>
            <li class="nav-item pull-left">
                <a href="#" ><?php echo $activeuser ?></a> 
                <div class="nav-item-panel">
                  <aside>
                  <ul>
                  <li><?php echo $isactive?></li>
                  <li><?php echo $emailver?></li>
                  <li><a href="index.php?logout=1">Logout</a></li>
                  </ul>
                  </aside>
                </div>
            </li>
            <?php  } ?>

            <?php
            if(!isUserLoggedIn()){
            ?>
            <li class="nav-item pull-left logreg">
                <a href="#">Login</a> 
                <div class="nav-item-panel">
                  <aside class="loginForm">
                    <form id="loginForm" name="loginForm" class="form-horizontal" action="login.php" method="post" novalidate="novalidate">
                      <div class="fosrm-group">
                        <label for="username" class="control-label"><?=$username_label?></label>
                        <input type="text" class="form-control" name="username" id="username" placeholder="<?=$username_label?>" autofocus="" autocomplete="off" aria-required="true" aria-invalid="true" aria-describedby="username-error" <?=(!is_null($bad_login) ? "value='$bad_login'" : "")?>>
                        <label for="password" class="control-label">Password:</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off" >
                      </div>

                      <a class="recoverpw pull-left" href="forgot-password.php">Forgot Password?</a>      
                      <input type="submit" class="btn btn-default pull-right" name="new_login" id="newfeedform" value="Login"/>      
      
                    	<div class="footer-links">
                    		<p class="emailus">If you are trying to withdraw and are unable to reset your credentials, please email us at <a href="mailto:well@stanford.edu">wellregistry@stanford.edu</a> from your portal email account requesting to discontinue participation.</p>
                    	</div>
                    </form>
                  </aside>
                  <aside class="lostPass">
                    <form name="newLostPass" class="form-horizontal" action="forgot-password.php" method="post">
                      <label for="username" class="control-label">To begin enter your account email address.</label>
                      <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email Address" autofocus />
                      <div class="footer-links">
                        <a class="login pull-left" href="#">Login</a>       
                        <input type="submit" class="btn btn-default pull-right" name="new_pass_reset_request" id="newfeedform" value="Forgot Password" />
                      </div>
                    </form>
                  </aside>
                </div>
            </li>
            <script>
              $(document).ready(function(){
                $("nav .recoverpw, nav a.login").click(function(e){
                  // e.preventDefault();

                  $(".logreg aside").hide();

                  if($(this).hasClass("recoverpw")){
                    $(".lostPass").fadeIn("medium");
                  }else{
                    $(".loginForm").fadeIn("medium");
                  }

                  return false;
                });

                $('#loginForm').validate({
                  rules: {
                    username: {
                      <?php echo $username_validation ?>
                    },
                    password: {
                      required: true
                    }
                  },
                  highlight: function(element) {
                    $(element).closest('.form-group').addClass('has-error');
                  },
                  unhighlight: function(element) {
                    $(element).closest('.form-group').removeClass('has-error');
                  },
                  errorElement: 'span',
                  errorClass: 'help-block',
                  errorPlacement: function(error, element) {
                    if(element.parent('.input-group').length) {
                      error.insertAfter(element.parent());
                    } else {
                      error.insertAfter(element);
                    }
                  }
                });
              });
            </script>
            <?php }?>
          </ul>
        </nav>
      </div>
    </div> 

    <?php
      include("models/inc/project_header.php");
    ?>
    
    <!-- main content -->
    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
      	<?php
    			print getSessionMessages();
    		?>

        <!-- Main column -->
        <div id="main-content" class="col-md-9" role="main">
          <div id="feature-banner" class="margin-bottom-30"> <img class="img-responsive" alt="" src="http://www.stanford.edu/assets/su-images/feature/ht_quad6am.jpg" />
            <div class="feature-caption">
              <?php
              if(isUserLoggedIn()){
                echo "<h3>Welcome Back!</h3>";
                echo "<p>Thanks for your commitment.  You are almost done!  You have 4 more surveys to complete 100%</p>";
              }else{
                echo "<h3>Welcome to the Well Registry Portal</h3>";
                echo "<p>Thank you for your interest in participating in the Well Registry.  Please register to begin!</p>";
              }
              ?>
            </div>
          </div>

          <?php
          if(isUserLoggedIn()){
          ?>
            <section >
              <h2 class="headline">Study Surveys               
                <div class="percent_complete">20% <span>1 of 5 complete</span></div>
              </h2>
              <p>Please complete all forms listed below</p>          

              <ul class="surveys">
                <li><a class="completed grapes" href="survey.php?sid=1">1 : Screening Questions for the Wellness Living Laboratory</a></li>
                <li><a class=" apple" href="survey.php?sid=2">2 : Socio-Demographic Questions</a></li>
                <li><a class=" orange" href="survey.php?sid=3">3 :  Behavior Questions</a></li>
                <li><a class=" cherry" href="survey.php?sid=4">4 : Social and Neighborhood Environment</a></li>
                <li><a class=" banana" href="survey.php?sid=5">5 : Wellness Questions</a></li>
              </ul>
            </section>
          <?php
          }else{
            include("models/inc/form_register.php");
          } ?>
        </div>
        <div id="sidebar-second" class="col-md-3">
          <?php if(isUserLoggedIn()){ ?>  
            <div class="well lowhang">
              <h2>Low hanging fruit</h2>
              <p>Chose password reminder questions</p>
              <form class="secans form-vertical">
                <div class="form-group">
                  <label>Question 1</label>
                  <select>
                    <option>What street did you live on in 3rd grade?</option>
                  </select>
                  <input type="text">
                </div>
                <div class="form-group">
                  <label>Question 2</label>
                  <select>
                    <option>What food did you hate as a kid?</option>
                  </select>
                  <input type="text">
                </div>
                <div class="form-group">
                  <label>Question 3</label>
                  <select>
                    <option>What is the most exotic ice cream flavor?</option>
                  </select>
                  <input type="text">
                </div>
                <p class="surveys pull-left">Reward : <a href="#" class="single strawberry"></a></p>

                <button type="submit" class="pull-right  btn btn-primary">Save Answers</button>

              </form>
            </div>

            <div class="well">
              <h2>Fruit Basket (hah!)</h2>
              <p>See how many fruits you have! Get fruits by completing surveys and other account completion tasks!</p>

              <ul class="fruit_basket">
                <li><a href="#" class="strawberry"></a></li>
                <li><a href="#" class="completed grapes"></a></li>
                <li><a href="#" class="apple"></a></li>
                <li><a href="#" class="orange"></a></li>
                <li><a href="#" class="cherry"></a></li>
                <li><a href="#" class="blueberry"></a></li>
                <li><a href="#" class="banana"></a></li>
                <li><a href="#" class="longan"></a></li>
                <li><a href="#" class="pineapple"></a></li>
              </ul>
            </div>
            <?php
            }
            include("models/inc/well_about.php");
          ?>
        </div>
      </div>
    </div>
  </div>
</div>

<form id="resumeSurveyForm" method="post" action="">
  <input type="hidden" id="__code" name="__code" />
</form>

<?php
  include("models/inc/project_footer.php");
?>
</body>
<?php 
  include("models/inc/gl_footer.php");
?>

