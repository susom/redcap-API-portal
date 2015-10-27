<?php

	require_once("models/config.php");
	
	//Prevent the user visiting the logged in page if he/she is already logged in
	if(isUserLoggedIn()) { header("Location: index.php"); die(); }

// Process New User Request
if(!empty($_POST['submit_new_user']))
{
	$errors = array();
	
	$email = trim($_POST["email"]);
	// use the email as the username if configured
	$username = $portal_config['useEmailAsUsername'] ? $email : trim($_POST["username"]);
	$password = trim($_POST["password"]);
	$password_again = trim($_POST["password_again"]);
	
	// Verify reCaptcha
	$reCaptcha = verifyReCaptcha();
	if ($reCaptcha['success'] != true) {
		$errors[] = "Invalid reCaptcha response - please try again.";
		logIt("Invalid reCaptcha in registration with $email: ". implode(','. $reCaptcha['
			error-codes']), "INFO");
	}
	
	if(minMaxRange(5,50,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,50));
	}
	if(minMaxRange(8,50,$password))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $password_again)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidemail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	//End data validation
	if(count($errors) == 0)
	{	
		//Construct a user auth object
		$auth = new RedcapAuth($username,NULL,$email);
		
		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if($auth->emailExists())
		{
			$errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
		}
		elseif($auth->usernameExists())
		{
			$errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if($auth->createNewUser($password))
			{
				addSessionMessage(lang('ACCOUNT_REGISTRATION_COMPLETE_TYPE2'));
				// Redirect to profile page to complete registration
				$loggedInUser = new RedcapPortalUser($auth->new_user_id);
				setSessionUser($loggedInUser);
				header("Location: profile.php");die();
			}
			else
			{
				$errors[] = !empty($auth->error) ? $auth->error : 'Unknown error creating user';
			}
		}
	}
	
	// Add alerts to session for display
	foreach ($errors as $error) addSessionAlert($error);
}
		
//		if(count($errors) == 0) 
//		{
//			if($emailActivation)
//			{
//				$message = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE2");
//			}
//			else
//			{
//				$message = lang("ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
//			}
//		}

//--------------------------------------------------------------------
// RENDER FORM
	
	// Depeding on portal_config, make the username block
	$username_block = $validation_rules = '';
	if (!$portal_config['useEmailAsUsername'])
	{
		$username_block = '
					<div class="form-group">
						<div class="col-xs-4 form-label"/>
							<label for="username" class="control-label">Username:</label>
						</div>
						<div class="col-xs-8">
							<input type="text" class="form-control" name="username" id="username" placeholder="Username" />
						</div>
					</div>';
		$validation_rules = '
			username: {
				required: true
			},';
	}
	
	$regPanel = new bootstrapPanel();
	$regPanel->setType('primary');
	$regPanel->setIcon('user');
	$regPanel->setHeader('<span class="headerText">Register</span>');
	$regPanel->setBody('
					<div class="mb-10">
						Create an account.
					</div>
					<div class="form-group">
						<div class="col-xs-4 form-label"/>
							<label for="email" class="control-label">Email:</label>
						</div>
						<div class="col-xs-8">
							<input type="email" class="form-control" name="email" id="email" placeholder="Email Address" autofocus />
						</div>
					</div>' . $username_block .'
					<div class="form-group">
						<div class="col-xs-4 form-label"/>
							<label for="password" class="control-label">Password:</label>
						</div>
						<div class="col-xs-8">
							<input type="password" class="form-control" name="password" id="password" placeholder="Password" />
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-4 form-label"/>
							<label for="password_again" class="control-label text-nowrap">Confirm:</label>
						</div>
						<div class="col-xs-8">
							<input type="password" class="form-control" name="password_again" id="password_again" placeholder="Password Again" />
						</div>
					</div>
					<div class="g-recaptcha g-recaptcha-right" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div>
					');
	$regPanel->setFooter('
					<div class="text-right">
						<input type="submit" class="btn btn-default" name="submit_new_user" id="submitNewUser" value="Register" />
					</div>'
	);


// RENDER Password Reset Request
########### CREATE PAGE/FORMS ###########
$page = new htmlPage("Register | $websiteName");
$page->printStart();
require_once("navbar.php");
?>
<div class='container'>
	<div class="row">
		<div class="max-600">
			<?php	print getSessionMessages(); ?>
			<div class="max-400">
				<form id="newUserForm" name="newUser" class="form-horizontal" action="" method="post">
					<?php print $regPanel->getPanel() ?>
				</form>
			</div>
			<div class="text-center">
				<a href="login.php">Login</a> / 
				<a href="forgot-password.php">Forgot Password?</a> / 
				<a href="<?php echo $websiteUrl; ?>">Home Page</a>
			</div>
		</div>
	</div>
</div>
<script type='text/javascript'>
	$('#newUserForm').validate({
		rules: {
			<?php echo $validation_rules ?>
			email: {
				required: true,
				email: true
			},
			password: {
				required: true,
				minlength: <?php echo PASSWORD_MIN_LENGTH ?>
			},
			password_again: {
				equalTo: "#password"
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


/*

			<div class="clear"></div>
            <p style="margin-top:30px; text-align:center;"><a href="login.php">Login</a> / <a href="forgot-password.php">Forgot Password?</a> / <a href="<?php echo $websiteUrl; ?>">Home Page</a></p>

</body>
</html>
*/