<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN
if(isUserLoggedIn()) { 
	header("Location: $websiteUrl"); 
	exit; 
}

//--------------------------------------------------------------------
// Login Posted

if(!empty($_POST) && isset($_POST['new_login'])) {
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
		//logIt("RA: <pre>".print_r($ra,true)."</pre>", "DEBUG");
		
		// Valid credentials
		if($auth->authenticated_user_id != Null) {
			// Log user in
			$loggedInUser = new RedcapPortalUser($auth->authenticated_user_id);
			setSessionUser($loggedInUser);
			
			//Redirect to user account page
			$destination = getSessionRedirectOr('index.php');
			header("Location: $destination");die();
		} else { // Invalid credentials
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
		}
	} // Validation
	
	// Add errors messages to session
	foreach ($errors as $error) {
		addSessionAlert($error);
	}
} // POST


//--------------------------------------------------------------------
// Render Login Page

// Use email for label if suppressing username
$username_label 		= $portal_config['useEmailAsUsername'] ? "Email" : "Username";
$username_validation	= $portal_config['useEmailAsUsername'] ? "required: true, email: true" : "required: true";

$loginPanel = new bootstrapPanel();
$loginPanel->setType('primary');
$loginPanel->setIcon('user');
$loginPanel->setHeader('<span class="headerText">Sign In</span>');
$loginPanel->setBody('
				<div class="form-group">
					<div class="col-xs-4 form-label"/>
						<label for="username" class="control-label">' . $username_label . '</label>
					</div>
					<div class="col-xs-8">
						<input type="text" class="form-control" name="username" id="username" placeholder="'.$username_label.'" autofocus />
					</div>
				</div>
				<div class="form-group">
					<div class="col-xs-4 form-label"/>
						<label for="password" class="control-label">Password:</label>
					</div>
					<div class="col-xs-8">
						<input type="password" class="form-control" name="password" id="password" placeholder="Password" />
					</div>
				</div>'
);
$loginPanel->setFooter('
				<div class="text-right">
					<input type="submit" class="btn btn-default" name="new_login" id="newfeedform" value="Sign In" />
				</div>'
);

$page = new htmlPage("Login | $websiteName");
$page->printStart();
require_once("navbar.php");
?>
<div class='container'>
	<div class="row">
		<div class="max-600">
			<?php	print getSessionMessages(); ?>
			<div class="max-400">
				<form id="loginForm" name="loginForm" class="form-horizontal" action="" method="post">
					<span class="headerText">Sign In</span>
					<?php echo $loginPanel->getPanel() ?>
				</form>
			</div>
			<div class="text-center">
				<a href="forgot-password.php">Forgot Password?</a>
			</div>
		</div><!-- /max-600 -->
	</div><!-- /row -->
</div><!-- /container -->
<script type='text/javascript'>
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
</script>
<?php
$page->printEnd();
