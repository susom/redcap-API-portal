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
		    $_SESSION["REDCAP_PORTAL"]['user']->lang = $use_lang;

			//CHECK IF USER AGREED TO CONSENT YET
			if($loggedInUser->active){
				$logged_in = true;
			}
		} else { // Invalid credentials		
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID") . "<br> ". $lang["ACCOUNT_ERROR_TRY_AGAIN"];
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
	$fname 				= (!empty($_POST["firstname"]) 	? $_POST["firstname"] : null ) ;
	$lname 				= (!empty($_POST["lastname"]) 	? $_POST["lastname"] : null) ;
	$zip 				= (!empty($_POST["zip"]) 		? intval($_POST["zip"]) :null ) ;
	$city 				= (!empty($_POST["city"]) 		? ucwords($_POST["city"]) :null ) ;
	$state 				= (isset($_POST["state"]) 		? $_POST["state"]: null) ;
	$nextyear 			= (isset($_POST["nextyear"]) 	? $_POST["nextyear"] 	:null ) ;
	$in_usa 			= (isset($_POST["in_usa"]) 		? $_POST["in_usa"] 		:null ) ;
	$oldenough 			= (isset($_POST["oldenough"]) 	? $_POST["oldenough"] 	: null) ;
	$birthyear 			= (isset($_POST["birthyear"]))  ? intval($_POST["birthyear"]) : null;
	$optin 				= (isset($_POST["optin"]) 		? $_POST["optin"] 		:null ) ;
	$actualage 			= (!$birthyear ? null : date("Y") - $birthyear);
	$password_new 		= trim($_POST["password_new"]);
	$password_new_again = trim($_POST["password_new_again"]);

	//VALIDATE STUFF (matching valid emails, nonnull fname, lastname, zip or city)
	if(is_null($fname) || is_null($lname)){
		$errors[] = lang("ACCOUNT_SPECIFY_F_L_NAME");
	}

	if($password_new !== $password_new_again) {
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}else{
		$scramble_pw 		= md5("somelongthingsurewhynot" + $username);
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
			$errors[] = lang("FORGOTPASS_INVALID_VALUE") . " " . $i;
		}
	}

	//End data validation
	if(count($errors) == 0){
		//Construct a user auth object
		$auth = new RedcapAuth($username, NULL, NULL, $fname, $lname, $zip, $city, $state, $actualage);

		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if($auth->usernameExists()){
			$tempu 		= getUserByUsername($email);
			$olduser 	= new RedcapPortalUser($tempu->user_id);
			if($olduser->isActive()){
				//CURRENT ACCOUNT + ACTIVE (LINK ALREADY CLICKED)
				$errors[] = lang("ACCOUNT_EMAIL_IN_USE_ACTIVE",array($email));
			}else{
				//CURRENT ACCOUTN NOT ACTIVE
				if($oldenough && $optin && $actualage >= 18){
					//WAS FORMERLY INELIGIBLE NOW ELIGIBLE, SEND ACTIVATION LINK
					$errors[] = lang("ACCOUNT_NEW_ACTIVATION_SENT",array($email));
					
					//SEND NEW ACTIVATION LINK
					$olduser->updateUser(array(
						getRF("zip") 	=> $zip,
				        getRF("city") 	=> $city,
				        getRF("state") 	=> $state,
				        getRF("age") 	=> $actualage,
				        getRF("lang") 	=> $lang_req

				      ));
		            $olduser->createEmailToken();
		            $olduser->emailEmailToken();

		            //CLEAN UP
					unset($fname, $lname, $email, $zip, $city);
				}else{
					//WAS FORMERLY AND STILL IS INELIGIBLE
					addSessionMessage( lang("ACCOUNT_NOT_YET_ELIGIBLE",array("")), "notice" );
				}
			}
		}else{
			//IF THEY DONT PASS ELIGIBILITY THEN THEY GET A THANK YOU , BUT NO ACCOUNT CREATION 
			//BUT NEED TO STORE THEIR STUFF FOR CONTACT
			if($in_usa && $oldenough && $optin && $actualage >= 18){
				//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
				if($newuserID = $auth->createNewUser($scramble_pw, FALSE)){
					$newuser = new RedcapPortalUser($newuserID);
					foreach ($password_reset_pairs as $i => $pair) {
						$q = $_POST[$pair['question']];
						$a = $_POST[$pair['answer']];
						$a = hashSecurityAnswer($a);
						$newuser->updatePasswordReset($pair['question'], $pair['answer'], $q, $a);
					}
					$newuser->setActive();
					$salt 				= $newuser->getSalt();
					$entered_pass_new 	= generateHash($password_new, $salt);
					$newuser->updatePassword($entered_pass_new);
					$newuser->updateUser(array(
						"portal_participant_id" => $participant_id,
				      ));

					addSessionMessage( "Account Created Succesfully", "success");

					header("Location: admin_login.php");
				}else{
					$errors[] = !empty($auth->error) ? $auth->error : 'Unknown error creating user';
				}
			}else{
				//ADD THEIR EMAIL , NAME TO CONTACT DB
				$auth->createNewUser($scramble_pw, FALSE);

				$reason 	= "";
				if(!$oldenough || $actualage < 18){
					$reason = lang("ACCOUNT_TOO_YOUNG");
				}

				if(!$in_usa){
					$reason = lang("ACCOUNT_NOT_IN_USA");
				}
				
				addSessionMessage( lang("ACCOUNT_NOT_YET_ELIGIBLE",array($reason)), "notice" );
			}

			//CLEAN UP
			unset($fname, $lname, $email, $zip, $city);
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
			<form id="loginForm" name="loginForm" class="form-horizontal loginForm col-md-6 " action="admin_login.php" method="post" novalidate="novalidate">
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
			<form id="getstarted" action="admin_login.php" class="form-horizontal" method="POST" role="form">
			    <input type="hidden" name="lang_req" value="<?php echo $_SESSION["use_lang"] ?>"/>
			    <h2>Admin : Register a user</h2>
			    <div class="form-group">
			      <label for="email" class="control-label col-sm-3">New User:</label>
			      <div class="col-sm-4"> 
			        <input type="text" class="form-control" name="firstname" id="firstname" placeholder="<?php echo lang("ACCOUNT_FIRST_NAME") ?>" value="<?php echo (isset($fname) ? $fname : "") ?>">
			      </div>
			      <div class="col-sm-4"> 
			        <input type="text" class="form-control" name="lastname" id="lastname" placeholder="<?php echo lang("ACCOUNT_LAST_NAME") ?>" value="<?php echo (isset($lname) ? $lname : "") ?>">
			      </div>
			    </div>
			    <div class="form-group">
			      <label for="username" class="control-label col-sm-3">New Username:</label>
			      <div class="col-sm-8"> 
			        <input type="text" class="form-control" name="username" id="username" placeholder="Username" value="<?php echo (isset($email) ? $email : "") ?>">
			      </div>
			    </div>
				<div class="form-group">
			      <label for="partid" class="control-label col-sm-3">Participant Id:</label>
			      <div class="col-sm-8"> 
			        <input type="text" class="form-control" name="partid" id="partid" placeholder="Particiapant Id" value="">
			      </div>
			    </div>
			    <div class="form-group">
			      <label for="zip" class="control-label col-sm-3">User Location:</label>
			      

			      <div class="col-sm-4"> 
			        <input type="text" class="form-control city" name="city" id="city" placeholder="<?php echo lang("ACCOUNT_CITY") ?>">
			      </div>
			      <div class="col-sm-2"> 
			        <select name="state" class="form-control state" id="state">
			          <option value="AL">AL</option>
			          <option value="AK">AK</option>
			          <option value="AZ">AZ</option>
			          <option value="AR">AR</option>
			          <option value="CA" selected>CA</option>
			          <option value="CO">CO</option>
			          <option value="CT">CT</option>
			          <option value="DE">DE</option>
			          <option value="DC">DC</option>
			          <option value="FL">FL</option>
			          <option value="GA">GA</option>
			          <option value="HI">HI</option>
			          <option value="ID">ID</option>
			          <option value="IL">IL</option>
			          <option value="IN">IN</option>
			          <option value="IA">IA</option>
			          <option value="KS">KS</option>
			          <option value="KY">KY</option>
			          <option value="LA">LA</option>
			          <option value="ME">ME</option>
			          <option value="MD">MD</option>
			          <option value="MA">MA</option>
			          <option value="MI">MI</option>
			          <option value="MN">MN</option>
			          <option value="MS">MS</option>
			          <option value="MO">MO</option>
			          <option value="MT">MT</option>
			          <option value="NE">NE</option>
			          <option value="NV">NV</option>
			          <option value="NH">NH</option>
			          <option value="NJ">NJ</option>
			          <option value="NM">NM</option>
			          <option value="NY">NY</option>
			          <option value="NC">NC</option>
			          <option value="ND">ND</option>
			          <option value="OH">OH</option>
			          <option value="OK">OK</option>
			          <option value="OR">OR</option>
			          <option value="PA">PA</option>
			          <option value="RI">RI</option>
			          <option value="SC">SC</option>
			          <option value="SD">SD</option>
			          <option value="TN">TN</option>
			          <option value="TX">TX</option>
			          <option value="UT">UT</option>
			          <option value="VT">VT</option>
			          <option value="VA">VA</option>
			          <option value="WA">WA</option>
			          <option value="WV">WV</option>
			          <option value="WI">WI</option>
			          <option value="WY">WY</option>
			        </select>
			      </div>
			      
			      <div class="col-sm-2"> 
			        <input type="number" class="form-control zip" name="zip" id="zip" placeholder="<?php echo lang("ACCOUNT_ZIP") ?>" min="0">
			        <select id="zipset"></select>
			      </div>
			    </div>

			    <aside class="eligibility">
			      <fieldset class="eli_one">
			        <div class="form-group">
			          <label class="control-label col-sm-6"><?php echo lang("ACCOUNT_USA_CURRENT") ?></label>
			          <div class="col-sm-2"> 
			            <label><input name="in_usa" type="radio" value="1"> <?php echo lang("GENERAL_YES") ?></label>
			          </div>

			          <div class="col-sm-2"> 
			            <label><input name="in_usa" type="radio" value="0"> <?php echo lang("GENERAL_NO") ?></label>
			          </div>
			        </div>
			      </fieldset>

			      <fieldset class="eli_two">
			        <div class="form-group">
			          <label class="control-label col-sm-6"><?php echo lang("ACCOUNT_18_PLUS") ?></label>
			          <div class="col-sm-2"> 
			            <label><input name="oldenough" type="radio" value="1"> <?php echo lang("GENERAL_YES") ?></label>
			          </div>
			          <div class="col-sm-2"> 
			            <label><input name="oldenough" type="radio" value="0"> <?php echo lang("GENERAL_NO") ?></label>
			          </div>
			        </div>
			        <div class="form-group">
			          <label class="control-label col-sm-6"><?php echo lang("ACCOUNT_BIRTH_YEAR") ?></label>
			          <div class="col-sm-4"> 
			            <select name="birthyear" class="form-control" id="birthyear">
			            <?php
			              $thisyear = date("Y") - 18;
			              for($i=0; $i < 100 ; $i++){
			                $cutoff = ($i == 0 ? "selected" : "");
			                echo "<option $cutoff>".($thisyear - $i)."</option>";
			              }
			            ?>
			            </select>
			          </div>
			        </div>
			      </fieldset>
			    </aside>
			  	
			  	<hr>

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
					<div class="form-group accountSetup">
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
							<input type="text" placeholder="<?php echo lang("FORGOTPASS_RECOVERY_ANSWER") ?>" class="form-control" aria-label="password recovery answer" name="<?php echo $pair['answer'] ?>" id="<?php echo $pair['answer'] ?>" value="">
						</div><!-- /input-group -->
					</div>
					<?php
				}
				?>

			    <div class="form-group">
			      <span class="control-label col-sm-3"></span>
			      <div class="col-sm-8"> 
			        <em><?php echo lang("ACCOUNT_AGREE") ?></em>
			      </div>
			    </div>
			    <div class="form-group">
			      <span class="control-label col-sm-3"></span>
			      <div class="col-sm-8"> 
			        <!-- <div class="g-recaptcha" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div> -->
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

          