<?php
$tcmanswers 	= isset($_REQUEST["tcm_answers"]) ? $_REQUEST["tcm_answers"] : NULL;
$tcmanswers 	= json_decode($tcmanswers,1);
$tcm_answers 	= array();
foreach($tcmanswers as $userans){
	$tcm_answers[$userans["name"]] = $userans["value"];
}

$tcm_reqs = array();
$tcm_reqs[0] = array('tcm_energy','tcm_optimism','tcm_weight','tcm_stool','tcm_loosestool','tcm_stickystool');
$tcm_reqs[1] = array('tcm_energy','tcm_voice','tcm_panting','tcm_tranquility','tcm_colds','tcm_pasweat');
$tcm_reqs[2] = array('tcm_handsfeet_cold','tcm_cold_aversion','tcm_sensitive_cold','tcm_cold_tolerant','tcm_pain_eatingcold','tcm_sleepwell');
$tcm_reqs[3] = array('tcm_handsfeet_hot','tcm_face_hot','tcm_dryskin','tcm_dryeyes','tcm_constipated','tcm_drylips');
$tcm_reqs[4] = array('tcm_sleepy','tcm_sweat','tcm_oily_forehead','tcm_eyelid','tcm_snore','tcm_naturalenv');
$tcm_reqs[5] = array('tcm_frustrated','tcm_nose','tcm_acne','tcm_bitter','tcm_ribcage','tcm_scrotum');
$tcm_reqs[6] = array('tcm_forget','bruises_skin','tcm_capillary_cheek','tcm_complexion','tcm_darkcircles','tcm_bodyframe');
$tcm_reqs[7] = array('tcm_depressed','tcm_anxious','tcm_melancholy','tcm_scared','tcm_suspicious','tcm_breastpain');
$tcm_reqs[8] = array('tcm_sneeze','tcm_cough','tcm_allergies','tcm_hives','tcm_skin_red');

$tcm_types 		= array(
	 "Balanced Constitution"
	,"Qi Deficiency Constitution"
	,"Yang Deficiency Constitution"
	,"Yin Deficiency Constitution"
	,"Phlegm-dampness Constitution"
	,"Damp-heat Constitution"
	,"Blood Stasis Constitution"
	,"Qi Stagnant Constitution"
	,"Inherited Special Constitution"
);

$tcm_map = array();
foreach($tcm_reqs as $key => $reqset){
	$temp = array_map(function($item) use ($tcm_answers){
		if(!isset($tcm_answers[$item])){
			exit;
		}
		$val 	= $tcm_answers[$item];
		return $val;
	},$reqset);
	$tcm_map[$tcm_types[$key]] = $temp;
}

function getBodyConstitution($constitutions,$type){
	$constitution 	= $constitutions[$type];
	$qs 			= count($constitution);
	$sum 			= array_sum($constitution);
	$theratio 		= $sum/($qs*5);

	if($type == "Balanced Constitution"){
		$others = array();
		foreach($constitutions as $i => $other){
			$others = array_merge($others,$other);
		}
		$oqs 		= count($others);
		$osum 		= array_sum($others);
		$oratio 	= $osum/($oqs*5);


		$determination = "Negative";
		if($theratio >= .7 && $oratio < .5){
			$determination = "Positive";
		}else if($theratio >= .7 && $oratio < .6){
			$determination = "Essentially Positive";
		}
	}else{
		$determination = "Negative";
		if($theratio >= .6){
			$determination = "Positive";
		}else if($theratio >= .5 && $theratio < .6){
			$determination = "Tendency to Positive";
		}
		$oratio = null;
	}
	return array("result" => $theratio, "determination" => $determination, "other_ratio" => $oratio);
}

$grouping = array();
foreach($tcm_map as $set => $qs){
	$tcm = getBodyConstitution($tcm_map, $set);
	$key = (strpos($tcm["determination"],"Positive") > 1) ? "<span>Tendency Positive</span> / <span>Essentially Positive</span>" : $tcm["determination"];
	$grouping[$key][] = $set;
	// echo "<dl class='constitution_result'><dt>$set</dt><dd>" . $tcm["determination"] ."</dd></dl>";
}
?>
<div id="tcm_results">
	<?php
	$group_size = array();
	foreach($grouping as $determination => $type){
		$group_size[] = count($type);
	}
	$max = max($group_size);
	$min = min($group_size);
	foreach($grouping as $determination => $type){
		$perc = count($type) == $max ? 100 : (count($type) == $min ? 50 : 75);
		echo "<div class='group'>";
		echo "<div class='subgroup $determination' style='width:$perc%'>";
		echo "<h4>$determination</h4>";
		echo "<ul>";
		echo "<li>";
		echo implode("</li><li>",$type);
		echo "</li>";
		echo "</ul>";
		echo "</div>";
		echo"</div>";
	}
	?>
</div>