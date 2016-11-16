<?php
$sleep_answers 	= $_POST["sleep"] ?: NULL;
$sleepar 		= json_decode($sleep_answers,1);
$qs 			= array();
foreach($sleepar as $i => $q){
	$qs[$q["name"]] = $q["value"];
}

$results 		= array();

// DURATION OF SLEEP
if($qs["psqi_actual_sleep"] >= 7){
	$results["PSQIDURAT"] = 0;
}else if($qs["psqi_actual_sleep"] < 7 && $qs["psqi_actual_sleep"] >= 6){
	$results["PSQIDURAT"] = 1;
}else if($qs["psqi_actual_sleep"] < 6  && $qs["psqi_actual_sleep"] >= 5){
	$results["PSQIDURAT"] = 2;
}else{
	$results["PSQIDURAT"] = 0;
}

//SLEEP DISTURBANCE
$disturbance 	= array();
$disturbance[] 	= $qs["psqi_wake_night"];
$disturbance[] 	= $qs["psqi_bathroom"];
$disturbance[] 	= $qs["psqi_breathe"];
$disturbance[] 	= $qs["psqi_snore"];
$disturbance[] 	= $qs["psqi_cold"];
$disturbance[] 	= $qs["psqi_hot"];
$disturbance[] 	= $qs["psqi_bad_dreams"];
$disturbance[] 	= $qs["psqi_pain"];
$disturbance[] 	= empty($qs["psqi_other"]) ? 0 : $qs["psqi_other"];
$total_disturb 	= array_sum($disturbance);
if($total_disturb == 0){
	$results["PSQIDISTB"] = 0;
}else if($total_disturb >= 1 && $total_disturb <= 9){
	$results["PSQIDISTB"] = 1;
}else if($total_disturb > 9 && $total_disturb <= 18){
	$results["PSQIDISTB"] = 2;
}else{
	$results["PSQIDISTB"] = 3;
}

//SLEEP LATENCY
if($qs["psqi_fall_asleep"] >= 0 && $qs["psqi_fall_asleep"] <= 15 ){
	$Q2_NEW = 0;
}else if($qs["psqi_fall_asleep"] >15 && $qs["psqi_fall_asleep"] <=30){
	$Q2_NEW = 1;
}else if($qs["psqi_fall_asleep"] >30  && $qs["psqi_fall_asleep"] <= 60){
	$Q2_NEW = 2;
}else{
	$Q2_NEW = 3;
}

//COMBINE Q5 + Q2NEW
$COMBO_LATENCY = $qs["psqi_sleep_30"] + $Q2_NEW;
if($COMBO_LATENCY == 0){
	$results["PSQILATEN"] = 0;
}else if($COMBO_LATENCY >= 1 && $COMBO_LATENCY <= 2){
	$results["PSQILATEN"] = 1;
}else if($COMBO_LATENCY >= 3 && $COMBO_LATENCY <= 4){
	$results["PSQILATEN"] = 2;
}else if($COMBO_LATENCY >= 5 && $COMBO_LATENCY <= 6){
	$results["PSQILATEN"] = 3;
}


//DAY DYSFUNCTION
$COMBO_DYSFUNCTION = $qs["psqi_staying_awake"] + $qs["psqi_enthusiasm"];
if($COMBO_DYSFUNCTION == 0){
	$results["PSQIDAYDYS"] = 0;
}else if($COMBO_DYSFUNCTION >= 1 && $COMBO_DYSFUNCTION <= 2){
	$results["PSQIDAYDYS"] = 1;
}else if($COMBO_DYSFUNCTION >= 3 && $COMBO_DYSFUNCTION <= 4){
	$results["PSQIDAYDYS"] = 2;
}else if($COMBO_DYSFUNCTION >= 5 && $COMBO_DYSFUNCTION <= 6){
	$results["PSQIDAYDYS"] = 3;
}

//SLEEP EFFICIENCY
//convert to military time at least
if($qs["psqi_to_bed_ampm"] == 1){
	$tobed_hour = $qs["psqi_to_bed_hr"];
	if($qs["psqi_to_bed_hr"] == 12){
		$tobed_hour = 0;
	}
}else{
	$tobed_hour = $qs["psqi_to_bed_hr"] + 12;
	if($qs["psqi_to_bed_hr"] == 12){
		$tobed_hour = 12;
	}
}
$tobed_seconds = ($tobed_hour * 60 * 60) +  ($qs["psqi_to_bed_min"] * 60);

if($qs["psqi_gotten_up_ampm"] == 1){
	$wake_hour = $qs["psqi_gotten_up_hr"];
	if($qs["psqi_gotten_up_hr"] == 12){
		$wake_hour = 0;
	}
}else{
	$wake_hour = $qs["psqi_gotten_up_hr"] + 12;
	if($qs["psqi_gotten_up_hr"] == 12){
		$wake_hour = 12;
	}
}
$wake_seconds = ($wake_hour * 60 * 60) +  ($qs["psqi_gotten_up_min"] * 60);
if($qs["psqi_to_bed_ampm"] > $qs["psqi_gotten_up_ampm"]){
	//normal sleep pm, wake am
	$sleep_seconds = (24*60*60) - $tobed_seconds + $wake_seconds;
}else{
	//weird sleep am, wake pm , or same day
	$sleep_seconds = $wake_seconds - $tobed_seconds;
}




$newtib = $sleep_seconds/3600;
$time_phase 			= ($qs["psqi_actual_sleep"]/$newtib)*100;
if($time_phase >= 85){
	$results["PSQIHSE"] = 0;
}else if($time_phase < 85 && $time_phase >= 75){
	$results["PSQIHSE"] = 1;
}else if($time_phase < 75 && $time_phase >= 65){
	$results["PSQIHSE"] = 2;
}else{
	$results["PSQIHSE"] = 3;
}

//OVERALL SLEEP QUALITY
$results["PSQISLPQUAL"] = $qs["psqi_sleep_overall"];
$results["PSQIMEDS"] 	= $qs["psqi_sleep_medicine"];

print_r($results);

//FINAL SCORE
//MIN = 0 BEST
//MAX = 21 WORST
//I AM 11

$PSQI = array_sum($results);



if($PSQI <= 5){
	//good quality sleep
}else{
	//poor quality sleep
}

print_r( $PSQI ) ;
?>