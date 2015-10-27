<?php
/* PASSWORD RESET - STEP 1: This is included upon confirmation of password reset email link */	

$attempt = getSessionPassResetAttempt();

// Confirm password and answer
$pass_reset_html = '';
$pass_reset_qa_panel = new bootstrapPanel();
$pass_reset_qa_panel->setType('primary');
$pass_reset_qa_panel->setIcon('lock');
$pass_reset_qa_panel->setHeader('<span class="headerText">Password Recovery</span>' . ($attempt>1 ? "<span class='pull-right'>Try #$attempt</span>": ''));

// Build html for each question/answer pair
$questions = array();
$validation_rules = array();
foreach ($password_reset_pairs as $i => $pair)
{
	// Question componet
	$questions[] = '
		<div class="mb-10">
			<h5>Question '.$i.':</h5>
			<div class="input-group mb-10">
				<span class="input-group-addon" id="sizing-addon3"><div style="width:15px;">Q:</div></span>
				<div class="form-control" name="'.$pair['question'].'" id="'.$pair['question'].'">' .
					$user->$pair['question'] . '
				</div>
			</div><!-- /input-group -->
			<div class="input-group">
				<span class="input-group-addon" id="sizing-addon3"><div style="width:15px;">A:</div></span>
				<input type="text" placeholder="Password Recovery Answer '.$i.'" class="form-control" aria-label="password recovery answer '.$i.'" name="'.$pair['answer'].'" id="'.$pair['answer'].'" value="">
			</div><!-- /input-group -->
		</div>';
	// jQuery validate rules
	$validation_rules[] = $pair['answer'].": { required: true }";
}

$pass_reset_qa_panel->setBody('
	<div>
		Please answer the password reset questions below.  The answers are not case sensitive but must otherwise be an exact match.
	</div>
	' . implode("<hr>",$questions)
);

$pass_reset_qa_panel->setFooter('
	<div class="text-right">
		<input type="submit" class="btn btn-default" name="submitPasswordResetAnswers" value="Submit" />
	</div>'
);



$page = new htmlPage("Password Reset | $websiteName");
$page->printStart();
//----------------------------------------------------------------------------------------------
include(PORTAL_INC_PATH . "/../../navbar.php");
?>
<div class='container'>
	<div class="row">
		<div class="max-600">
			<?php print getSessionMessages(); ?>
			<form role="form" action="" method="POST" id="submitPasswordResetForm" name="submitPasswordResetForm">
				<?php print $pass_reset_qa_panel->getPanel(); ?>
			</form>
		</div>
	</div><!-- /.row -->
</div>
<script type='text/javascript'>
	$( document ).ready(function() {
		// Validate
		$('#submitPasswordResetForm').validate({
			rules: {
				<?php echo implode(",",$validation_rules) ?>
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
		})
	});
</script>

<?php
//----------------------------------------------------------------------------------------------
$page->printEnd();
?>