<?php
require_once("models/config.php");

//REDIRECT USERS THAT ARE ALREADY LOGGED IN AND CONSENTED TO THE PORTAL PAGE
if(isUserLoggedIn() && isUserActive()) { 
	$destination = $websiteUrl . "index.php";
	header("Location: " . $destination);
	exit; 
}else if(!isUserLoggedIn()) { 
	$destination = $websiteUrl . "login.php";
	header("Location: " . $destination);
	exit; 
}

$pg_title 		= "Consent | $websiteName";
$body_classes 	= "consent";
include("models/inc/gl_header.php");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
	<div id="main-content" class="col-md-8 col-md-offset-2" role="main">
		<div class="well row">
			<img src="assets/img/well_logo.png" class="print"/>
			<ul id="register_steps">
				<li><span>1</span> <?php echo lang("STEP_REGISTER") ?></li>
				<li><span>2</span> <?php echo lang("STEP_VERIFY") ?></li>
				<li class="on"><span>3</span> <?php echo lang("STEP_CONSENT") ?></li>
				<li><span>4</span> <?php echo lang("STEP_SECURITY") ?></li>
			</ul>
			<div class="consent_slides">
				<section  class="consent_disclaim">
					<h2><?php echo lang("CONSENT_WELCOME") ?></h2>
					<div id='irbexp'><i><?php echo lang("IRB_EXPIRATION") ?> : August 31, 2018</i></div> 
					<ul>
						<li><?php echo lang("CONSENT_BULLET_1") ?></li>
						<li><?php echo lang("CONSENT_BULLET_2") ?></li>
					</ul>
					<p><?php echo lang("CONSENT_CONTACT") ?></p>
				</section>
				<?php
					include("models/inc/well_consent_doc_ss.php");
				?>
			</div>
			<div class="submits">
				<form method="POST" action="account_setup.php" class="submits">
					<input type="hidden" name="consented" value="true"/>
					<button class="btn btn-info" role="back"><?php echo lang("GENERAL_BACK") ?></button>
					<button class="btn btn-info" role="next"><?php echo lang("GENERAL_NEXT") ?></button>
					<button type="submit" role="consent" class="btn btn-info agree"><?php echo lang("CONSENT_I_AGREE") ?></button>
					<button role="print" class="btn btn-info print"><?php echo lang("CONSENT_PRINT") ?></button>
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
button[role='next'] {display:none; }
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

$("button[role='print']").click(function(){
	window.print();
	return false;
});
</script>