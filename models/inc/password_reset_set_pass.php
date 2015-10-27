<?php
/* PASSWORD RESET - STEP 2: This is included upon confirmation of password reset questions */	
$page = new htmlPage("Password Reset | $websiteName");
$page->printStart();
//----------------------------------------------------------------------------------------------
include(PORTAL_INC_PATH . "/../../navbar.php");

?>
<div class='container'>
	<div class="row">
		<div class="max-600">
			<?php print getSessionMessages(); ?>
			<div class="max-400">
				<form role="form" action="" method="POST" id="resetPassword" name="resetPassword">
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h4>
								<span class="glyphicon glyphicon-lock" aria-hidden="true"></span>
								<span class="sr-only">Set New Password:</span>
								Set a New Password
							</h4>
						</div><!-- /.panel-header -->
					
						<div class="panel-body">
							<p>
								<label>Password:</label>
								<input type="password" autofocus placeholder="Enter New Password" class="form-control" aria-label="New Password" name="password" id="password">
							</p>
							<p>
								<label>Password Again:</label>
								<input type="password" placeholder="Confirm New Password" class="form-control" aria-label="Confirm Password" name="password_again" id="password_again">
							</p>
						</div><!-- /.panel-body -->
					
						<div class="panel-footer">
							<div class="text-right">
								<input type="submit" class="btn btn-primary" name="saveResetPassword" value="Submit" />
							</div>
						</div><!-- /.panel-footer -->
					</div><!-- /.panel -->
				</form>
			</div>
		</div>
	</div><!-- /.row -->
</div>

<script type='text/javascript'>
	$('#resetPassword').validate({
		rules: {
			password: {
				required: true,
				minlength: <?php echo PASSWORD_MIN_LENGTH ?>
			},
			password_again: {
				equalTo: "#password"
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
</script>

<?php
//----------------------------------------------------------------------------------------------
$page->printEnd();
?>