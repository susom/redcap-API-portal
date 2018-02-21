<?php
$sleep_answers 	= $_POST["sleep"] ?: NULL;
$sleepar 		= json_decode($sleep_answers,1);

$qs 			= array();
foreach($sleepar as $i => $q){
	$qs[$q["name"]] = $q["value"];
}

$results 		= array();

// DURATION OF SLEEP
$actual_sleep 	= ($qs["psqi_actual_sleep_mm"] + ($qs["psqi_actual_sleep_hh"]*60))/60;


if($actual_sleep >= 6 && $actual_sleep < 7){
	$duration_val 	= 1;
}else if($actual_sleep >= 5 && $actual_sleep < 6){
	$duration_val 	= 2;
}else if($actual_sleep < 5){
	$duration_val 	= 3;
}else{
	$duration_val 	= 0;
}
$results["PSQIDURAT"] = $duration_val;

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
$Q2_NEW = $qs["psqi_fall_asleep"];


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


$newtib 		= $sleep_seconds/3600;
$time_phase 	= ($actual_sleep/$newtib)*100;

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

//FINAL SCORE
//MIN = 0 BEST
//MAX = 21 WORST
//I AM 11

$PSQI = array_sum($results);
?>
<style>
.psqi_desc { display:none; }
#PSQI div { display:none; }
<?php
foreach($results as $datapoint => $value){
	if($value >= 1){
		echo "#".$datapoint . " { display:block; }\n";
	}
}

if($PSQI < 5){
	echo "#PSQI div.less_than_5 { display:block; }\n";
}else{
	echo "#PSQI div.greater_than_5 { display:block; }\n";
}
?>
</style>
<div id="psqi_results">
	<h3>Thank you for your participation! Your PSQI is : <b id="psqi_score"><?php echo $PSQI ?></b></h3>
	<div id="psqi_slider"></div>
	
	<div id="PSQI">
		<div class="less_than_5">
			<p>Nice job! Your sleep survey score is associated with good quality sleep.  You understand the importance of sleep to your health and wellbeing and have made it a priority in your daily routine.  Good quality sleep is associated with better learning, memory, cellular repair and muscle building, better immune function, and can lead to living a healthier and more productive life.  Keep it up!</p>
			<p>Check out our <u><b><a href='sleep_resources.php' onclick='window.open(this.href,"sleep_resources","width=780,height=980, left=450, top=100, toolbar=0, menubar=0, status=1"); return false;'>Sleep Resources page</a></b></u> page to learn more about sleep, including tools for improving, maintaining, and tracking your excellent sleep quality.  While there, you can also access our <a href="https://youtu.be/sFJIMzBVnW0" target="blank">interview with a sleep expert</a>, where many common sleep questions from other study participants like you were answered.</p>
		</div>
		<div class="greater_than_5">
			<p>Your answers to the sleep survey are associated with poor quality sleep.  Sleep is a very important component to our health and wellbeing, and good quality sleep is associated with better learning, memory, cellular repair and muscle building, better immune function, and can lead to living a healthier and more productive life.  Poor quality sleep has been associated with reduced immunity, and health problems related to metabolism, appetite regulation, and stress response.</p>
			<p>Based on your personal sleep survey answers, the factors contributing to your poor quality sleep include the following:</p>
			<p>If you're interested in addressing these factors and improving your sleep quality, please check out our <a href='sleep_resources.php' onclick='window.open(this.href,"sleep_resources","width=780,height=980, left=450, top=100, toolbar=0, menubar=0, status=1"); return false;'>Sleep Resources page</a>, where we provide tips, strategies, and natural approaches for getting you to sleep and stay asleep. If you feel you need additional help, consider visiting your primary doctor, a sleep center, or a sleep specialist.  Use the <a href='http://www.sleepeducation.org/find-a-facility' target='_blank'>Find a Professional tool</a> to find a sleep specialist near you.></p>
		</div>
		
	</div>
	<div id="PSQIDURAT" class="psqi_desc">
		<h3>Short Duration of Sleep</h3>
		<p>The National Sleep Foundation recommends that adults between the ages of 18 and 64 receive between 7 and 9 hours of sleep every night, and between 7-8 hours for adults 65 or older.  If you are routinely getting less than 7 hours of sleep at night, it may be affecting your sleep quality.  </p>
	</div>
	<div id="PSQIDISTB" class="psqi_desc">
		<h3>Sleep Disturbance</h3>
		<p>Creating a sleep environment that is free of night time disturbance is critical to good quality sleep.  A sleep disturbance includes anything that wakes you during the night, including the need to use the bathroom, coughing or loud snoring, not being able to breath comfortably, feeling too hot or too cold, experiencing pain, bad dreams, or anything else that can cause you to wake in the night.  These sleep disturbances interrupt natural sleep cycles.  If you frequently awake in the night, take steps to create a better sleep environment.</p>
	</div>
	<div id="PSQILATEN" class="psqi_desc">
		<h3>Sleep Latency</h3>
		<p>Sleep latency refers to the amount of time that it takes you to fall asleep at night.  Good quality sleep is characterized by falling asleep within 15 minutes of your head hitting the pillow.  If you have difficulty falling asleep at night, there are steps you can take to prepare yourself and your environment for falling asleep faster.  </p>
	</div>
	<div id="PSQIDAYDYS" class="psqi_desc">
		<h3>Day Dysfunction due to Sleepiness</h3>
		<p>Day dysfunction is defined as having trouble staying awake while driving, eating, or engaging in social activities, or with maintaining enthusiasm for daily tasks due to lack of sleep.  One episode of day dysfunction within the past month is a characteristic of poor quality sleep and requires that one address the underlying problem, which is whatever is contributing to the poor quality sleep.    </p>
	</div>
	<div id="PSQIHSE" class="psqi_desc">
		<h3>Sleep Efficiency</h3>
		<p>Sleep efficiency refers to the amount of time spent in bed versus the amount of time actually asleep.  If you spend less than 85% of your time in bed actually sleeping, this is an indication of poor quality sleep. </p>
	</div>
	<div id="PSQISLPQUAL" class="psqi_desc">
		<h3>Overall Sleep Quality</h3>
		<p>We all deserve very good quality sleep and require it in order to thrive.  If you feel that your sleep quality is not as good as it could be, there are many steps that you can take to get you on the path to better sleep. </p>
	</div>
	<div id="PSQIMEDS" class="psqi_desc">
		<h3>Use of Sleep Medication</h3>
		<p>Taking prescription or over the counter medications within the past month to help you sleep, is an indication of poor quality sleep.  Medications can be useful for acute cases of insomnia, but are not recommended for long-term use.  There more natural treatments for insomnia that don’t involve taking medication, including various relaxation techniques, such as meditation, breathing exercises, and guided imagery.  Cognitive behavioral therapy has also been shown to be an effective treatment for insomnia.   </p>
	</div>
	<p><B><u><a href='sleep_tips.php' onclick='window.open(this.href,"sleep_resources","width=780,height=980, left=450, top=100, toolbar=0, menubar=0, status=1"); return false;' style="color:#369">Sleep Tips page</a></u></B></p>
	<p>If you feel you need additional help, consider visiting your primary doctor, a sleep center, or a sleep specialist.  Use the Find a Professional tool to find a sleep specialist near you.</p>
</div>
