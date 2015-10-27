<?php 

	require_once("models/config.php");
	
	requireUserAccount();	// Must have an account but it can be inactive
	
	//FOR TESTING PURPOSES!!!  redownload user settings at each page load
	//$loggedInUser->refreshUser();
	
	global $portal_config;
	$errors = array();
	
	
########### HANDLE INCOMING GET/POST REQUESTS FIRST ###########
//--------------------------------------------------------------------
	// EMAIL ACTIVATION LINK
	if( isset($_GET['activation']) && !empty($_GET['activation']) )
	{
		if( $loggedInUser->isEmailTokenValid($_GET['activation']) )
		{
			// Verify email address
			$loggedInUser->setEmailVerified();
			$loggedInUser->refreshUser();
			logIt("Verified Email","DEBUG");
			addSessionMessage("Email account verified", 'success');
		}
		else
		{
			// Invalid token match
			logIt("Tried to use an invalid email verification token: " . $_GET['token'],"DEBUG");
			$errors[] = "The supplied email activation token is invalid or expired.  This can happen if you regenerated a new token but followed the link from an older request.";
			addSessionAlert("Invalid email activation token");
		}
	} // End Email Activation Link
	
	
	// Look for POST requesting Activation Email be re-sent
	if( isset($_POST['resend_activation']) )
	{
		$activation_last_sent = $loggedInUser->getMinSinceLastActivationEmail();
		if( $activation_last_sent < EMAIL_ACTIVATION_EXPIRY )
		{
			addSessionAlert("Your last activation email was sent $activation_last_sent min ago.  Please wait more than " . EMAIL_ACTIVATION_EXPIRY . " min between trying to send another email");
		}
		else
		{
			// Create a new token
			$loggedInUser->createEmailToken();
			
			// Send email
			$loggedInUser->emailEmailToken();
			
			// By setting this to null, it will resend a new email
			addSessionMessage("A new email activation token has been sent", 'notice');
		}
	}
	
//--------------------------------------------------------------------
	// PASSWORD RESET QUESTIONS AND ANSWERS
	
	// Save updated security settings
	if( isset($_POST['update_password_reset']) )
	{
		//global $password_reset_pairs;
		$password_reset_data = array();
		$all_valid = true;
		foreach ($password_reset_pairs as $i => $pair)
		{
			$q = isset($_POST[$pair['question']]) ? $_POST[$pair['question']] : null;
			$a = isset($_POST[$pair['answer']]) ? $_POST[$pair['answer']] : null;
			$password_reset_data[$i]['question'] = $q;
			$password_reset_data[$i]['answer'] =  $a;
		
			if (empty($q) || empty($a))
			{
				// Invalid responses
				addSessionAlert("Invalid password reset values for question $i");
				$all_valid = false;
			}
			else
			{
				$a = hashSecurityAnswer($a);
				$loggedInUser->updatePasswordReset($pair['question'], $pair['answer'], $q, $a);
			}
		}
		if ($all_valid) addSessionMessage("Password recovery questions updated",'success');
	}
	
//--------------------------------------------------------------------
// CHANGE PASSWORD
	
	// Save updated security settings
	if( isset($_POST['change_password']) )
	{
		logIt("Change Password Post", "DEBUG");
		
		$password = trim($_POST["password"]);
		$password_new = trim($_POST["password_new"]);
		$password_new_again = trim($_POST["password_new_again"]);
		$valid = true;
		
		if($password == "")
		{
			addSessionAlert( lang("ACCOUNT_SPECIFY_PASSWORD") );
			logIt("Change Password: Blank Password", "INFO");
			$valid = false;
		}
		else if($password_new == "")
		{
			addSessionAlert( lang("ACCOUNT_SPECIFY_NEW_PASSWORD") );
			logIt("Change Password: Blank New Password", "INFO");
			$valid = false;
		}
		else if(minMaxRange(PASSWORD_MIN_LENGTH,50,$password_new))
		{	
			addSessionAlert( lang("ACCOUNT_NEW_PASSWORD_LENGTH", array(PASSWORD_MIN_LENGTH, 50)) );
			logIt("Change Password: Invalid New Password", "INFO");
			$valid = false;
		}
		else if($password_new != $password_new_again)
		{
			addSessionAlert( lang("ACCOUNT_PASS_MISMATCH") );
			logIt("Change Password: New and New Again Mismatch", "INFO");
			$valid = false;
		}
		else if($password_new == $password)
		{
			addSessionAlert( lang("NOTHING_TO_UPDATE") );
			$valid = false;
		}
		
		//End data validation
		if( $valid )
		{
         $salt = $loggedInUser->getSalt();
         logIt("Salt is: $salt","DEBUG");
			//Confirm the hash's match before updating a users password
			$entered_pass_hash = generateHash($password,$salt);
         logIt("Old Hash is: $entered_pass_hash","DEBUG");
			
         //Make a new password from the existing salt
         $entered_pass_new = generateHash($password_new, $salt);
			//logIt("loggedInUsername: " . $loggedInUser->getUsername(), "DEBUG");
			//logIt("entered_pass_new:  $entered_pass_new", "DEBUG");
			//logIt("current_pass_hash: " . $loggedInUser->getPasswordHash(), "DEBUG");
		
			if($entered_pass_hash != $loggedInUser->getPasswordHash())
			{
				//No match
				logIt("Change Password: current password does not match","DEBUG");
				addSessionAlert( lang("ACCOUNT_PASSWORD_INVALID") );
				$valid = false;
			}
			else
			{
				// Check that things are still good so we should update the password
				if ($valid)
				{
					//This function will update the hash_pw property.
					logIt('Password Updated','DEBUG');
					$loggedInUser->updatePassword($entered_pass_new);
					$success = true;
					addSessionMessage("Password Updated","success");
				}
			}
		}
		else
		{
			logIt("Change Password: Invalid Request", "INFO");
		}
	} // $_POST['change_password']


//--------------------------------------------------------------------
	// PART 1: EMAIL ACTIVATION
	$emailPanel = new bootstrapPanel();
	
	if ($loggedInUser->isEmailVerified())
	{
		// Render success panel
		$emailPanel->setType('primary');
		$emailPanel->setIcon('envelope');
		$emailPanel->setHeader('<span class="headerText">Email Address Verified</span>');
		$emailPanel->setBody("The email address " . $loggedInUser->getEmail() . " was verified on " . $loggedInUser->email_verified_ts);
	}
	else
	{
		// Create a verification token if empty
		$emailPanel->setType('danger');
		$emailPanel->setIcon('exclamation-sign');
		$emailPanel->setHeader('<span class="headerText">Email has not been verified</span>');
		
		if( !$loggedInUser->isEmailTokenSet() )
		{
			$loggedInUser->createEmailToken();
			logIt("Created Email activation token","DEBUG");
			addSessionMessage("Email activation token created");
		}
		
		// Email token or display option to email
		if ( empty($loggedInUser->email_act_sent_ts) )
		{
			// Activation email was never sent - send one now
			$loggedInUser->emailEmailToken();
			if($loggedInUser->mail_failure) {
				addSessionAlert("An error occurred emailing the activation token.");
				logIt("Error creating/sending email verification token","ERROR");
				$emailPanel->setBody("An error occurred emailing the verification token.");
			} else {
				addSessionMessage("An email was just sent to " . $loggedInUser->getEmail(), 'info');
				logIt("Sent Email verification token","DEBUG");
				$emailPanel->setBody('An email was just sent to <i>' . $loggedInUser->getEmail() . '</i>.  Please wait a few moments, then check your inbox (including spam/junk folders).  Open the activation link in the email to enable your account.');
			}
		}
		// Activation email was previously sent
		else
		{
			$emailPanel->setBody("
				<div>
					An email verification link was sent on " . $loggedInUser->email_act_sent_ts . ".  Please check your email, 
					including spam and junk folders for the message and click through the activation link.
				</div>
				<div>
					If you are unable to locate the email, you can resend a new link.
				</div>");
			$emailPanel->setFooter("
				<div class='text-right'>
					<input type='submit' class='btn btn-default' name='resend_activation' value='Send New Verification Code' />
				</div>");
		}
	}
	$emailHtml = "
		<form name='emailVerification' action='' method='post'>" .
		$emailPanel->getPanel() .
		"</form>";
	// END PART 1: EMAIL VERIFICATION
	
	
//--------------------------------------------------------------------
	// PART 2: SECURITY RECOVERY QUESTIONS
	$securityHtml = '';
	$securityPanel = new bootstrapPanel();
	// Security Questions complete
	if( $loggedInUser->isPasswordRecoveryConfigured() )
	{
		$securityPanel->setType('primary');
		$securityPanel->setIcon('ok-sign');
		$securityPanel->setHeader('<span class="headerText">Password Recovery Questions Configured</span>');
		$securityPanel->setBody('Your ' . count($password_reset_pairs) . ' password recovery questions have been configured.  In the event you were to forget your password, you will be required to answer these questions to regain access to your account.');
		$securityHtml = $securityPanel->getPanel();
	}
	// Security Questions need to be done
	else
	{
		// Build list of template questions
		$options = '';
		foreach ($template_security_questions as $k => $v)
		{
			$options .= "<li><a href='javascript:'>$v</a></li>";
		}
				
		// Build html for each question/answer pair
		$html = array();
		foreach ($password_reset_pairs as $i => $pair)
		{
			$html[] = '
				<div class="mb-10">
					<h5>Please enter/select a password recovery question '.$i.':</h5>
						<div class="input-group">
							<span class="input-group-addon" id="sizing-addon3"><div style="width:15px;">Q:</div></span>
							<input type="text" placeholder="Password Recovery Question '.$i.'" class="form-control" aria-label="password recovery question '.$i.'" name="'.$pair['question'].'" id="'.$pair['question'].'" value="'.$loggedInUser->$pair['question'].'">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1" aria-expanded="false">
									Templates <span class="caret"></span>
								</button>
								<ul class="dropdown-menu dropdown-menu-right pass_reset_questions" role="menu">
									' . $options . '
								</ul>
							</div><!-- /btn-group -->
						</div><!-- /input-group -->
				</div>
				<div>
					<!--h5>Please enter your answer to the question '.$i.':</h5-->
					<div class="input-group">
						<span class="input-group-addon" id="sizing-addon3"><div style="width:15px;">A:</div></span>
						<input type="text" placeholder="Password Recovery Answer '.$i.'" class="form-control" aria-label="password recovery answer '.$i.'" name="'.$pair['answer'].'" id="'.$pair['answer'].'" value="' . $loggedInUser->$pair['answer'] . '">
					</div><!-- /input-group -->
				</div>';
		}
		
		$securityPanel->setType('danger');
		$securityPanel->setIcon('exclamation-sign');
		$securityPanel->setHeader('<span class="headerText">Password recovery questions not configured</span>');
		$securityPanel->setBody('
			<div>
				Should you ever need to recover a lost or forgotten password, you must provide the answers to 
				a set of security questions. You may create your own questions or select from a number of templates.
			</div>
			<div class="pass_q_and_a" style="display:none;">
				<div class="container">
					A good security question is one:
					<ul>
						<li>you wil not forget</li>
						<li>no one else knows</li>
						<li>can not be easily obtained / found by searching</li>
						<li>one that will not change over time</li>
					</ul>
				</div>
				The answers are not case sensitive but must otherwise be an exact match.
				' . implode("<hr>",$html) . '
			</div>'
		);
		$securityPanel->setFooter('
			<div class="text-right">
				<button type="button" class="btn btn-default" onclick="' . "javascript:$(this).hide();$('.pass_q_and_a').show(400);" . '"/>Configure Questions</button>
				<input type="submit" class="btn btn-default pass_q_and_a" style="display:none;" name="update_password_reset" value="Save" />
			</div>'
		);
		
		$securityHtml = '
			<form role="form" action="profile.php" method="POST" id="updatePasswordResetForm" name="updatePasswordResetForm">
				' . $securityPanel->getPanel() . '
			</form>' . "
			<script type='text/javascript'>
				$( document ).ready(function() {
					// Select a template question from the dropdown
					$('.pass_reset_questions li a').click(function(e){
						$(this).closest('.input-group').find('input').val(this.text);
						e.preventDefault();
					});
				});
				
				jQuery.validator.addMethod('notEqualTo',
					function(value, element, param) {
						var notEqual = true;
						value = $.trim(value);
						for (i = 0; i < param.length; i++) {
							if (value == $.trim($(param[i]).val())) { notEqual = false; }
						}
						return this.optional(element) || notEqual;
					},
					'Each question must be different.'
				);
				
				// Validate
				$('#updatePasswordResetForm').validate({
					rules: {
						pass_reset_question: {
							required: true,
							notEqualTo: ['#pass_reset_question2', '#pass_reset_question3']
						},
						pass_reset_answer: {
							required: true
						},
						pass_reset_question2: {
							required: true,
							notEqualTo: ['#pass_reset_question', '#pass_reset_question3']
						},
						pass_reset_answer2: {
							required: true
						},
						pass_reset_question3: {
							required: true,
							notEqualTo: ['#pass_reset_question', '#pass_reset_question2']
						},
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
			</script>";	// securityHtml
		//include(PORTAL_INC_PATH . '/password_recovery_questions_form.php');
	} //Security Questions need to be done
	// END PART 2: SECURITY RECOVERY QUESTIONS
	
//--------------------------------------------------------------------
// PART 3: CHANGE PASSWORD
$passPanel = new bootstrapPanel();
$passPanel->setType('primary');
$passPanel->setIcon('lock');
$passPanel->setHeader('<span class="headerText">Change Password</span>');
$passPanel->setBody('
	<div>
		<div class="form-group">
			<label for="password" class="control-label col-xs-4">Current Password:</label>
			<div class="col-xs-8">
				<input type="password" class="form-control" name="password" id="password" placeholder="Current Password" />
			</div>
		</div>
		
		<div class="form-group">
			<label for="password_new" class="control-label col-xs-4">New Password:</label>
			<div class="col-xs-8">
				<input type="password" class="form-control" name="password_new" id="password_new" placeholder="New Password" />
			</div>
		</div>

		<div class="form-group">
			<label for="password_new_again" class="control-label col-xs-4">New Password Again:</label>
			<div class="col-xs-8">
				<input type="password" class="form-control" name="password_new_again" id="password_new_again" placeholder="Confirm New Password" />
			</div>
		</div>
	</div>
');
$passPanel->setFooter('
	<div class="text-right">
		<input type="submit" class="btn btn-default" name="change_password" value="Change Password" />
	</div>'
);
$passChangeHtml = '
	<form role="form" class="form-horizontal" action="profile.php" method="POST" id="changePasswordForm" name="changePasswordForm">
		' . $passPanel->getPanel() . '
	</form>' . "
	<script type='text/javascript'>
		// Validate
		$('#changePasswordForm').validate({
			rules: {
				password: {
					required: true
				},
				password_new: {
					required: true,
					minlength: ".PASSWORD_MIN_LENGTH."
				},
				password_new_again: {
					required: true,
					equalTo: '#password_new'
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
	</script>";	// changePasswordForm
// END PART 3: CHANGE PASSWORD
	




//--------------------------------------------------------------------
// FINAL CHECK TO SET OVERALL ACTIVE FLAG
$activePanel = new bootstrapPanel();


if( $loggedInUser->isEmailVerified() && $loggedInUser->isPasswordRecoveryConfigured() && !isUserActive() )
{
	// Mark user active
	$loggedInUser->setActive();
	addSessionMessage("Your profile is complete -- your account has been activated",'success');
}

// Render overall account activity
if( isUserActive() )
{
	$activePanel->setType('primary');
	$activePanel->setIcon('ok-sign');
	$activePanel->setHeader('<span class="headerText">Account Active</span>');
	$activePanel->setBody('
		<div>
			Your account is active.
		</div>'
	);
	$activePanel->setFooter('
		<div class="text-center">
			<a href="index.php">
				<button class="btn btn-default strong"><span class="">Goto My Home Page</span></button>
			</a>
		</div>'
	);
}
else
{
	$activePanel->setType('danger');
	$activePanel->setIcon('exclamation-sign');
	$activePanel->setHeader('<span class="headerText">Account is Not Active</span>');
	$activePanel->setBody('
		<div>
			Please complete all of the items on this page to fully activate your account.
		</div>'
	);
}
$activeHtml = $activePanel->getPanel();


########### CREATE PAGE/FORMS ###########
$page = new htmlPage("Profile | $websiteName");
$page->printStart();
require_once("navbar.php");

?>
<div class='container'>
	<div class="row">
		<div class="max-600">
<?php			
print getSessionMessages();
print $activeHtml;
print $emailHtml;
print $securityHtml;
print $passChangeHtml;
?>
		</div>
	</div>
</div>
<?php
$page->printEnd();
