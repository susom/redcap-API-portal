<?php
$firstname  = $loggedInUser->firstname;
$lastname   = $loggedInUser->lastname;
$city       = $loggedInUser->city;
$state      = $loggedInUser->state;
$location   = $city . "," . $state;
?>
<!DOCTYPE html>
<html lang="en" class="app">
<head>  
  <meta charset="utf-8" />
  <title><?php echo $pg_title ?></title>
  <meta name="description" content="app, web app, responsive, admin dashboard, admin, flat, flat ui, ui kit, off screen nav" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /> 
  <link href='https://fonts.googleapis.com/css?family=Dancing+Script' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
  <link rel="stylesheet" href="css/animate.css" type="text/css" />
  <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css" />
  <link rel="stylesheet" href="css/icon.css" type="text/css" />
  <link rel="stylesheet" href="css/font.css" type="text/css" />
  <link rel="stylesheet" href="css/app.css" type="text/css" />  
  <link rel="stylesheet" href="js/calendar/bootstrap_calendar.css" type="text/css" />

  <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.10.3/themes/ui-lightness/jquery-ui.css"/>
  <link rel="stylesheet" href="css/weather.css" />
  <link rel="stylesheet" href="css/roundslider.css" />
  <link rel="stylesheet" type="text/css" href="css/custom.css"/>
  
  <script src="js/jquery.min.js"></script>
  <!--[if lt IE 9]>
    <script src="js/ie/html5shiv.js"></script>
    <script src="js/ie/respond.min.js"></script>
    <script src="js/ie/excanvas.js"></script>
  <![endif]-->
</head>
<?php
if($portal_test){
  echo "<div id='testserver'>Test Server</div>";
}
?>
<body class="<?php echo $body_classes ?>">
<?php
  print getSessionMessages();
?>