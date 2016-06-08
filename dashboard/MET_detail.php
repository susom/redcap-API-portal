
<?php
$gender 	= isset($_REQUEST["gender"]) 	? $_REQUEST["gender"] : NULL;
$age 		= isset($_REQUEST["age"]) 		? $_REQUEST["age"] : NULL;
$metscore 	= isset($_REQUEST["metscore"]) 	? $_REQUEST["metscore"] : NULL;

$suggestion = array(
		 array("Needs Improvement" , "Your current level of fitness indicates that you could improve, consider engaging in physical activity more regularly.")
		,array("Healthy Zone" , "Your current level of fitness is associated with good health, nice work!")
		,array("Excellent Zone" , "Your current level of fitness is associated with excellent health, great job!")
	);
if($gender == "male"){
	if($age <= 39 ){
		if($metscore < 9){
			$level = 0;
		}else if($metscore <= 14){
			$level = 1;
		}else{
			$level = 2;
		}
	}else if($age <= 59){
		if($metscore < 8){
			$level = 0;
		}else if($metscore <= 13){
			$level = 1;
		}else{
			$level = 2;
		}
	}else{
		if($metscore < 7){
			$level = 0;
		}else if($metscore <= 12){
			$level = 1;
		}else{
			$level = 2;
		}
	}
}else{
	if($age <= 39 ){
		if($metscore < 7){
			$level = 0;
		}else if($metscore <= 12){
			$level = 1;
		}else{
			$level = 2;
		}
	}else if($age <= 59){
		if($metscore < 6){
			$level = 0;
		}else if($metscore <= 11){
			$level = 1;
		}else{
			$level = 2;
		}
	}else{
		if($metscore < 5){
			$level = 0;
		}else if($metscore <= 10){
			$level = 1;
		}else{
			$level = 2;
		}
	}
}

$suggest = $suggestion[$level];
?>
<div id="met_results" class="level_<?php echo $level?>">
<div id="met_score"></div>
<div id="met_desc">
	<h2><?php echo $suggest[0] ?></h2>
	<dl>
	<dt><?php echo $suggest[1] ?></dt>
	</dl>
</div>
</div>
