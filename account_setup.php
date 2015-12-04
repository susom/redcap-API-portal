<?php 
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
// if(isUserLoggedIn()) { 
// 	$destination = (isUserActive() ? $websiteUrl . "dashboard/index.php" : $websiteUrl . "consent.php");
// 	header("Location: " . $destination);
// 	exit; 
// }

//--------------------------------------------------------------------
// PASSWORD RESET QUESTIONS AND ANSWERS

// Save updated security settings
if( isset($_POST['account_update']) ) {
	//NEW PASSWORD!
	$password_new 		= trim($_POST["password_new"]);
	$password_new_again = trim($_POST["password_new_again"]);
	$valid 				= true;

	if($password_new != $password_new_again) {
		addSessionAlert( lang("ACCOUNT_PASS_MISMATCH") );
		$valid = false;
	} 
	
	//End data validation
	if( $valid ) {
		$salt = $loggedInUser->getSalt();

		//Make a new password from the existing salt
		$entered_pass_new = generateHash($password_new, $salt);

		// Check that things are still good so we should update the password
		if ($valid) {
			//This function will update the hash_pw property.
			$loggedInUser->updatePassword($entered_pass_new);
			addSessionMessage("Password Updated","success");
		}
	} else {
		logIt("Change Password: Invalid Request", "INFO");
	}

	//PASSWORD SECURITY QUESTIONS
	$password_reset_data 	= array();
	$all_valid 				= true;
	foreach ($password_reset_pairs as $i => $pair) {
		$q 										= isset($_POST[$pair['question']]) 	? $_POST[$pair['question']] : null;
		$a 										= isset($_POST[$pair['answer']]) 	? $_POST[$pair['answer']] : null;
		$password_reset_data[$i]['question'] 	= $q;
		$password_reset_data[$i]['answer'] 		= $a;
	
		if (empty($q) || empty($a)) {
			// Invalid responses
			addSessionAlert("Invalid password reset values for question $i");
			$all_valid = false;
		} else {
			$a = hashSecurityAnswer($a);
			$loggedInUser->updatePasswordReset($pair['question'], $pair['answer'], $q, $a);
		}
	}
	if ($all_valid) {
		addSessionMessage("Password recovery questions updated!",'success');
	}

	if( $valid && $all_valid ) {
		//REDIRECT TO THE DASHBOARD
		include("models/surveys.php");

		header("Location: dashboard/survey.php?url=". urlencode($surveys[0][3]) ); //survey link of first survey
		exit;
	}
} 


$pg_title 		= "Account Setup | $websiteName";
$body_classes 	= "login register setup";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
	<div id="main-content" class="col-md-8 col-md-offset-2 accountSetup" role="main">
		<div class="well row">
			<form role="form" class="form-horizontal" action="account_setup.php" method="POST" id="accountSetupForm" name="accountSetupForm">
				<h2>Please setup your password and security questions:</h2>
				<?php 
					include("models/inc/set_pw.php");
				?>
				
				<div class="form-group">
					<span class="control-label col-sm-3"></span>
					<div class="col-sm-8">
					<p>Should you ever need to recover a lost or forgotten password, you must provide the answers to a set of security questions.</p>
					</div>
				</div>
				<?php 
				$options = '<option>Choose a question from the list</option>\n';
				foreach ($template_security_questions as $k => $v){
					if(!empty($v)){
						$options .= "<option value=\"$k\">$v</option>\n";
					}
				}
						
				// Build html for each question/answer pair
				foreach ($password_reset_pairs as $i => $pair){
				?>
					<div class="form-group">
						<label for="sec_q<?php echo $i ?>" class="control-label col-sm-3">Security Question <?php echo $i ?>:</label>
						<div class="col-sm-8">
							<?php 
								if($i == 3){
							?>
									<input type="text" name="<?php echo $pair['question'] ?>" class="form-control" id="<?php echo $pair['question'] ?>" placeholder="Write a custom security question"/>
							<?php
								}else{
							?>
									<select name="<?php echo $pair['question'] ?>" class="form-control" id="<?php echo $pair['question'] ?>">
									<?php echo $options ?>
									</select>
							<?php 
								}
							?>
							<input type="text" placeholder="Password Recovery Answer" class="form-control" aria-label="password recovery answer" name="<?php echo $pair['answer'] ?>" id="<?php echo $pair['answer'] ?>" value="<?php echo $loggedInUser->$pair['answer'] ?>">
						</div><!-- /input-group -->
					</div>
					<?php
				}
				?>

				<div class="form-group">
			      <span class="control-label col-sm-3"></span>
			      <div class="col-sm-8"> 
			        <button type="submit" class="btn btn-success" value="true">Submit</button>
			        <input type="hidden" name="account_update" value="true"/>
			      </div>
			    </div>
			</form>
	  	</div>
	</div>
  </div>
</div>
<script type='text/javascript'>
// Validate
$('#accountSetupForm').validate({
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

var Qs = {};
$('.accountSetup select').change(function(e){
	//FIRST RESET ALL THE DISPLAYS
	$('.accountSetup select option').show();

	//THEN HIDE QUESTIONS SO Q ARE MUTUALLY EXCLUSIVE
	var thisid 				= $(this).attr("id");
	var selectedQuestion 	= $(this).find("option:selected").val();
	Qs[thisid] = selectedQuestion;

	$('.accountSetup select').each(function(){
		for(var i in Qs){
			if($(this).attr("id") != i){
				var used = $(this).find("option[value='"+Qs[i]+"']").hide();
			}
		}
	});
	
	return false;
});
</script>
<?php 
include("models/inc/gl_footer.php");
?>




	
