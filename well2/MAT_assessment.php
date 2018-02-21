<?php
include "MAT_scoring.php";
$mat_answers  = $_REQUEST["mat_answers"] ?: null;
$mat_answers  = json_decode($mat_answers,1);

$matstring  = "";
foreach($mat_answers as $fieldlabel => $values){
	$mat_key  = $values["vid"];
	$q_val    = $values["value"];
	$mat_category = $MAT_cat[$mat_key];
	$matvalue = getMATscoreCAT($mat_category,$q_val);
	$matstring .= $matvalue;
}

$matscore = isset($scoring[$matstring]) ? $scoring[$matstring] : 0 ;
$data[]   = array(
  "field_name"        => 'mat_score',
  "value"             => $matscore
);
$data   = array_shift($data);
$data["matstring"] = $matstring;
print_r( json_encode($data) );
exit;
?>