</div>
</div>
</body>
</html>
<script>
$(document).on('click', function(event) {
	if ($(event.target).closest('.alert').length) {
		$(".alert").fadeOut("fast",function(){
			$(".alert").remove();
		});
	}
  	
});
</script>
<?php
// $end_time = microtime(true) - $start_time;
// print_r($end_time . " seconds");
// exit;
?>