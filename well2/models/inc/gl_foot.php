	<?php include_once("gl_socialfoot.php") ?>
</div>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="assets/js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
<!-- Bootstrap -->
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
 <script src="assets/js/slimscroll/jquery.slimscroll.min.js"></script>
<script src="assets/js/charts/easypiechart/jquery.easy-pie-chart.js"></script>
<script src="assets/js/charts/sparkline/jquery.sparkline.min.js"></script>
<script src="assets/js/charts/flot/jquery.flot.min.js"></script>
<script src="assets/js/charts/flot/jquery.flot.tooltip.min.js"></script>
<script src="assets/js/charts/flot/jquery.flot.spline.js"></script>
<script src="assets/js/charts/flot/jquery.flot.pie.min.js"></script>
<script src="assets/js/charts/flot/jquery.flot.resize.js"></script>
<script src="assets/js/charts/flot/jquery.flot.grow.js"></script>
<script src="assets/js/charts/flot/demo.js"></script>
<script src="assets/js/calendar/bootstrap_calendar.js"></script>
<script src="assets/js/calendar/demo.js"></script>
<script src="assets/js/sortable/jquery.sortable.js"></script>
<script src="assets/js/app.plugin.js"></script> 
<script src="assets/js/jquery.visible.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="assets/js/jquery.simpleWeather.js"></script>
<script src="assets/js/weather.js"></script>
<script src="assets/js/roundslider.js"></script>
<script src="assets/js/verify.notify.min.js"></script>
<script src="assets/js/underscore-min.js"></script>
<script src="assets/js/main.js"></script>
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

$("a.disabled").click(function(){
  return false;
});

// this will wait to show alert boxes until after page loads
$(".alert").css("opacity",1);
</script>
<?php
