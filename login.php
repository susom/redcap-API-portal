<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	header("Location: $websiteUrl"); 
	exit; 
}

//--------------------------------------------------------------------
// Login Posted

if( !empty($_POST) && isset($_POST['new_login']) ) {
	$errors 	= array();
	$username 	= trim($_POST["username"]);
	$password 	= trim($_POST["password"]);

	//Perform some basic validation
	if($username == "") $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	if($password == "") $errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");

	//End data validation
	if(count($errors) == 0) {
		// Continue with authentication
		$auth = new RedcapAuth($username,$password);
		
		// Valid credentials
		if($auth->authenticated_user_id != Null) {
			// Log user in
			$loggedInUser = new RedcapPortalUser($auth->authenticated_user_id);
			setSessionUser($loggedInUser);

			//Redirect to user account page
			$destination = getSessionRedirectOr('index.php');
			$location_redirect = $destination;
		} else { // Invalid credentials
			//IF NOT A REGISTERED USER - KEEP EMAIL AND PREFILL ON REGISTER FORM
			$_SESSION[SESSION_NAME]['new_username'] = $username;
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
			$location_redirect = $websiteUrl;
		}
	} // Validation
	
	// Add errors messages to session
	foreach ($errors as $error) {
		addSessionAlert($error);
	}

	header("Location: $location_redirect"); 
	exit; 
} // POST


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