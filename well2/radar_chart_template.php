<!DOCTYPE html>
<html ng-app="RadarChart">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" />
  <link rel="stylesheet" href="assets/css/Radarstyle.css" />
</head>
<body class="container" ng-controller="MainCtrl as radar" data-csvfile="RadarUserCSV/<?php echo $_GET["id"]; ?>Results">
  <div class="main container">
    <h2>Results Summary</h2>
    <div class="visualization">
        <radar csv="radar.csv" config="radar.config"></radar>
        <i>*To review your score for each individual domain, hover over the data point with your mouse.</i>
        <h3>Overall WELL-Being Score: <b><?php echo $_GET["well_long_score"]?>/100</b></h3>
        <p>There are 10 domains that make up your overall well-being score.  This graph shows you how you scored in each of those domains.  For each domain, you can earn a maximum of 10 points, to total a possible overall well-being score of 100 points.  A lower score in the domain indicates more opportunity for growth.</p>
    </div>
  </div>
</body>
<script src="assets/js/angular.js"></script>
<script src="assets/js/d3.v3.min.js"></script>

<script src="assets/js/appRadar.js"></script>
<script src="assets/js/radar.js"></script>
<script src="assets/js/radarDraw.js"></script>
<script>
  // Hack to make this example display correctly in an iframe on bl.ocks.org
  d3.select(self.frameElement).style("height", "1000px");
</script>
</html>


