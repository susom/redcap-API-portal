<nav class="navbar navbar-default navbar-learn">
	<div class="container">
		<div class="navbar-header"> 
			<div>
				<a class="navbar-brand" id="logo" href="<?= $websiteUrl; ?>"></a>
			</div>
		</div>
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="navbar">
			<ul class="nav navbar-nav navbar-right">
<?php if(isUserLoggedIn()) { ?>
				<li><a href="userpage.php">My Surveys</a></li>
				<li><a href="profile.php">User Settings</a></li>
				<li><a href="index.php?logout=1">Logout</a></li>
<?php } else { ?>
				<li><a href="index.php">Home</a></li>
				<li><a href="login.php">Login</a></li>
				<li><a href="register.php">Register</a></li>
<?php } ?>
			</ul>
		</div><!-- /.navbar-collapse -->     
	</div><!-- /.container-fluid -->
</nav>
<script type='text/javascript' ?>
	$( document ).ready(function() {
		var page = '<?php echo $PAGE ?>';
		$(".nav").find(".active").removeClass("active");
		$(".nav").find('a[href~="' + page + '"]').parent().addClass("active");
	});
</script>