<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN AND CONSENTED TO THE PORTAL PAGE
if(isUserLoggedIn() && isUserActive()) { 
	$destination = $websiteUrl . "dashboard/index.php";
	header("Location: " . $destination);
	exit; 
}

if(!isUserLoggedIn()) { 
	$destination = $websiteUrl . "login.php";
	header("Location: " . $destination);
	exit; 
}

if( !empty($_POST) && isset($_POST['consented']) ){
	//REDIRECT TO SECURITY QUESTIONS
	header("Location: account_setup.php");
	exit;
}

$pg_title 		= "Consent | $websiteName";
$body_classes 	= "consent";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
	<div id="main-content" class="col-md-8 col-md-offset-2 consent" role="main">
		<div class="well row">
			<div  class="consent_disclaim">
				<ul>
					<li>We need your permission before we can ask you any questions, so please read the following Informed Consent Document</li>
					<li>The initial survey will take 15 – 20 minutes to complete – but you don't need to fill it all out at one time</li>
					<li>We will check back in with you every 6 months</li>
					<li>We will add new surveys and invite you to participate</li>
				</ul>
			</div>
		  <?php
		  	include("models/inc/well_consent_doc.php");
		  ?>
	  	</div>
	</div>
  </div>
</div>
<?php 
include("models/inc/gl_footer.php");
?>