<?php
$grit_answers 	= $_POST["grit"] ?: NULL;
$gender 		= $_POST["gender"] ?: NULL;

$grit_scoring 	= array();
$grit_scoring[] = "grit_ideas_distract"		;
$grit_scoring[] = "grit_interests_change"	;
$grit_scoring[] = "grit_setbacks_discourage";
$grit_scoring[] = "grit_hard_worker"		;
$grit_scoring[] = "grit_different_goal"		;
$grit_scoring[] = "grit_focus_months"		;
$grit_scoring[] = "grit_finish_begin"		;
$grit_scoring[] = "grit_diligent"			;
$grit_scoring[] = "grit_achieved_goal"		;
$grit_scoring[] = "grit_idea_interest"		;

$grit_count = 0;
if(!is_null($grit_answers)){
	$grit_answers = json_decode($grit_answers,1);
	foreach($grit_answers as $user_answer){
		if(in_array($user_answer["name"],$grit_scoring)){
			$grit_count += $user_answer["value"];
		}
	}
}
$gender 	= $gender % 2 == 0 ? "women" : "men";
$gritscore 	= number_format($grit_count/10, 1);

if($gender == "men"){
	if($gritscore < 2.5){
		$animtime 	= 0;
	}elseif($gritscore < 2.8){
		$animtime 	= 1;
	}elseif($gritscore < 3.0){
		$animtime 	= 2;
	}elseif($gritscore < 3.2){
		$animtime 	= 3;
	}elseif($gritscore < 3.3){
		$animtime 	= 4;
	}elseif($gritscore < 3.5){
		$animtime 	= 5;
	}elseif($gritscore < 3.7){
		$animtime 	= 6;
	}elseif($gritscore < 3.9){
		$animtime 	= 7;
	}elseif($gritscore < 4.2){
		$animtime 	= 8;
	}elseif($gritscore < 5.0){
		$animtime 	= 9;
	}else{
		$animtime 	= 10;
	}
}else{
	if($gritscore < 2.5){
		$animtime 	= 0;
	}elseif($gritscore < 2.8){
		$animtime 	= 1;
	}elseif($gritscore < 3.1){
		$animtime 	= 2;
	}elseif($gritscore < 3.2){
		$animtime 	= 3;
	}elseif($gritscore < 3.5){
		$animtime 	= 4;
	}elseif($gritscore < 3.6){
		$animtime 	= 5;
	}elseif($gritscore < 3.8){
		$animtime 	= 6;
	}elseif($gritscore < 4.0){
		$animtime 	= 7;
	}elseif($gritscore < 4.2){
		$animtime 	= 8;
	}elseif($gritscore < 5.0){
		$animtime 	= 9;
	}else{
		$animtime 	= 10;
	}
}
//DO SOME PROCESSING
$grit_score 	= $gritscore . "/5.0";
$grit_perc 		= $animtime*10;

$level = array();
$level[0] 		= "zero";
$level[1] 		= "ten";
$level[2] 		= "twenty";
$level[3] 		= "thirty";
$level[4] 		= "forty";
$level[5] 		= "fifty";
$level[6] 		= "sixty";
$level[7] 		= "seventy";
$level[8] 		= "eighty";
$level[9] 		= "ninety";
$level[10] 		= "one_hundred";

?>
<div id="grit_results" class="<?php echo $level[$animtime] ?>" data-animation-time="<?php echo $animtime ?>">
	<ul>
		<li class="one_hundred">100%</li>
		<li class="ninety">90%</li>
		<li class="eighty">80%</li>
		<li class="seventy">70%</li>
		<li class="sixty">60%</li>
		<li class="fifty">50%</li>
		<li class="forty">40%</li>
		<li class="thirty">30%</li>
		<li class="twenty">20%</li>
		<li class="ten">10%</li>
	</ul>
	<div class="grit_score_bubble">
		<h3 class="grit_score"><?php echo $grit_score ?></h3>
		<p>You scored better than <span class="grit_perc"><?php echo $grit_perc ?></span>% of American Adults</p>
	</div>
	<div class="sisyphus"></div>
</div>