<?php 
require_once("models/config.php");

//REDIRECT USERS THAT ARE NOT ALREADY LOGGED IN
if(!isUserLoggedIn()) { 
	$destination = $websiteUrl . "login.php";
	header("Location: " . $destination);
	exit; 
}

//--------------------------------------------------------------------
// PASSWORD RESET QUESTIONS AND ANSWERS

// Save updated security settings
if( isset($_POST['account_update']) ) {
	$_POST["consented"] = true;

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
			addSessionMessage(lang("FORGOTPASS_UPDATED"),"success");
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
			addSessionAlert(lang("FORGOTPASS_INVALID_VALUE") . " " . $i);
			$all_valid = false;
		} else {
			$a = hashSecurityAnswer($a);
			$loggedInUser->updatePasswordReset($pair['question'], $pair['answer'], $q, $a);
		}
	}
	if ($all_valid) {
		addSessionMessage(lang("FORGOTPASS_Q_UPDATED"),'success');
	}

	if( $valid && $all_valid ) {
		//THEY ARE CONSENTED, SET ACCOUNT ACTIVE
		$loggedInUser->setActive();

		//REDIRECT TO THE DASHBOARD
		include("models/inc/surveys.php");

		// if(isset($_SESSION["elite_users"])){
		// 	$elite 				= $_SESSION["elite_users"];
		// }else{
		// 	$elite				= getEliteUsers();
		// 	$_SESSION["elite_users"] 	= $elite;
		// }
		// if(in_array($loggedInUser->id, $elite)){
		// 	addSessionMessage(lang("ACCOUNT_ELITE_THANKS") . "<br><br><img src='images/ribbon_heart.png'>","success");
		// }
		header("Location: survey.php?sid=" . SurveysConfig::$core_surveys[0]); //survey link of first survey
		exit;
	}
} 

//MAKE SURE THIS COMES DIRECTYLY FROM consent
if( !isset($_POST['consented']) ){
	//REDIRECT TO SECURITY QUESTIONS
	header("Location: consent.php");
	exit;
}

$pg_title 		= "Account Setup | $websiteName";
$body_classes 	= "login register setup";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
	<div id="main-content" class="col-md-8 col-md-offset-2 accountSetup" role="main">
		<div class="well row">
			<ul id="register_steps">
				<li><span>1</span> <?php echo lang("STEP_REGISTER") ?></li>
				<li><span>2</span> <?php echo lang("STEP_VERIFY") ?></li>
				<li><span>3</span> <?php echo lang("STEP_CONSENT") ?></li>
				<li class="on"><span>4</span> <?php echo lang("STEP_SECURITY") ?></li>
			</ul>
			<form role="form" class="form-horizontal" action="account_setup.php" method="POST" id="accountSetupForm" name="accountSetupForm">
				<h2><?php echo lang("FORGOTPASS_SEC_Q_SETUP") ?>:</h2>
				<?php 
					include("models/inc/set_pw.php");
				?>
				
				<div class="form-group">
					<span class="control-label col-sm-3"></span>
					<div class="col-sm-8">
					<p><?php echo lang("FORGOTPASS_SEC_Q_ANSWERS") ?></p>
					</div>
				</div>
				<?php 
				$options = '<option>'.lang("FORGOTPASS_CHOSE_QUESTION").'</option>\n';
				foreach ($template_security_questions as $k => $v){
					if(!empty($v)){
						$options .= "<option value=\"$k\">$v</option>\n";
					}
				}
						
				// Build html for each question/answer pair
				foreach ($password_reset_pairs as $i => $pair){
				?>
					<div class="form-group">
						<label for="sec_q<?php echo $i ?>" class="control-label col-sm-3"><?php echo lang("FORGOTPASS_SEC_Q") ?> <?php echo $i ?>:</label>
						<div class="col-sm-8">
							<?php 
								if($i == 3){
							?>
									<input type="text" name="<?php echo $pair['question'] ?>" class="form-control" id="<?php echo $pair['question'] ?>" placeholder="<?php echo lang("FORGOTPASS_WRITE_CUSTOM_Q") ?>"/>
							<?php
								}else{
							?>
									<select name="<?php echo $pair['question'] ?>" class="form-control" id="<?php echo $pair['question'] ?>">
									<?php echo $options ?>
									</select>
							<?php 
								}
							?>
							<input type="text" placeholder="<?php echo lang("FORGOTPASS_RECOVERY_ANSWER") ?>" class="form-control" aria-label="password recovery answer" name="<?php echo $pair['answer'] ?>" id="<?php echo $pair['answer'] ?>" value="<?php echo $loggedInUser->$pair['answer'] ?>">
						</div><!-- /input-group -->
					</div>
					<?php
				}
				?>

				<div class="form-group">
			      <span class="control-label col-sm-3"></span>
			      <div class="col-sm-8"> 
			        <button type="submit" class="btn btn-success" value="true"><?php echo lang("GENERAL_SUBMIT") ?></button>
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




	
