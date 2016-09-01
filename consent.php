<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN AND CONSENTED TO THE PORTAL PAGE
if(isUserLoggedIn() && isUserActive()) { 
	$destination = $websiteUrl . "dashboard/index.php";
	header("Location: " . $destination);
	exit; 
}else if(!isUserLoggedIn()) { 
	$destination = $websiteUrl . "login.php";
	header("Location: " . $destination);
	exit; 
}

//RECORD ACTUAL CONSENT TIME BUTTON PUSH 
// if(isset($_REQUEST["consent_actual"])){
// 	$data         	= array();
// 	$record_id    	= $loggedInUser->id;
// 	$date 			= new DateTime;
// 	$date->setTimestamp(time()); 
// 	$consent_ts 	= $date->format('Y-m-d H:i:s');

// 	$data[] = array(
// 	  "record"            => $record_id,
// 	  "field_name"        => 'consent_agree_ts',
// 	  "value"             => $consent_ts
// 	);

// 	$result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), REDCAP_API_URL, REDCAP_API_TOKEN);
// 	print_r($data);
// 	exit;
// }

$pg_title 		= "Consent | $websiteName";
$body_classes 	= "consent";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
	<div id="main-content" class="col-md-8 col-md-offset-2" role="main">
		<div class="well row">
			<div class="consent_slides">
			<section  class="consent_disclaim">
				<h2>WELCOME!</h2> 
				<ul>
					<li>We need your permission before we can ask you any questions, so please read the following Informed Consent Document</li>
					<li>The initial survey will take 20-30 minutes to complete – but you don't need to fill it all out at one time</li>
					<li>We will check back in with you every few months</li>
					<li>We will add new surveys, materials, and content and invite you to participate over time</li>
				</ul>
				<p>FOR QUESTIONS ABOUT THE STUDY, CONTACT the Protocol Director, Sandra Winter at 650-723-8513.</p>
			</section>
			<?php
				include("models/inc/well_consent_doc_ss.php");
			?>
			</div>
			<div class="submits">
				<form method="POST" action="account_setup.php" class="submits">
					<input type="hidden" name="consented" value="true"/>
					<button class="btn btn-info" role="back">Back</button>
					<button class="btn btn-info" role="next">Next</button>
					<button type="submit" role="consent" class="btn btn-info agree">I Agree</button>
				</form>
			</div>
	  	</div>
	</div>
  </div>
</div>
<?php 
include("models/inc/gl_footer.php");
?>
<style>
button[role='back'],
.agree {display:none; }

</style>
<script>
//LOAD UP FIRST SLIDE
$(".consent_slides section").first().addClass("active");


// //I AGREE
// $("button[role='consent']").click(function(){
// 	var _this = $(this);

// 	//REDIRECT TO HOME WITH A MESSAGE
//     var dataURL         = "consent.php?ajax=1&consent_actual=1";
//     $.ajax({
//       url:  dataURL,
//       type:'POST',
//       success:function(result){
//       	console.log(result);
//       }
//     });
// });


//NEXT BUTTON
$("button[role='next']").click(function(){
	var _this = $(this);
	$(".consent_slides section.active").each(function(idx){
		var prevpanel = $( ".consent_slides section" ).index( $(this) );

		// console.log(prevpanel, $( ".consent_slides section" ).length);
		//make sure there is a previous section before showing backbutton
		if($(this).next().length){
			$(".consent .submits button[role='back']").show();
		}

		//IF THERE IS ANOTHER SECTION THEN ITS A "NEXT" ACTION OTHERWISE, FINAL SUBMIT
		if($(this).next().length){
		    if($(this).hasClass("active")){
		      $(this).removeClass("active").addClass("inactive");
		      $(this).next().addClass("active");

		      if(prevpanel == $( ".consent_slides section" ).length - 2){
		      	_this.hide();
		      	$(".submits button[role='consent']").show();
		      }
		      return false;
		    }
		}
	});
	return false;
});

$("button[role='back']").click(function(){
	var _this = $(this);
	$(".consent_slides section.active").each(function(idx){
		var prevpanel = $( ".consent_slides section" ).index( $(this) );
		
		//IF THERE IS ANOTHER SECTION THEN ITS A "NEXT" ACTION OTHERWISE, FINAL SUBMIT
		if($(this).prev().length){
		    if($(this).hasClass("active")){
		      $(this).removeClass("active").addClass("inactive");
		      $(this).prev().addClass("active");

		      if(prevpanel == 1){
		      	_this.hide();
		      }
		      return false;
		    }
		} 
	});
	return false;
});
</script>