<?php 
require_once("models/config.php");

//requireUserAccount();	// Allow any user/pass to enter - even if they are missing profile requirements
requireActiveUserAccount();	// Only allow 'active' users that presumably have met all requirements

//FOR TESTING PURPOSES!!!  redownload user settings at each page load
//	$loggedInUser->refreshUser();	

$page = new htmlPage("User Page | $websiteName");
$page->printStart();
require_once("navbar.php");
include("learn_functions.php");
?>
<div class='container'>
   <div class="row">
     <div class="jumbotron">			
        <p>Welcome to your survey page.</p>          
        <p>You are logged in as <strong><?php echo $loggedInUser->getEmail(); ?></strong></p>          
      </div> <!-- jumbotron -->
   </div> <!-- row -->
</div> <!-- container -->

<div class='container'>
   <div class="row">
     <div class="jumbotron">
        <h3>Initial Registration</h3>
        <div class="list-group">
      
		<?php
//xxyjl: this is counter to the API documentation, but API parameters
//seem to expect the event name and NOT an array of event ids.
define('EVENT_1', 'enrollment_arm_1');
define('EVENT_2', 'follow_up_1_arm_1'); 
define('EVENT_3', 'follow_up_2_arm_1'); //235

$learn_forms = array(
   "informed_consents"									=>	array("label"	=>"Informed consent"),
   "demographics"										=>	array("label"	=>"Demographics"),
   "healthcare_provider_information"					=>	array("label"	=>"Healthcare Provider Information", "retake"=>TRUE),
   "lymphatic_diagnosis"								=>	array("label"	=>"Lymphatic Diagnosis", "retake"=>TRUE),
   "tissue_bank_genetics"								=>	array("label"	=>"Tissue Bank Genetics", "retake"=>TRUE),
   "medical_and_surgical_history"						=>	array("label"	=>"Medical and Surgical History", "retake"=>TRUE),
   "lymphatic_signs_and_symptoms"						=>	array("label"	=>"Lymphatic Signs and Symptoms", "retake"=>TRUE),
   "pregnancy_form"										=>	array("label"	=>"Pregnancy Form"),
   "quality_of_life_in_the_face_of_lymphatic_disease"	=>	array("label"	=>"Quality of Life in the Face of Lymphatic Disease", "retake"=>TRUE),
   "lymphatic_treatment_and_procedures"					=>	array("label"	=>"Lymphatic Treatment and Procedures"),
   "family_members_general"								=>	array("label"	=>"Family Members General"),
   "family_members_your_generation_siblings"			=>	array("label"	=>"Family Members Your Generation Siblings"),
   "family_members_children"							=>	array("label"	=>"Family Members Subsequent Generations"),
   "family_members_other_affected_relatives"			=>	array("label"	=>"Family Members Other Affected Relatives"),
   "feedback_survey"									=>	array("label"	=>"Feedback Survey")
);

$followup_forms = array(
   "follow_up_demographics"					=>	array("label"	=>"Followup Demographics"),
   "follow_up_family_members"				=>	array("label"	=>"Followup Family Members"),
   "follow_up_healthcare_provider"			=>	array("label"	=>"Followup Healthcare Provider Information"),
   "follow_up_lymphatic_diagnosis"			=>	array("label"	=>"Followup Lymphatic Diagnosis"),
   "follow_up_tissue_bank_genetics"			=>	array("label"	=>"Followup Tissue Bank Genetics"),
   "follow_up_medical_and_surgical_history"	=>	array("label"	=>"Followup Medical and Surgical History")
);

logIt("Starting learn...");

$record = $loggedInUser->user_id;
//logIt( "getConsentStatus RECORD " .  $record, "DEBUG");

if (!informedConsented($record)) {
	echo "<p>Surveys are not available until you have completed the consent form.</p>";
	$link = getSurveyLink($record, 'informed_consents', EVENT_1);
	echo "<a href=\"$link\" class=\"list-group-item \">Informed consent</a>";
} else {
	$instrument_list 	= array_keys($learn_forms);
	$result 			= getAllCompletionStatus($record,$instrument_list,EVENT_1);
	$status 			= $result[0];

	//collect the status data
	foreach ($status as $form=>$form_status) {
		logIt("STATUS for ". $form . " is " .$form_status);
		$learn_forms[substr($form,0,-9)]["status"] = $form_status;
		//status is wrong for incomplete surveys  (status = 0).
	}

	echo "<p>Please complete all forms listed below. </p>
		   <p>Completed forms are in gray.  Forms with the <span class=\"glyphicon glyphicon-repeat\"></span> icon can be retaken.</p>";

	// TO DETERMINE WHETHER FOLLOWUP, check emails   
	// $emails = retrieveEmailsSent($record);
	// logIt("EMAILS: " . print_r($emails, true));
    
	foreach ($learn_forms as $form=>$form_data) {
		$label = $form_data["label"];
		if ((($form_data["status"]) == 2) or (array_key_exists('retake', $form_data))) {
                //Check if this is one of the retakable surveys                                                                                                                                                                                                                                   
			if (array_key_exists('retake', $form_data)) {
				$retakeHash = getRetakeHash($record, $form, EVENT_1);
				$link = getSurveyLink($record, $form, EVENT_1);
				// logIt($form. "RETAKE HASH: ".$retakeHash . " with this status " .$form_data["status"]);

				$grey = "list-group-item";
				if (($form_data["status"]) == 2) {
					// logIt($form. " should be disabled ".$retakeHash . " with this status " .$form_data["status"]);
					$grey .= " retake";
				}
				?>
					<div class="<?php echo $grey?>" redirect=<?php echo $retakeHash?> link=<?php echo $link?> onclick="javascript: doRedirect($(this));">
						<span class="glyphicon glyphicon-repeat" ></span>
						<?php echo $label ?>
					</div>  
				<?php 
				} else {
			 	?>
					<div class="list-group-item disabled">
						<span class="glyphicon glyphicon-ok-sign">
						</span>
						<?php echo $label ?>
					</div>   		 
				<?php
					// echo "  <li class=\"list-group-item disabled \">$label</li>";
				}
	  	} else {
			//check if there is logic to show
			if ($form == 'pregnancy_form') {
				$pregnant = getFieldStatus($record, 'pregnancy',EVENT_1);	
				if (!$pregnant) {
				   continue;
				}
			}

			if ($form == 'family_members_your_generation_siblings') {
				$siblings = getFieldStatus($record, 'siblings_exist', EVENT_1);
				if (!$siblings) {
				   continue;
				}
			}

			if ($form == 'family_members_other_affected_relatives') {
				$relatives = getFieldStatus($record, 'have_family_info', EVENT_1);
				if (!$relatives) {
				   continue;
				}
			} 

			$link = getSurveyLink($record, $form, EVENT_1);
			//logIt( "This is the LINK".$link, "DEBUG");
		?>
			<div class="list-group-item" link=<?php echo $link?> onclick="javascript: doRedirect($(this));">
				<span class="glyphicon glyphicon-play-circle" ></span>
				<?php echo $label ?>
			</div>    		 
		<?php 
      	}	
	}
?>
	<form id="resumeSurveyForm" method="post" action="">
	<input type="hidden" id="__code" name="__code" />
	</form>
<?php 
}
?>
		</div>
      </div> <!-- jumbotron -->
   </div> <!-- row -->
</div> <!-- container -->

<div class='container'>
   <div class="row">
      <div class="jumbotron">
        <h3>Followup Surveys</h3>
        <p>Please check back in approximately six months to update your records about any new developments in your medical condition that may have occurred.</p>  
      </div> <!-- jumbotron -->
   </div> <!-- row -->
</div> <!-- container -->

<script type='text/javascript'>
	function doRedirect(caller) {
		var redirectHash1 	= caller.attr('redirect');
		var surveyHash 		= caller.attr('hash');
		var link 			= caller.attr('link');

		// console.log("THIS IS THE redirectHash" +redirectHash + " and surveyHash" +surveyHash +" and link is " +link);
		$("#__code").val(redirectHash);
		$("#resumeSurveyForm").attr("action", link);
		$("#resumeSurveyForm").submit();

		return;
	}
</script>


