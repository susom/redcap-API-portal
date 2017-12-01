<?php
require_once("models/config.php");

//Prevent the user visiting the lost password page if he/she is already logged in
if(isUserLoggedIn()) { 
	$destination = (isUserActive() ? $websiteUrl . "index.php" : $websiteUrl . "consent.php");
	header("Location: " . $destination);
	exit; 
}

$pass_reset_form 	= false;
$errors 			= array();

// THIS HANDLES SENDING EMAIL LINK AND CONFIRMATION LINK
if( !empty($_GET["confirm"]) ){
	$token 	= trim($_GET['confirm']);
	
	// Look up a matching user
	$user 	= getUserByPasswordToken($token);

	// Validation
	if($token == ""){
		// logIt("Invalid confirm password reset received: $token","DEBUG");
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	}elseif($user == false){
		// logIt("Unable to locate a valid user with the supplied token: $token");
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");		
	}elseif(!isPasswordResetActive($user)){
		$errors[] = "Password Reset Session is not active (may have timed out?)";
	}elseif(!$user->isPasswordRecoveryConfigured()){
		// logIt("Password reset attempt on account without recovery configured!", "ERROR");
		$errors[] = "Password Reset is not properly configured";
	}

	//THIS GOES STRAIGHT TO THE PASSWORD RESET FORM IF NO ERRORS
	if (count($errors) == 0){
		$pass_reset_form 	= true;
		$email 				= $user->email; 
	}else{
		foreach($errors as $error){
			addSessionAlert($error);
		}

		// redirect to the forget-password page without any current (invalid) tokens
		header("Location: login.php");
		exit;	
	}
}elseif( !empty($_POST['resetlink'])  ){
	// PASSWORD RESET EMAIL LINK REQUEST SUBMITTED
	$email 	= sanitize($_POST["forgotemail"]);
	$user 	= getUserByEmail($email);

	if(!empty($user) && !$user->isActive()){
		addSessionAlert("This account is not active yet.  Please check your email for an activation link.");
		
		$olduser = new RedcapPortalUser($user->user_id);
		//SEND NEW ACTIVATION LINK
		$olduser->updateUser(array(
			getRF("zip") 	=> $zip,
	        getRF("city") 	=> $city,
	        getRF("state") 	=> $state,
	        getRF("age") 	=> $actualage
	      ));
        $olduser->createEmailToken();
        $olduser->emailEmailToken();
		header("Location: login.php");
		exit;	
	}

	//Check for email
	if(trim($email) == ""){
		$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
	}elseif(!isValidemail($email) || !($user = getUserByEmail($email)) ){
		//Check regex to ensure email is in the correct format			
		//&& Lookup a valid user account by this email
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
		
	if(count($errors) == 0){
		if($_POST['resetlink'] == "emailme"){
			//SEND THEM AN EMAIL RESET LINK
			//Check if the user has any outstanding lost password requests
			if(isPasswordResetActive($user)){
				$errors[] = lang("FORGOTPASS_REQUEST_EXISTS", array(getPasswordTokenAgeInMin($user)));
			}else{
				// Generate a new password reset token
				$user->log_entry[] = "Initiated password reset process";
				$user->createPassResetToken();
				
				// Email Request	
				$mail 			= new userPieMail();
				$confirm_url 	= $websiteUrl."forgot_password.php?confirm=".$user->pass_reset_token;
				
				//Setup our custom hooks
				$hooks = array(
					"searchStrs" => array("#CONFIRM-URL#", "#USERNAME#"),
					"subjectStrs" => array($confirm_url, $user->firstname)
				);

				if(!$mail->newTemplateMsg("lost-password-request.txt",$hooks)){
					$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
				}else{
					if(!$mail->sendMail($user->email,"Lost password request")){
						$errors[] = lang("MAIL_ERROR");
					}else{
						//Update the DB to show this account has an outstanding request
						$user->log_entry[] = "Email sent to user to confirm/deny request";
						$user->updateUser(array(
							getRF('pass_reset_token') 	=> $user->pass_reset_token,
							getRF('pass_reset_req_ts') 	=> $user->pass_reset_req_ts
						));
						addSessionMessage( lang("FORGOTPASS_REQUEST_SUCCESS"), 'notice', true );
					} // Mail
				} // Template
			} // Reset Active

			header("Location: login.php");
			exit;	
		}else{
			//LET THEM PASS THROUGH TO THE SECURITY QUESTIONS OTHERWISE
			//WE PASS THROUGH $email var
		}
	}else{
		foreach($errors as $error){
			addSessionAlert($error);
		}

		header("Location: login.php");
		exit;	
	}
} 

// WE HAVE USERS $email IF IT GETS HERE SO LOAD THEIR QUESTIONS, IF NOT GET IT FROM THE OTHER TWO
if(!empty($_POST['submitPasswordResetAnswers']) || !empty($_POST['saveResetPassword'])){
	$email = (!empty($_POST['submitPasswordResetAnswers']) ? $_POST['submitPasswordResetAnswers'] : $_POST['saveResetPassword']);
}
$user 			= getUserByEmail($email);
$user_qs 		= array();
foreach ($password_reset_pairs as $i => $pair){
	$user_qs[$i] 	= $user->$pair["question"];
}

// THIS HANDLES SAVING NEW PASSWORDS AND CHECKING PASSWORD RECOVERY ANSWERS
if(!empty($_POST['submitPasswordResetAnswers'])){
	$correct 	= 0;
	$total 		= count($password_reset_pairs);
	$attempt 	= getSessionPassResetAttempt();

	logIt("Checking Password Reset answers - try $attempt","INFO");
	
	foreach ($password_reset_pairs as $i => $pair){
		$pass_reset_answer = isset($_POST[$pair['answer']]) ? trim($_POST[$pair['answer']]) : "";
		if(empty($pass_reset_answer)){
			$errors['a'] = "Invalid password recovery answers";
		}elseif(empty($user->$pair['answer'])){
			// Make sure answer is configured in the user object as a sanity check
			$errors['b'] = "Invalid password recovery configuration";
		}elseif($user->$pair['answer'] !== hashSecurityAnswer($pass_reset_answer)){
			// Compare the answers
			$errors['a'] = "Invalid password recovery answers";
			logIt("Question $i incorrect: ($pass_reset_answer) doesn't match stored hash", "INFO");
		}else{
			$correct++;
		}
	}

	// As a safety measure, make sure the number of questions designated to pass has been configured
	if (!$password_reset_pairs_min_correct) {
		$password_reset_pairs_min_correct = max(count($password_reset_pairs), 1);
	}
	
	// Sufficient answers correct to pass
	logIt ("$correct of $total were correct ($password_reset_pairs_min_correct required)", "DEBUG");
	
	if ($correct >= $password_reset_pairs_min_correct) {
		//QUESTIONS ANSWERED CORRECTLY , PASS THROUGH TO PASSWORD RESET FORM
		addSessionMessage('Identity verified - Set new password.','success');
		$pass_reset_form = true;
	}elseif ($attempt >= 3){
		// If still incorrect after 3 tries, then cancel and redirect
		$errors[] 			= "Too many incorrect attempts - password reset cancelled";
		$user->log_entry[] 	= "Too many incorrect password reset attempts.<br>Reset token has been invalidated.";
		$user->clearPasswordReset();
		clearSession();

		foreach($errors as $error) addSessionAlert($error);
		header("Location: login.php");
		exit;
	}else{
		// Just increment the attempt
		$attempt = incrementSessionPassResetAttempt();
	}

	// Continue and render passResetQuestions again for another attempt
	foreach($errors as $error){
		addSessionAlert($error);
	}
}

//THIS HANDLES SAVING NEW PASSWORDS
if(!empty($_POST['saveResetPassword'])){
	//validate and save - if okay, redirect to home page, if bad, clear session and make them start over?
	$password_new 		= trim($_POST["password_new"]);
	$password_new_again = trim($_POST["password_new_again"]);
	$valid 				= true;

	if($password_new == ""){
		$errors[] 	= lang("ACCOUNT_SPECIFY_NEW_PASSWORD");
		$valid 		= false;
	}elseif($password_new != $password_new_again){
		$errors[] 	= lang("ACCOUNT_PASS_MISMATCH");
		$valid 		= false;
	}

	//End data validation
	if( count($errors) == 0 ){
		$entered_pass_new = generateHash($password_new,$user->getSalt());
		
		//This function will update the hash_pw property.
		$user->updatePassword($entered_pass_new);
		$user->clearPasswordReset();
		unset($_SESSION[SESSION_NAME]['login_attempts']);
		addSessionMessage("Your password has been reset - please login","success");
		
		// Redirect to main website page
		header("Location: login.php"); 
		exit;
	}else{
		// There were errors.  Let's be harsh and cancel the entire reset session.
		$user->clearPasswordReset();
		clearSession();

		foreach($errors as $error){
			addSessionAlert($error);
		}

		//PASS THROUGH TO SHOW PASSWORD RESET FORM AGAIN
		$pass_reset_form = true;
	}
}


$pg_title 		= lang("FORGOTPASS_RESET"). " | $websiteName";
$body_classes 	= "login register reset";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
	<div id="main-content" class="col-md-8 col-md-offset-2 pwreset" role="main">
		<div class="well row">
			<form role="form" class="form-horizontal" action="forgot_password.php" method="POST" id="pwResetForm" name="pwResetForm">
				<h2><?php echo lang("FORGOTPASS_RESET_FORM") ?> <?php echo ($pass_reset_form ? "" : ": " . lang("FORGOTPASS_PLEASE_ANSWER") )?></h2>
				<?php 

				if($pass_reset_form){
					//PASSWORD RESET FORM ONLY
					echo "<input type='hidden' name='saveResetPassword' value='$email'/>";

					include("models/inc/set_pw.php");
				}else{
					//SECURITY QUESTIONS FORM INSTEAD
					echo "<input type='hidden' name='submitPasswordResetAnswers' value='$email'/>";

					// Build html for each question/answer pair
					foreach ($password_reset_pairs as $i => $pair){
					?>
						<div class="form-group">
							<label for="<?php echo $pair["question"] ?>" class="control-label col-sm-3"><?php echo lang("FORGOTPASS_SEC_Q") ?> <?php echo $i  ?>:</label>
							<div class="col-sm-8">
								<p><?php echo ($i == 3 ? $user_qs[$i] : $template_security_questions[$user_qs[$i]]); ?></p>
								<input type="text" placeholder="<?php echo lang("FORGOTPASS_RECOVERY_ANSWER") ?>" class="form-control" aria-label="password recovery answer" name="<?php echo $pair["answer"] ?>" id="<?php echo $pair["answer"] ?>">
							</div>
						</div>
					<?php
					}
				}
				?>

				<div class="form-group">
			      <span class="control-label col-sm-3"></span>
			      <div class="col-sm-8"> 
			        <button type="submit" class="btn btn-success" value="true"><?php echo lang("GENERAL_SUBMIT") ?></button>
			      </div>
			    </div>
			</form>
	  	</div>
	</div>
  </div>
</div>
<script>
$('#pwResetForm').validate({
	rules: {
		password_new: {
			required: true,
			minlength: "<?php echo PASSWORD_MIN_LENGTH ?>"
		},
		password_new_again: {
			required: true,
			equalTo: '#password_new'
		},
		// pass_reset_question: {
		// 	required: true,
		// 	// notEqualTo: ['#pass_reset_question2', '#pass_reset_question3']
		// },
		pass_reset_answer: {
			required: true
		},
		// pass_reset_question2: {
		// 	required: true,
		// 	notEqualTo: ['#pass_reset_question', '#pass_reset_question3']
		// },
		pass_reset_answer2: {
			required: true
		},
		// pass_reset_question3: {
		// 	required: true,
		// 	notEqualTo: ['#pass_reset_question', '#pass_reset_question2']
		// },
		pass_reset_answer3: {
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
include("models/inc/gl_footer.php");
?>




	


