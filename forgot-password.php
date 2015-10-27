<?php
/*
*	Forgotten password reset
*
*	Step 1) User enters in valid email and a confirmation email is sent
*	Step 2) User confirmsUpon confirmation email approval, user must asnwer questions
*	Step 3) A new password is created and saved
*
*/

require_once("models/config.php");

//Prevent the user visiting the lost password page if he/she is already logged in
if(isUserLoggedIn()) {
	logIt("Trying to access forgot-password.php site - redirecting", "DEBUG");
	header("Location: profile.php"); die();
}

logIt("At Password Reset", "DEBUG");
$errors = array();

// STEP 1: PROCESS CONFIRMATION TOKEN PRESENT IN THE URL - THIS IS REQUIRED FOR ALL RESET STEPS
//----------------------------------------------------------------------------------------------
if( !empty($_GET["confirm"]) )
{
	$token = trim($_GET['confirm']);
	
	// Look up a matching user
	$user = getUserByPasswordToken($token);
	
	// Validation
	if ($token == "")
	{
		logIt("Invalid confirm password reset received: $token","DEBUG");
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	}
	elseif ($user == false)
	{
		logIt("Unable to locate a valid user with the supplied token: $token");
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");		
	}
	elseif (!isPasswordResetActive($user))
	{
		$errors[] = "Password Reset Session is not active (may have timed out?)";
	}
	elseif (!$user->isPasswordRecoveryConfigured())
	{
		logIt("Password reset attempt on account without recovery configured!", "ERROR");
		$errors[] = "Password Reset is not properly configured";
	}
	
	if (count($errors) == 0)
	{
		// With a confirmed token/user we have three steps:
		//		1) Render Password Reset Questions / Answers
		//		2) Process Answers and Render Change Password Form or Retry
		//		3) Process Change Password
		//
		//--------------------------------------------------------------------------------------------
		// STEP 1: Render Password Reset Questions / Answers
		if ($_SERVER['REQUEST_METHOD'] == 'GET') 
		{
			//logIt("Starting phase 1 of password reset");
			foreach($errors as $error) addSessionAlert($error);
			include PORTAL_INC_PATH . "/password_reset_qa.php";
			die();
		}
		//--------------------------------------------------------------------------------------------
		// STEP 2: Process Answers and Render Change Password Form or Retry
		if(!empty($_POST['submitPasswordResetAnswers']))
		{
			// TBD - add some sort of CSRF token to this form
			$correct = 0;
			$total = count($password_reset_pairs);
			$attempt = getSessionPassResetAttempt();
			
			logIt("Checking Password Reset answers - try $attempt","INFO");
			
			foreach ($password_reset_pairs as $i => $pair)
			{
				$pass_reset_answer = isset($_POST[$pair['answer']]) ? trim($_POST[$pair['answer']]) : "";
				if(empty($pass_reset_answer))
				{
					$errors['a'] = "Invalid password recovery answers";
				}
				// Make sure answer is configured in the user object as a sanity check
				elseif(empty($user->$pair['answer']))
				{
					$errors['b'] = "Invalid password recovery configuration";
				}
				// Compare the answers
				elseif($user->$pair['answer'] !== hashSecurityAnswer($pass_reset_answer))
				{
					$errors['a'] = "Invalid password recovery answers";
					logIt("Question $i incorrect: ($pass_reset_answer) doesn't match stored hash", "INFO");
				}
				else
				{
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
				// SUCCESS: Render form to reset password
				addSessionMessage('Identity verified','success');
				include PORTAL_INC_PATH . "/password_reset_set_pass.php";
				die();
			}
			// If still incorrect after 3 tries, then cancel and redirect
			elseif ($attempt >= 3)
			{
				$errors[] = "Too many incorrect attempts - password reset cancelled";
				$user->log_entry[] = "Too many incorrect password reset attempts.<br>Reset token has been invalidated.";
				$user->clearPasswordReset();
				clearSession();
				foreach($errors as $error) addSessionAlert($error);
				header("Location: forgot-password.php");die();
			}
			// Just increment the attempt
			else
			{
				$attempt = incrementSessionPassResetAttempt();
			}
			// Continue and render passResetQuestions again for another attempt
			foreach($errors as $error) addSessionAlert($error);
			include PORTAL_INC_PATH . "/password_reset_qa.php";
			die();
		} // POST: submitPasswordResetAnswers
		//--------------------------------------------------------------------------------------------
		// STEP 3: Process Change Password
		elseif(!empty($_POST['saveResetPassword']))
		{
			//validate and save - if okay, redirect to home page, if bad, clear session and make them start over?
			logIt("Starting step 3: Processing saveResetPassword", "DEBUG");
		
			$password_new = trim($_POST["password"]);
			$password_new_again = trim($_POST["password_again"]);
			$valid = true;
		
			if($password_new == "")
			{
				//addSessionAlert( lang("ACCOUNT_SPECIFY_NEW_PASSWORD") );
				$errors[] = lang("ACCOUNT_SPECIFY_NEW_PASSWORD");
				$valid = false;
			}
			else if(minMaxRange(PASSWORD_MIN_LENGTH,50,$password_new))
			{	
				//addSessionAlert( lang("ACCOUNT_NEW_PASSWORD_LENGTH", array(PASSWORD_MIN_LENGTH, 50)) );
				//logIt("Change Password: Invalid New Password", "INFO");
				//$valid = false;
				$errors[] = lang("ACCOUNT_NEW_PASSWORD_LENGTH", array(PASSWORD_MIN_LENGTH, 50));
			}
			else if($password_new != $password_new_again)
			{
				//addSessionAlert( lang("ACCOUNT_PASS_MISMATCH") );
				//logIt("Change Password: New and New Again Mismatch", "INFO");
				//$valid = false;
				$errors[] = lang("ACCOUNT_PASS_MISMATCH");
			}
		
			//End data validation
			if( count($errors) == 0 )
			{
				$entered_pass_new = generateHash($password_new,$user->getSalt());
				
				//This function will update the hash_pw property.
				$user->updatePassword($entered_pass_new);
				$user->clearPasswordReset();
				addSessionMessage("Your password has been reset - please login","success");
				
				// Redirect to main website page
				header("Location: login.php"); die();
			}
			else
			{
				// There were errors.  Let's be harsh and cancel the entire reset session.
				$user->clearPasswordReset();
				clearSession();
				foreach($errors as $error) addSessionAlert($error);
				addSessionAlert("Error processing password reset - cancelling reset.<br>You must start over from the beginning.");
				
				//logIt("Reset Forgotten Password Errors: " . print_r($errors,true), "DEBUG");
				//include "inc/password_reset_page.php";
				header("Location: forgot-password.php");
				die();
			}
		} // $_POST['saveResetPassword']
	}
	else
	{
		foreach($errors as $error) addSessionAlert($error);
		// redirect to the forget-password page without any current (invalid) tokens
		header("Location: forgot-password.php"); die();
	}
}	// GET: confirm
// USER DENIED PASSWORD RESET REQUEST
//----------------------------------------------------------------------------------------------
elseif( !empty($_GET["deny"]) )
{
	$token = trim($_GET["deny"]);
	
	// Look up a matching user
	$user = getUserByPasswordToken($token);
	if ($token == "" || $user === false)
	{
		$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
	}
	else
	{
		logIt("Cleared password reset based on DENY", "DEBUG");
		$user->log_entry[] = "Received DENY on password request.";
		$user->clearPasswordReset();
		addSessionMessage( lang("FORGOTPASS_REQUEST_CANNED") );
		
		// Redirect to index page
		header("Location: index.php");
		die();
	}
} // GET: deny
// PASSWORD RESET REQUEST SUBMITTED
//----------------------------------------------------------------------------------------------
elseif( !empty($_POST['new_pass_reset_request']) )
{
	logIt("Handling a new request...","DEBUG");
	$email = sanitize($_POST["email"]);

	//Perform some validation
	
	// Verify reCaptcha
	$reCaptcha = verifyReCaptcha();
	if ($reCaptcha['success'] != true) {
		$errors[] = "Invalid reCaptcha response - please try again.";
		logIt("Invalid reCaptcha in forgot-password with $email: ". implode(','. $reCaptcha['
			error-codes']), "INFO");
	}
	
	//Check for email
	if(trim($email) == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
	}
	
	//Check regex to ensure email is in the correct format
	elseif(!isValidemail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	
	// Lookup a valid user account by this email
	elseif(!($user = getUserByEmail($email)) )
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	
	if(count($errors) == 0)
	{
		//Check if the user has any outstanding lost password requests
		if(isPasswordResetActive($user))
		{
		
			logIt("A still valid " . getPasswordTokenAgeInMin($user) . " min old request exists", "DEBUG");
			$errors[] = lang("FORGOTPASS_REQUEST_EXISTS", array(getPasswordTokenAgeInMin($user)));
		}
		else
		{
			// Generate a new password reset token
			logIt("Reset password reset token","DEBUG");
			$user->log_entry[] = "Initiated password reset process";
			$user->createPassResetToken();
			
			// Email Request	
			$mail = new userPieMail();
			
			$confirm_url = lang("CONFIRM")."\n".$websiteUrl."forgot-password.php?confirm=".$user->pass_reset_token;
			$deny_url = ("DENY")."\n".$websiteUrl."forgot-password.php?deny=".$user->pass_reset_token;
			
			//Setup our custom hooks
			$hooks = array(
				"searchStrs" => array("#CONFIRM-URL#","#DENY-URL#","#USERNAME#"),
				"subjectStrs" => array($confirm_url,$deny_url,$user->username)
			);
			
			if(!$mail->newTemplateMsg("lost-password-request.txt",$hooks))
			{
				$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
			}
			else
			{
				if(!$mail->sendMail($user->email,"Lost password request"))
				{
					$errors[] = lang("MAIL_ERROR");
				}
				else
				{
					//Update the DB to show this account has an outstanding request
					$user->log_entry[] = "Email sent to user to confirm/deny request";
					$user->updateUser(array(
						getRF('pass_reset_token') => $user->pass_reset_token,
						getRF('pass_reset_req_ts') => $user->pass_reset_req_ts
					));
					addSessionMessage( lang("FORGOTPASS_REQUEST_SUCCESS"), 'success', true );
				
					// Redirect to index page
					header("Location: index.php"); die();
				} // Mail
			} // Template
		} // Reset Active
	} // End error-free section
	// Add errors messages to session
	foreach ($errors as $error) addSessionAlert($error);
} // POST: new_pass_reset_request

$emailForm = new bootstrapPanel();
$emailForm->setType('primary');
$emailForm->setIcon('lock');
$emailForm->setHeader('<span class="headerText">Password Recovery</span>');
$emailForm->setBody('
			<form name="newLostPass" class="form-horizontal" action="" method="post">
				<div class="mb-10">
					To begin the password recovery process, please enter your account email address.
				</div>
				<input type="email" class="form-control text-center" name="email" id="email" placeholder="Enter Email Address" autofocus />
				<br>
				<div class="g-recaptcha g-recaptcha-center" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div>'
);
$emailForm->setFooter('
				<div class="text-right">
					<input type="submit" class="btn btn-default" name="new_pass_reset_request" id="newfeedform" value="Forgot Password" />
				</div>
			</form>'
);



// RENDER Password Reset Request
########### CREATE PAGE/FORMS ###########
$page = new htmlPage("Forgot Password | $websiteName");
$page->printStart();
require_once("navbar.php");

?>
<div class='container'>
	<div class="row">
		<div class="max-600">
			<?php	print getSessionMessages(); ?>
			<div class="max-400">
				<?php print $emailForm->getPanel() ?>
			</div>
		</div>
	</div>
</div>
<?php
$page->printEnd();


