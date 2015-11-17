<?
require_once("models/config.php");
$pg_title = "Login | $websiteName";

if(!isUserLoggedIn() || !isset($_GET["sid"])){
	header("Location: index.php");
	exit;
}

$sid              = $_GET["sid"];
$iframe_src       = "http://redcap.stanford.edu/surveys/?s=C4LCPHX4FL";
for($i = 1; $i < 6; $i++){
  $sid_class[$i] = null;
}
$sid_class[$sid ] = "class='hot'";

switch($_GET["sid"]){
  case 1:
  break;

  case 2:
  $iframe_src = "http://redcap.stanford.edu/surveys/?s=7yIVRhnMba";
  break;

  case 3:
  $iframe_src = "http://redcap.stanford.edu/surveys/?s=zXnFvdeSzI";
  break;
  
  case 4:
  $iframe_src = "http://redcap.stanford.edu/surveys/?s=8sFwdQBJ4V";
  break;
  
  case 5:
  $iframe_src = "http://redcap.stanford.edu/surveys/?s=7yIVRhnMba";
  break;
  
  default:
  
  break;
}

include("models/inc/gl_header.php");
?>
<!-- Customization Options:                       
     body class:   "home", "nav-1", "nav-2", etc. - specifies which item in the top nav to underline
                   "site-slogan" - display a site slogan in the header signature
     logo, h1  :   "hide" - hides the logo or H1 element, eg <h1 class="hide">
 -->
<body class="site-slogan consent">
<div id="su-wrap">
  <div id="su-content">
    <div id="brandbar">
      <div class="container"> 
        <a class="pull-left som_logo" href="http://www.stanford.edu"><img src="assets/lagunita/images/brandbar_logo_som.png" alt="Stanford University" width="176" height="23"></a> 
        
        <nav id="nosession" class="pull-right">
          <ul class="list-unstyled pull-right">
          </ul>
        </nav>
      </div>
    </div> 

    <?php
      include("models/inc/project_header.php");
    ?>

    <!-- main content -->
    <div id="content" class="container" role="main" tabindex="0">
      <div class="row"> 
      	<?php
			print getSessionMessages();
		?>

        <!-- Main column -->
        <div id="main-content" class="col-md-9" role="main">
          <iframe frameborder="0" width="100%" height="1000" scrolling="auto" name="eligibilityScreener" 
          src="<?php echo $iframe_src; ?>"></iframe>
        </div>
        <div id="sidebar-second" class="col-md-3">
            <div class="well">
              <h2>Survey Completion</h2>
              <h3 class="percent_complete">0%! <span>0 of 5 complete</span></h3>
              <div class="footer-links">
                <ul class="surveys">
                  <li <?php echo $sid_class[1] ?>><a class=" grapes" href="survey.php?sid=1">Screening Questions for the Wellness Living Laboratory</a></li>
                  <li <?php echo $sid_class[2] ?>><a class=" apple" href="survey.php?sid=2">Socio-Demographic Questions</a></li>
                  <li <?php echo $sid_class[3] ?>><a class=" orange" href="survey.php?sid=3">Health Behavior Questions</a></li>
                  <li <?php echo $sid_class[4] ?>><a class=" cherry" href="survey.php?sid=4">Social and Neighborhood Environment</a></li>
                  <li <?php echo $sid_class[5] ?>><a class=" banana" href="survey.php?sid=5">Wellness Questions</a></li>
                </ul>
              </div>
            </div>
            
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  include("models/inc/project_footer.php");
?>
</body>
<?php 
  include("models/inc/gl_footer.php");
?>

