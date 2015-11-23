<?php
require_once("models/config.php");
$pg_title = "Login : $websiteName";

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	header("Location: $websiteUrl/portal.php"); 
	exit; 
}
if(isset($_GET["session_clear"])){
	unset($_SESSION[SESSION_NAME]['login_attempts']);
	header("Location: $websiteUrl/login.php"); 
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
			setSessionUser($loggedInUser);

			//Redirect to user account page
			$destination 		= getSessionRedirectOr('portal.php');
			header("Location: $destination");
		} else { // Invalid credentials
			//IF NOT A REGISTERED USER - KEEP EMAIL AND PREFILL ON REGISTER FORM
			$attempts_remaining--;
			$_SESSION[SESSION_NAME]['login_attempts'] 	= $attempts_remaining;
			
			if($attempts_remaining < 1){
				$errors[] = lang("FORGOTPASS_SUGGEST");
			}else{
				$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID") . " Try again... " . $attempts_remaining . " attempts remaining.";
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

include("models/inc/gl_header.php");
?>
<body class="login">
<div id="su-wrap">
<div id="su-content">

    <div id="brandbar"></div> 

    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
        <div id="main-content" class="col-md-4 col-md-offset-4 logpass" role="main">
			<a href="index.php"><img src="assets/img/Stanford_Medicine_logo-web-CS.png" id="logo"/></a>
			
			<?php
				print getSessionMessages();
			?>

			<div class="well">
				<h2>Well Registry</h2>

				<form id="loginForm" name="loginForm" class="form-horizontal loginForm" action="login.php" method="post" novalidate="novalidate">
					<h3>Please login to continue</h3>
					<div class="form-group">
						<label for="username" class="control-label">Email Address</label>
						<input <?php echo $disabled?> type="text" class="form-control" name="username" id="username" placeholder="Enter Email Address" autofocus="true" aria-required="true" aria-invalid="true" aria-describedby="username-error" value="<?php echo $badlogin?>">
						<label for="password" class="control-label">Password:</label>
						<input <?php echo $disabled?> type="password" class="form-control" name="password" id="password" placeholder="Enter Password" autocomplete="off" >
					</div>
					<div class="form-group">
						<a class="showrecover pull-left" href="#">Forgot Password?</a>      
						<input <?php echo $disabled?> type="submit" class="btn btn-info pull-right" name="new_login" id="newfeedform" value="Login"/>      
					</div>
		        </form>

		        <form id="pwresetForm" name="newLostPass" class="form-horizontal lostPass" action="forgot-password.php" method="post">
					<aside class="stepone">
						<h3>To begin password reset process:</h3>
						<div class="form-group">
							<label for="username" class="control-label">Enter email address:</label>
							<input type="text" class="form-control" name="forgotemail" id="forgotemail" placeholder="Enter Email Address" autofocus value="<?php echo $badlogin?>"/>
						</div>
						<div class="form-group">
							<a class="showlogin pull-left" href="#">Login Now</a>       
							<a href="#" class="btn btn-info pull-right nextstep" title="Forgot Password" >Next Step</a>
						</div>
					</aside>

					<aside class="steptwo">
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
						<div class="form-group submits">
							<a class="showlogin pull-left" href="#">Login Now</a>       
							<input type="submit" class="btn btn-info pull-right " name="new_pass_reset_request" id="newfeedform" value="Forgot Password" />
						</div>
					</aside>
				</form>
	        </div>	
        </div>
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
		$(".logpass form").hide();
		$(".stepone").hide();
		$(".steptwo").show();
		$(".lostPass").fadeIn("medium");
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
</body>
<?php 
include("models/inc/gl_footer.php");
?>

          