<?php
require_once("models/config.php");

$logged_in 			= false;

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

	//End data validation
	if(count($errors) == 0) {
		$API_TOKEN    = "2AC734BFA26EBF3618719A0B09EDAA0F";
		$API_URL      = "http://redcap.irvins.loc/api/";

		//GET ALL THE ADMINS, COMPARE THEN GO FOR IT
		$params = array(
			'fields' => array("admin_id", "admin_username", "admin_password", "admin_role")
		);
		$result = RC::callApi($params, true, $API_URL, $API_TOKEN);

		// Scan records for email and username matches and to set nextId
		$new_id = 1;
		$username_matches = array();

		foreach ($result as $idx => $record){
			$id = $record["admin_id"];
			if (is_numeric($id) && $id >= $new_id){
				$new_id = $id+1; //GUESS THE NEXT AUTOINCREME
			}

			if ( empty($record["admin_username"]) ){
				continue;
			}
			if ( !empty($record["admin_username"]) 
				&& $username == sanitize($record["admin_username"]) 
				&& $password == sanitize($record["admin_password"])
				&& $record["admin_role"] < 3
				){
				$logged_in = true;
			}
		}

		// Valid credentials
		if(!$logged_in) { // Invalid credentials		
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID") . " Or not boss enough.<br> ". $lang["ACCOUNT_ERROR_TRY_AGAIN"];
		}
	} 
	
	// Add errors messages to session
	foreach ($errors as $error) {
		addSessionAlert($error);
	}
}

if( !empty($_POST) && isset($_POST['submit_new_user']) ){
	$_POST["consented"] = true;
	$lang_req 			= $_POST["lang_req"];

	$errors 			= array();
	$username 			= trim($_POST["username"]);
	$participant_id 	= (!empty($_POST["partid"]) 	? trim($_POST["partid"]) : null ) ;
	$fname 				= "n/a";
	$lname 				= "n/a";

	//PASSWORD SECURITY QUESTIONS
	$all_valid 				= true;
	$scramble_pw 			= md5("taiwan_login");

	//End data validation
	if(count($errors) == 0){
		if($username !== $participant_id){
			$errors[] = "The two participant ID fields do not match.";
		}else{
			//Construct a user auth object
			$auth = new RedcapAuth($username, NULL, NULL, $fname, $lname, NULL, NULL, NULL, NULL);

			//Checking this flag tells us whether there were any errors such as possible data duplication occured
			if($auth->usernameExists()){
				$tempu 			= getUserByUsername($username);
				$loggedInUser 	= new RedcapPortalUser($tempu->user_id);
				$loggedInUser->setActive();
				$loggedInUser->updateUser(array(
					"portal_participant_id" => $participant_id,
					"portal_lang" 			=> $use_lang
			      ));
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

			
				setSessionUser($loggedInUser);
				
				addSessionMessage( "Account Created Succesfully", "success");
				header("Location: dashboard/index.php");
			}else{
				//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
				if($newuserID 		= $auth->createNewUser($scramble_pw, FALSE)){
					$loggedInUser 	= new RedcapPortalUser($newuserID);
					$loggedInUser->setActive();
					$loggedInUser->updateUser(array(
						"portal_participant_id" => $participant_id,
						"portal_lang" 			=> $use_lang
				      ));
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

				
					setSessionUser($loggedInUser);

					addSessionMessage( "Account Created Succesfully", "success");
					header("Location: dashboard/index.php");
				}else{
					$errors[] = !empty($auth->error) ? $auth->error : 'Unknown error creating user';
				}
			}
		}
	}

	// Add alerts to session for display
	foreach ($errors as $error) {
		addSessionAlert($error);
	}
}
$disabled = null;
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
			<?php 
			if(!$logged_in){
			?>
			<form id="loginForm" name="loginForm" class="form-horizontal loginForm col-md-6 " action="admin_login_tw.php" method="post" novalidate="novalidate">
				<input type="hidden" name="use_lang" value="<?php echo $_SESSION["use_lang"] ?>"/>

				<h2>Admin : <?php echo lang("ACCOUNT_LOGIN_CONTINUE") ?></h2>
				<div class="form-group">
					<label for="username" class="control-label"><?php echo lang("ACCOUNT_EMAIL_ADDRESS_OR_USERNAME") ?></label>
					<input <?php echo $disabled?> type="text" class="form-control" name="username" id="username" placeholder="<?php echo lang("ACCOUNT_EMAIL_ADDRESS_OR_USERNAME") ?>" autofocus="true" aria-required="true" aria-invalid="true" aria-describedby="username-error" value="<?php echo $badlogin?>">
				</div>
				<div class="form-group">
					<label for="password" class="control-label"><?php echo lang("ACCOUNT_PASSWORD") ?></label>
					<input <?php echo $disabled?> type="password" class="form-control" name="password" id="password" placeholder="<?php echo lang("ACCOUNT_PASSWORD") ?>" autocomplete="off" >
				</div>
				<div class="form-group"> 
					<div class="pull-right"> 
						<input <?php echo $disabled?> type="submit" class="btn btn-success" name="new_login" id="newfeedform" value="Log In"/>     
						<span></span>
					</div>
				</div>
	        </form>
        	<?php
			}else{
			?>
			<form id="getstarted" action="admin_login_tw.php" class="form-horizontal" method="POST" role="form">
			    <input type="hidden" name="lang_req" value="<?php echo $_SESSION["use_lang"] ?>"/>
			    <h2>Admin : Login a user</h2>
			    <div class="form-group">
			      <label for="username" class="control-label col-sm-3">Participant Id:</label>
			      <div class="col-sm-8"> 
			        <input type="text" class="form-control" name="username" id="username" placeholder="Particiapant Id" value="">
			      </div>
			    </div>
				<div class="form-group">
			      <label for="partid" class="control-label col-sm-3">Participant Id:</label>
			      <div class="col-sm-8"> 
			        <input type="text" class="form-control" name="partid" id="partid" placeholder="Particiapant Id" value="">
			      </div>
			    </div>
			    

			    <div class="form-group">
			      <span class="control-label col-sm-3"></span>
			      <div class="col-sm-8"> 
			        <em><?php echo lang("ACCOUNT_AGREE") ?></em>
			      </div>
			    </div>
			    <div class="form-group">
			      <span class="control-label col-sm-3"></span>
			      <div class="col-sm-8"> 
			        <button type="submit" class="btn btn-primary" name="submit_new_user"  value="true"><?php echo lang("SUBMIT") ?></button>
			        <input type="hidden" name="submit_new_user" value="true"/>
			        <input type="hidden" name="optin" value="true"/>
			      </div>
			    </div>
			</form>
			<?php
			}
			?>
        </div>
    </div>
  </div>
</div>
<style>
  #zipset  { display:none; 
    border:1px solid #ddd;
    height:34px; 
    width:100%; 
  }
  #zip{
    opacity:1;
    transition: .5s opacity;
  }
  #zip.goaway {
    opacity:0;
    position:absolute; 
    z-index:-1;
  }
  </style>
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
});
</script>
<?php 
include("models/inc/gl_footer.php");
?>

          