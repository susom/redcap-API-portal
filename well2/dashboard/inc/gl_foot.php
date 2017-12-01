  <!-- Bootstrap -->
  <script src="js/bootstrap.js"></script>
  <!-- App -->
  <script src="js/app.js"></script>
  <script src="js/slimscroll/jquery.slimscroll.min.js"></script>
  <script src="js/charts/easypiechart/jquery.easy-pie-chart.js"></script>
  <script src="js/charts/sparkline/jquery.sparkline.min.js"></script>
  <script src="js/charts/flot/jquery.flot.min.js"></script>
  <script src="js/charts/flot/jquery.flot.tooltip.min.js"></script>
  <script src="js/charts/flot/jquery.flot.spline.js"></script>
  <script src="js/charts/flot/jquery.flot.pie.min.js"></script>
  <script src="js/charts/flot/jquery.flot.resize.js"></script>
  <script src="js/charts/flot/jquery.flot.grow.js"></script>
  <script src="js/charts/flot/demo.js"></script>
  <script src="js/calendar/bootstrap_calendar.js"></script>
  <script src="js/calendar/demo.js"></script>
  <script src="js/sortable/jquery.sortable.js"></script>
  <script src="js/app.plugin.js"></script>
  <script src="js/jquery.visible.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
  <script src="js/jquery.simpleWeather.js"></script>
  <script src="js/weather.js"></script>
  <script src="js/roundslider.js"></script>
  <script src="js/verify.notify.min.js"></script>
  <script src="js/jquery.maskedinput.min.js"></script>
  <script src="js/underscore-min.js"></script>
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.2.7/min/inputmask/jquery.inputmask.min.js"></script> -->
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
</script>