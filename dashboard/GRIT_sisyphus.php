<?php
$grit_answers 	= $_POST["grit"] ?: NULL;

$animtime 		= 6;

$level = array();
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
		<h3 class="grit_score">3.0/5</h3>
		<p>You acored better than <span class="grit_perc">60</span>% of American Adults</p>
	</div>
	<div class="sisyphus"></div>
</div>