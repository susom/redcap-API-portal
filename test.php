<?php
	
/* TEST FILE FOR INCLUDES */
//testing something real quick
//awesome merge confilict fixed
require_once('models/config.php');




//include(PORTAL_INC_PATH . "/password_reset_form.php");

echo "TEST START<hr><pre>";	
	
	
$path = dirname(__FILE__);
	
echo "\n path: $path";
//echo "\n PORTAL_BASE_PATH: " . PORTAL_BASE_PATH;
echo "\n PORTAL_INC_PATH: " . PORTAL_INC_PATH;


$p1 = "asdfasdf";
$p1_h = generateHash($p1);

$p2 = "asdfasdf";
$p2_h = generateHash($p2);

print "\n$p1";
print "\n$p2";
print "\n$p1_h " . strlen($p1_h);
print "\n$p2_h " . strlen($p1_h);

$p1_e = substr($p1_h, 25);
$p2_e = substr($p2_h, 25);

print "\n$p1_e " . strlen($p1_e);
print "\n$p2_e " . strlen($p1_e);


$uc = getUniqueCode();
print "\n$uc " . strlen($uc);

$rh = generateRandomString();
print "\n$rh " . strlen($rh);

?>
