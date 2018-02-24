<!DOCTYPE html>
<html ng-app="RadarChart">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" />
  <link rel="stylesheet" href="assets/css/Radarstyle.css" />
</head>
<body class="container" ng-controller="MainCtrl as radar">
  <div class="main container">
    <h2>Results Summary</h2>
    <input type='hidden' ng-model="radar.exampleSelected" value="RadarUserCSV/Results"/>
    <div class="visualization">
        <radar csv="radar.csv" config="radar.config"></radar>

        <h3>Overall WELL-Being Score: <b><?php echo $_REQUEST["well_long_score"]?>/100</b></h3>
    </div>
  </div>
</body>
<script src="http://code.angularjs.org/1.3.5/angular.js"></script>
<script src="http://d3js.org/d3.v3.min.js"></script>

<script src="assets/js/appRadar.js"></script>
<script src="assets/js/radar.js"></script>
<script src="assets/js/radarDraw.js"></script>
<script>
  // Hack to make this example display correctly in an iframe on bl.ocks.org
  d3.select(self.frameElement).style("height", "1000px");
</script>
</html>