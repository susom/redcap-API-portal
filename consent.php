<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN AND CONSENTED TO THE PORTAL PAGE
if(isUserLoggedIn() && isUserActive()) { 
	$destination = $websiteUrl . "dashboard/index.php";
	header("Location: " . $destination);
	exit; 
}

if( !empty($_POST) && isset($_POST['consented']) ){
	//THEY ARE CONSENTED, SET ACCOUNT ACTIVE
	$loggedInUser->setActive();

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