
<?php
$gender 	= isset($_REQUEST["gender"]) ? $_REQUEST["gender"] : NULL;
$metscore 	= isset($_REQUEST["metscore"]) ? $_REQUEST["metscore"] : NULL;

function getMetDesc($gender, $metscore){

}
if($gender == "male"){
?>
<div id="met_results">
<div id="met_score"></div>
<div id="met_desc">
	<h2>MET Fitness Value: Male</h2>
	
	<dl data-value="3">
	<dt>< 3 METs</dt>
	<dd>Your current level of cardio respiratory fitness indicates severely limited functional capacity. If you are confident that you have completed this activity using the correct information, we recommend you contact your medical provider to discuss your cardio respiratory fitness further.</dd>	
	</dl>

	<dl data-value="5">
	<dt>3-5 METs</dt>
	<dd>Your current level of cardio respiratory fitness is associated with deconditioned fitness.</dd>	
	</dl> 

	<dl data-value="8">
	<dt>8 METs</dt>
	<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of men aged 70-79.</dd>	
	</dl> 

	<dl>
	<dt data-value="9">9 METs</dt>
	<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of men aged 60-69.</dd>	
	</dl> 

	<dl data-value="10">
	<dt>10 METs</dt>
	<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of men aged 50-59.</dd>	
	</dl> 

	<dl data-value="11">
	<dt>11 METs</dt>
	<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of men aged aged 40-49.</dd>	
	</dl> 

	<dl data-value="12">
	<dt>12 METs</dt>
	<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of men aged aged 20-39.</dd>	
	</dl> 

	<dl data-value="13">
	<dt>13 METs</dt>
	<dd>Your current level of cardio respiratory fitness is associated with excellent health.</dd>	
	</dl>

	<dl data-value="18">
	<dt>18 METs</dt>
	<dd>Your current level of cardio respiratory fitness is equivalent to that of elite endurance athletes! Great work!</dd>	
	</dl>

	<dl data-value="20">
	<dt>20 METs</dt>
	<dd>Wow! Your current level of cardio respiratory fitness is equivalent to that of world-class athletes!</dd>	
	</dl>
</div>
</div>
<?php
	exit;
}

?>
<div id="met_results">
<h2>MET Fitness Value: Female</h2>
<div id="met_score">12</div>
<dl>
<dt>< 3 METs</dt>
<dd>Your current level of cardio respiratory fitness indicates severely limited functional capacity. If you are confident that you have completed this activity using the correct information, we recommend you contact your medical provider to discuss your cardio respiratory fitness further.</dd>	
</dl>

<dl>
<dt>3-5 METs</dt>
<dd>Your current level of cardio respiratory fitness is associated with deconditioned fitness.</dd>	
</dl>

<dl>
<dt>8 METs</dt>
<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of women aged 50-79.</dd>	
</dl> 

<dl>
<dt>9 METs</dt>
<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of men aged 40-49.</dd>	
</dl> 

<dl>
<dt>10 METs</dt>
<dd>Your current level of cardio respiratory fitness is equivalent to the expected capacity of men aged 20-39.</dd>	
</dl> 

<dl>
<dt>18 METs</dt>
<dd>Your current level of cardio respiratory fitness is equivalent to that of elite endurance athletes! Great work!</dd>	
</dl>

<dl>
<dt>20 METs</dt>
<dd>Wow! Your current level of cardio respiratory fitness is equivalent to that of world-class athletes!</dd>	
</dl>
</div>
