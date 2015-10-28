<?php
require_once("models/config.php");

// MANUAL METHOD TO HAVE MIXED PATH BASED ON USER STATUS
if(isUserLoggedIn()){
	logIt('Logged in - redirect to userpage...', 'DEBUG');
	header("Location: userpage.php");
	die();
}else{
	// You could include or display a page...
	//	logIt('At index but not logged in - redirect to landing-page...', 'DEBUG');
	//	include('landing-page.php');
}

$page = new htmlPage("Login | $websiteName");
$page->printStart();

// Add nav bar
require_once("navbar.php");
print getSessionMessages();

logIt("At index / Landing Page","DEBUG");
?>
<div id="content" style="width: 75%;">
    <h1>The National Lymphatic Disease and Lymphedema Registry</h1>

    </br>

	<h4>Why the Patient Registry and Tissue Bank Are Important</h4>

	<p>Lymphedema and lymphatic diseases take a variety of forms, but, in general, they have the capacity to affect virtually every organ in the body. These lymphatic diseases include, but are not limited to, primary and secondary lymphedemas, lymphangiomas, lipedema, cystic hygromas, lymphangiectasias, lymphangiomatosis, and syndromes of mixed lymphatic and vascular anomalies, along with a variety of other developmental disorders that influence lymphatic competence.</p>
	<h4>What is a registry and what are its goals?</h4>
	<p>The National Lymphatic Disease Patient Registry and Tissue Bank will include a representative and well-characterized population of patients, with associated biological materials (blood and tissue samples), to serve as a source for the clinical and laboratory study of lymphatic diseases. This registry is a confidential database that contains information about individuals who carry the diagnosis of a lymphatic disease or of lymphedema. This comprehensive registry will serve as a repository of information that will enhance the future ability of health care professionals to accurately identify, categorize, treat, and prevent these diseases. The tissue bank will facilitate the availability of blood samples to lymphatic investigators for prospective research including genetic and proteomic studies.</p>
	<p>We invite you to participate in this highly significant development for the patient community. A national patient registry paves the way for future clinical trials of experimental drugs and therapies designed to treat lymphatic diseases. We encourage all patients to participate in this important initiative. </p>
	<h4>How would I be involved?</h4>
	<p>If you would like to participate in this registry, you simply have to enroll within this website. You will have to give your consent to participate, and fill in the survey forms. You will have to provide some personal information, but Stanford will ensure that it will be protected and secure. While completing the survey forms process may be time-consuming, please try to complete the questionnaires in their entirety. Multiple consents may be required of you in order to assure both the confidentiality and integrity of this program. You will be able to log off and continue completing the surveys at a later date if you so require. You will also be able to save a hard copy of your responses at any time.</p>
	<h4>What is the commitment of my time and effort to participate?</h4>
	<p>Periodically after enrolling, you will be asked to return to this site to update information about any symptoms, test results or treatments. You will be asked to log in about every six months. It should not take more than 15-60 minutes to enter this information each time you log into this secure website.</p>
	<p>The Lymphedema and Lymphatic Disease Patient Registry operates under the guidance and direction of the Lymphatic Education & Research Network Board of Directors and the Institutional Review Board of Stanford University.</p>       

	<hr>
	<p style="text-align:center">
		<a class="btn btn-large" href="login.php">Login</a> 
		<a class="btn btn-teal btn-large" href="register.php">Register</a>
	</p>
	<br>
	<div class="clear"></div>
</div>
<script>
	$(function() {
		//$('.carousel').carousel('pause');
	});
</script>
</body>
</html>