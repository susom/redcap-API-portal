<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN TO THE PORTAL PAGE
if(isUserLoggedIn()) { 
	$destination = (isUserActive() ? $websiteUrl . "index.php" : $websiteUrl . "consent.php");
	header("Location: " . $destination);
	exit; 
}

if(isset($_GET["session_clear"])){
	unset($_SESSION[$_CFG->SESSION_NAME]['login_attempts']);
	header("Location: " . $websiteUrl . "login.php"); 
}
unset($_SESSION[SESSION_NAME]['login_attempts']);
$attempts_remaining = (isset($_SESSION[$_CFG->SESSION_NAME]['login_attempts']) ? $_SESSION[$_CFG->SESSION_NAME]['login_attempts'] : 4);
$username_label 	= "";
$badlogin 			= "";
//--------------------------------------------------------------------
// Login Posted
if( !empty($_POST) && isset($_POST['new_login']) ) {

	$errors 	= array();
	$username 	= trim($_POST["username"]);
	$password 	= trim($_POST["password"]);
	$badlogin 	= $username;
	$use_lang 	= $_POST["use_lang"];

	//Perform some basic validation
	if($username == "") $errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	if($password == "") $errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");

	if(empty($_POST["username"]) && empty($_POST["password"]) && !empty($_POST["participant_id"])){
		array_pop($errors);
		array_pop($errors);
		$participant_id = trim($_POST["participant_id"]);

		$user 		= getUsernameByParticipantID($participant_id);
		$username 	= $user->username;
		$password = md5("somelongthingsurewhynot" + $username);

		//use participant ID to get the username
		//then use the username and the salt to do the password
		//then sign in 
	}

	//End data validation
	if(count($errors) == 0) {
		// Continue with authentication
		$auth = new RedcapAuth($username,$password);

		// Valid credentials
		if($auth->authenticated_user_id != Null) {
			// Log user in
			$loggedInUser 		= new RedcapPortalUser($auth->authenticated_user_id);

			//ADD IN LANGUAGE OPTION TO SESSION
			$data[] = array(
		      "record"            => $loggedInUser->id,
		      "field_name"        => 'portal_lang',
		      "value"             => $use_lang
		    );
		    $projects     = SurveysConfig::$projects;
		    $API_TOKEN    = $projects["REDCAP_PORTAL"]["TOKEN"];
		    $API_URL      = $projects["REDCAP_PORTAL"]["URL"];
		    $result       = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
		    $_SESSION["REDCAP_PORTAL"]['user']->lang = $use_lang;

			//CHECK THIS ON EVERY LOGIN? SURE
			$supp_proj 		= SurveysConfig::$projects;
			foreach($supp_proj as $proj_name => $project){
				if($proj_name == $_CFG->SESSION_NAME){
					continue;
				}
				$supp_id 					= linkSupplementalProject($project, $loggedInUser);
				$loggedInUser->{$proj_name} = $supp_id;
			}

			unset($_SESSION[SESSION_NAME]['login_attempts']);
			setSessionUser($loggedInUser);

			//CHECK IF USER AGREED TO CONSENT YET
			if(!$loggedInUser->active){
				$destination 	= "consent.php";
			}else{
				$destination 	= getSessionRedirectOr($websiteUrl.'index.php');
			}
			
			header("Location: $destination");
		} else { // Invalid credentials
			//IF NOT A REGISTERED USER - KEEP EMAIL AND PREFILL ON REGISTER FORM
			$attempts_remaining--;
			$_SESSION[SESSION_NAME]['login_attempts'] 	= $attempts_remaining;
			
			if($attempts_remaining < 1){
				$errors[] = lang("FORGOTPASS_SUGGEST");
			}else{
				$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID") . "<br> ". $lang["ACCOUNT_ERROR_TRY_AGAIN"] . $attempts_remaining . " " . ($attempts_remaining > 1 ? $lang["ACCOUNT_ERROR_ATTEMPTS"] : $lang["ACCOUNT_ERROR_ATTEMPT"]);
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

$pg_title 		= "Login : " .$_CFG->WEBSITE["Name"];
$body_classes 	= "login";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
    <div id="main-content" class="col-md-8 col-md-offset-2 logpass" role="main">
		<?php
			include("models/inc/language_select.php");
		?>
		<div class="well row">
			<form id="loginForm" name="loginForm" class="form-horizontal loginForm col-md-6 " action="login.php" method="post" novalidate="novalidate">
				<input type="hidden" name="use_lang" value="<?php echo $_SESSION["use_lang"] ?>"/>
				<h2><?php echo lang("ACCOUNT_LOGIN_CONTINUE") ?></h2>
				<div class="form-group">
					<label for="username" class="control-label"><?php echo lang("ACCOUNT_EMAIL_ADDRESS_OR_USERNAME") ?></label>
					<input <?php echo $disabled?> type="text" class="form-control" name="username" id="username" placeholder="<?php echo lang("ACCOUNT_EMAIL_ADDRESS_OR_USERNAME") ?>" autofocus="true" aria-required="true" aria-invalid="true" aria-describedby="username-error" value="<?php echo $badlogin?>">
				</div>
				<div class="form-group">
					<label for="password" class="control-label"><?php echo lang("ACCOUNT_PASSWORD") ?></label>
					<input <?php echo $disabled?> type="password" class="form-control" name="password" id="password" placeholder="<?php echo lang("ACCOUNT_PASSWORD") ?>" autocomplete="off" >
				</div>
				
				<!-- <div class="form-group">
					<h3 class="or">OR</h3>
					<label for="participant_id" class="control-label"><?php echo lang("ACCOUNT_PARTICIPANT_ID") ?></label>
					<input <?php echo $disabled?> type="text" class="form-control" name="participant_id" id="participant_id" placeholder="Login with Participant ID" autofocus="true" aria-required="true" aria-invalid="true" aria-describedby="username-error" value="<?php echo $badlogin?>">
				</div> -->
	

				<div class="form-group">
					<div class="pull-left">
						<a class="showrecover" href="#"><?php echo lang("FORGOTPASS") ?></a> <br>
						<a class="showregister" href="register.php"><?php echo lang("REGISTER_STUDY") ?></a>  
					</div>   
					<div class="pull-right"> 
						<input <?php echo $disabled?> type="submit" class="btn btn-success" name="new_login" id="newfeedform" value="<?php echo lang("ACCOUNT_LOGIN_NOW") ?>"/>     
						<span></span>
					</div>
				</div>
	        </form>

	        <form id="pwresetForm" name="newLostPass" class="form-horizontal lostPass  col-md-6 " action="forgot_password.php" method="post">
				<aside class="stepone">
					<h2><?php echo lang("FORGOTPASS_BEGIN_RESET") ?></h2>
					<div class="form-group">
						<label for="username" class="control-label"><?php echo lang("ACCOUNT_EMAIL_ADDRESS") ?></label>
						<input type="text" class="form-control" name="forgotemail" id="forgotemail" placeholder="<?php echo lang("ACCOUNT_EMAIL_ADDRESS") ?>" autofocus value="<?php echo $badlogin?>"/>
					</div>
					<div class="form-group">
						<a class="showlogin pull-left" href="#"><?php echo lang("ACCOUNT_LOGIN_NOW") ?></a>       
						<button type='submit' class="btn btn-success pull-right nextstep" title="Forgot Password" ><?php echo lang("ACCOUNT_NEXT_STEP") ?></button>
					</div>
				</aside>

				<aside class="steptwo">
					<h2><?php echo lang("FORGOTPASS_RECOVERY_METHOD") ?></h2>
					<div class="form-group">
						<label for="emailme" class="control-label">
							<input type="radio" name="resetlink" id="emailme" checked value="emailme"/>
							<?php echo lang("FORGOTPASS_EMAIL_ME") ?>
						</label>
						
					</div>
					<div class="form-group">
						<label for="secquestions" class="control-label">
							<input type="radio" name="resetlink" id="secquestions" value="secquestions"/>
							<?php echo lang("FORGOTPASS_ANSWER_QS") ?>
						</label>
					</div>
					<div class="form-group">
						<a class="showlogin pull-left" href="#"><?php echo lang("ACCOUNT_LOGIN_NOW") ?></a>       
						<input type="submit" class="btn btn-success pull-right " name="new_pass_reset_request" id="newfeedform" value="<?php echo lang("FORGOTPASS") ?>" />
					</div>
				</aside>
			</form>
        </div>	
    </div>
  </div>
</div>
<script>
$(document).ready(function(){
	$("#loginForm").submit(function(){
		$("input[name='new_login']").addClass("loading");
	});

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
				required: false
			},
			password: {
			  	required: false
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

          