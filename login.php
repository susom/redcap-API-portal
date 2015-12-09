<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	$destination = (isUserActive() ? $websiteUrl . "dashboard/index.php" : $websiteUrl . "consent.php");
	header("Location: " . $destination);
	exit; 
}

if(isset($_GET["session_clear"])){
	unset($_SESSION[SESSION_NAME]['login_attempts']);
	header("Location: " . $websiteUrl . "login.php"); 
}

$attempts_remaining = (isset($_SESSION[SESSION_NAME]['login_attempts']) ? $_SESSION[SESSION_NAME]['login_attempts'] : 4);
$username_label 	= "";
$badlogin 			= "";
//--------------------------------------------------------------------
// Login Posted
if( !empty($_POST) && isset($_POST['new_login']) ) {
	$errors 	= array();
	$username 	= trim($_POST["username"]);
	$password 	= trim($_POST["password"]);
	$badlogin 	= $username;

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
			$loggedInUser 		= new RedcapPortalUser($auth->authenticated_user_id);
			unset($_SESSION[SESSION_NAME]['login_attempts']);
			setSessionUser($loggedInUser);


			//CHECK IF USER AGREED TO CONSENT YET
			if(!$loggedInUser->active){
				$destination 	= "consent.php";
			}else{
				$destination 	= getSessionRedirectOr($websiteUrl.'dashboard/index.php');
			}
			
			header("Location: $destination");
		} else { // Invalid credentials
			//IF NOT A REGISTERED USER - KEEP EMAIL AND PREFILL ON REGISTER FORM
			$attempts_remaining--;
			$_SESSION[SESSION_NAME]['login_attempts'] 	= $attempts_remaining;
			
			if($attempts_remaining < 1){
				$errors[] = lang("FORGOTPASS_SUGGEST");
			}else{
				$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID") . "<br> Try again... " . $attempts_remaining . " attempts remaining.";
			}
		}
	} 
	
	// Add errors messages to session
	foreach ($errors as $error) {
		addSessionAlert($error);
	}
}
$disabled = ($attempts_remaining < 1 ? "disabled=disabled" : null);
$username_validation  = $portal_config['useEmailAsUsername'] ? "required: true, email: true" : "required: true";

$pg_title 		= "Login : $websiteName";
$body_classes 	= "login";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
    <div id="main-content" class="col-md-8 col-md-offset-2 logpass" role="main">
		<div class="well row">
			<form id="loginForm" name="loginForm" class="form-horizontal loginForm col-md-6 " action="login.php" method="post" novalidate="novalidate">
				<h2>Please Login to continue</h2>
				<div class="form-group">
					<label for="username" class="control-label">Email Address</label>
					<input <?php echo $disabled?> type="text" class="form-control" name="username" id="username" placeholder="Enter Email Address" autofocus="true" aria-required="true" aria-invalid="true" aria-describedby="username-error" value="<?php echo $badlogin?>">
				</div>
				<div class="form-group">
					<label for="password" class="control-label">Password</label>
					<input <?php echo $disabled?> type="password" class="form-control" name="password" id="password" placeholder="Enter Password" autocomplete="off" >
				</div>
				<div class="form-group">
					<div class="pull-left">
						<a class="showrecover" href="#">Forgot Password?</a> <br>
						<a class="showregister" href="register.php">Register for Study</a>  
					</div>    
					<input <?php echo $disabled?> type="submit" class="btn btn-success pull-right" name="new_login" id="newfeedform" value="Log In"/>      
				</div>
	        </form>

	        <form id="pwresetForm" name="newLostPass" class="form-horizontal lostPass  col-md-6 " action="forgot_password.php" method="post">
				<aside class="stepone">
					<h2>Enter email to begin password reset</h2>
					<div class="form-group">
						<label for="username" class="control-label">Email Address</label>
						<input type="text" class="form-control" name="forgotemail" id="forgotemail" placeholder="Enter Email Address" autofocus value="<?php echo $badlogin?>"/>
					</div>
					<div class="form-group">
						<a class="showlogin pull-left" href="#">Login Now</a>       
						<button type='submit' class="btn btn-success pull-right nextstep" title="Forgot Password" >Next Step</button>
					</div>
				</aside>

				<aside class="steptwo">
					<h2>Chose recovery method</h2>
					<div class="form-group">
						<label for="emailme" class="control-label">
							<input type="radio" name="resetlink" id="emailme" checked value="emailme"/>
							Email me a password reset link
						</label>
						
					</div>
					<div class="form-group">
						<label for="secquestions" class="control-label">
							<input type="radio" name="resetlink" id="secquestions" value="secquestions"/>
							Answer my security questions
						</label>
					</div>
					<div class="form-group">
						<a class="showlogin pull-left" href="#">Login Now</a>       
						<input type="submit" class="btn btn-success pull-right " name="new_pass_reset_request" id="newfeedform" value="Forgot Password" />
					</div>
				</aside>
			</form>
        </div>	
    </div>
  </div>
</div>
<script>
$(document).ready(function(){
	$(".showrecover, .showlogin").click(function(e){
		$(".logpass form").hide();

		$("#forgotemail").val( $("#username").val() );

		if($(this).hasClass("showrecover")){
			$(".lostPass").fadeIn("medium");
		}else{
			$(".loginForm").fadeIn("medium");
		}
		return false;
	});

	$(".nextstep").click(function(){
		if($("#forgotemail").val()){
			$(".logpass form").hide();
			$(".stepone").hide();
			$(".steptwo").show();
			$(".lostPass").fadeIn("medium");
		}else{
			$("#forgotemail").closest('.form-group').addClass('has-error');
		}
		return false;
	});

	$('#pwresetForm').validate({
		rules: {
			forgotemail: {
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

	$('#loginForm').validate({
		rules: {
			username: {
				required: true
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
<?php 
include("models/inc/gl_footer.php");
?>

          