<?php
$tcmanswers 	= isset($_REQUEST["tcm_answers"]) ? $_REQUEST["tcm_answers"] : NULL;
$tcmanswers 	= json_decode($tcmanswers,1);
$tcm_answers 	= array();
foreach($tcmanswers as $userans){
	$tcm_answers[$userans["name"]] = $userans["value"];
}

$tcm_reqs = array();
$tcm_reqs[0] = array('tcm_energy','tcm_optimism','tcm_weight','tcm_stool','tcm_naturalenv','tcm_sleepwell');
$tcm_reqs[1] = array('tcm_energy','tcm_voice','tcm_panting','tcm_tranquility','tcm_colds','tcm_pasweat');
$tcm_reqs[2] = array('tcm_handsfeet_cold','tcm_cold_aversion','tcm_sensitive_cold','tcm_cold_tolerant','tcm_pain_eatingcold','tcm_loosestool');
$tcm_reqs[3] = array('tcm_handsfeet_hot','tcm_face_hot','tcm_dryskin','tcm_dryeyes','tcm_constipated','tcm_drylips');
$tcm_reqs[4] = array('tcm_sleepy','tcm_sweat','tcm_oily_forehead','tcm_eyelid','tcm_bodyframe','tcm_snore');
$tcm_reqs[5] = array('tcm_frustrated','tcm_nose','tcm_acne','tcm_bitter','tcm_stickystool','tcm_scrotum','tcm_discharge');
$tcm_reqs[6] = array('tcm_forget','tcm_bruises_skin','tcm_capillary_cheek','tcm_complexion','tcm_darkcircles','tcm_tongue');
$tcm_reqs[7] = array('tcm_depressed','tcm_anxious','tcm_melancholy','tcm_scared','tcm_suspicious','tcm_ribcage','tcm_breastpain');
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

//WTF IS UP WITH THE GENDER THING 
if(isset($tcm_answers["tcm_scrotum"]) && isset($tcm_answers["tcm_ribcage"])){
	$tcm_answers["tcm_gender"] = 5;
}
if(isset($tcm_answers["tcm_discharge"]) && isset($tcm_answers["tcm_breastpain"])){
	$tcm_answers["tcm_gender"] = 4;
}

if( $tcm_answers["tcm_gender"] == 5 ){
	unset($tcm_reqs[5][6]);
	unset($tcm_reqs[7][6]);
}
if( $tcm_answers["tcm_gender"] == 4 ){
	unset($tcm_reqs[5][5]);
	unset($tcm_reqs[7][5]);
}

$tcm_map = array();
foreach($tcm_reqs as $key => $reqset){
	$temp = array_map(function($item) use ($tcm_answers){
		if( $tcm_answers["tcm_gender"] == 5 && ($item == "tcm_discharge" || $item == "tcm_breastpain") ){
			return false;
		}else if( $tcm_answers["tcm_gender"] == 4 && ($item == "tcm_scrotum" || $item == "tcm_ribcage") ){
			return false;
		}
		
		if( !isset($tcm_answers[$item]) ){
			exit;
		}

		$val 	= $tcm_answers[$item];
		return $val;
	},$reqset);

	$tcm_map[$tcm_types[$key]] = $temp;
}

$tcm_def = array();
$tcm_det = array();
foreach($tcm_map as $set => $qs){
	$tcm = getBodyConstitution($tcm_map, $set);
	$tcm_det[] = $tcm["determination"];
	$tcm_def[] = $tcm["determination"] < 1 ? "hidetcm" : ($tcm["determination"] > 1 ? "positive" : "tendency");
}
	
function getBodyConstitution($constitutions,$type){
	$constitution 	= $constitutions[$type];
	$qs 			= count($constitution);
	$sum 			= array_sum($constitution);
	$theratio 		= $sum/($qs*5);
	$determination 	= 0;

	if($type == "Balanced Constitution"){
		$oratio_less_than_5 = true;
		$oratio_less_than_6 = true;
		foreach($constitutions as $i => $other){
			if($i == "Balanced Constitution"){
				continue;
			}

			$sum 				= array_sum($other);
			$total_possible 	= count($other) * 5;
			$constitution_ratio = $sum/$total_possible;

			if($constitution_ratio >= .5){
				$oratio_less_than_5 = false;
			}

			if($constitution_ratio >= .6){
				$oratio_less_than_6 = false;
			}

			// echo "<pre>";
			// print_r($i);
			// print_r($other);
			// print_r($constitution_ratio);
			// echo "</pre>";
		}
		
		if($theratio >= .7 && $oratio_less_than_5){
			$determination = 2;
		}else if($theratio >= .7 && $oratio_less_than_6){
			$determination = 1;
		}

		// echo "<pre>";
		// print_r($theratio);
		// print_r($determination);
		// echo "</pre>";
	}else{
		if($theratio >= .6){
			$determination = 2;
		}else if($theratio >= .5 && $theratio < .6){
			$determination = 1;
		}
	}
	return array("result" => $theratio, "determination" => $determination);
}
?>
<div id="tcm_results">
<table >
	<tr>
		<td>
			<table>
				<tr><td >Positive</td></tr>
				<tr><td style="height:100px; vertical-align:bottom">Tendency (Essentially) Positive</td></tr>
				<tr><td style="height:80px; vertical-align:bottom">Negative</td></tr>
			</table>	
		</td>
		<?php
			foreach($tcm_det as $det){
				$det = !$det ? "neg" : ($det > 1 ? "pos" : "mayb");
				echo "<td><div class='$det'></div></td>";
			}
		?>
	</tr>
	<tr class="type">
		<td></td>
		<td><span><?php echo implode("</span></td><td><span>",$tcm_types) ?></span></td>
	</tr>
</table>
<div class="constitutions">
	<h3>These are you body type constitutions, click on each one to learn more.</h3>
	<dl id="yang_def" class="constitution <?php echo $tcm_def[2] ?>">
	<dt>Guide for Yang-deficient Constitution</dt>
	<dd>
	<p>People with this constitution are physically weak with soft and loose muscles, and often have a  cold sensation in the limbs, stomach or abdomen, back, waist and knees.They wear heavier  clothes than others and do not like to stay in air-conditioned rooms. After eating cold food  or  drinks they may experience discomfort with abdominal distention and loose stools or diarrhea, or profuse  or incontinent urination. Most of these people are quiet and introverted.</p>
	<ol>
	<li><b>Diet</b>
		<p>Foods which can warm yang include garlic, onion, chives, ginger, chestnut, walnut, quinoa, quail eggs, black beans, beef, lamb and mutton. Baking and roasting are beneficial cooking methods. Foods should be eaten at room temperature or after heating. Drinks such as black tea, ginger tea, cinnamon tea, anise tea, fennel tea and raspberry tea are recommended. Avoid uncooked and cold food such as pears, watermelon, grapefruit, raw vegetables , cold milk, cold beer, ice cream and green tea .</p></li>
	<li><b>Work and rest</b>
		<p>It is important for people of yang-deficient constitution to keep warm, especially the feet, upper back and lower abdomen. It is particularly important to pay attention to seasonal temperature changes. In winter and autumn try to keep rooms well ventilated; in summer it is important to  avoid staying in air conditioned rooms for too long as it can predispose these people to catching colds. Profuse sweating can impair yang qi and needs to be avoided. Proper outdoor activities on sunny days  are recommended.</p></li>
	<li><b>Exercise</b>
		<p>Mild exercise such as jogging , walking and Taiji are recommended . Avoid strenuous exercise in summer and any exercise in adverse environmental conditions such as strong wind, bitter cold, heavy fog, heavy  snow or air pollution.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="balanced" class="constitution <?php echo $tcm_def[0] ?>">
	<dt>Guide for Balanced Constitution</dt>
	<dd>
	<p>A balanced constitution is a normal constitution. People with this constitution are fit, with fine lustrous skin and complexion , thick shiny hair, bright eyes and rosy lips. They are full of energy and do not tire easily. They sleep well and have a good appetite with normal bowel movements and urination. They are easy-going and extroverted, are seldom ill and possess the ability to easily adapt to their natural and social environment.</p>
	<ol>
	<li><b>Diet</b>
	<p>It is suggested to take moderate amounts of food, don't eat too much or too little. Avoid eating overly cold, hot, spicy or greasy foods. Maintain a rational diet, including fresh, high-quality grains, fruits and vegetables.</p></li>
	<li><b>Work and rest</b>
	<p>Keep a regular schedule with enough rest and sleep. Remember not to sleep right after meals.</p></li>
	<li><b>Exercise</b>
	<p>Proper exercise according to your age and physical capability is necessary. For example, young people could go running or play ball games, while the elderly could take a walk    or practice Taiji.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="stagnant_qi" class="constitution <?php echo $tcm_def[7] ?>">
	<dt>Guide for Stagnant Qi Constitution</dt>
	<dd>
	<p>Most people with this constitution are thin and often feel gloomy or depressed. They get nervous, anxious or frightened easily and tend to be overly sensitive. They often feel distending pain in the chest and hypochondria (area below the ribs). Sometimes they experience chest distress, sigh for no reason, have a sense of obstruction  in the throat   and suffer  from insomnia.</p>
	<ol>
	<li><b>Diet</b>
	<p>Foods that move qi to relieve depression are suggested. These include artichokes, cilantro, parsley, turnips, oats, anise, cardamom seeds, fennel, coriander leaves, hawthorn berries, plums, tangerine, orange, grapefruit, lime and lemon. Drinks such as lemon tea, chrysanthemum tea, peppermint tea, jasmine tea and citrus peel tea are recommended. Avoid overly sweet, sticky or greasy food, such as mashed potato, fatty meat, ice cream or other foods with a high fat content.</p></li>
	<li><b>Work and rest</b>
	<p>Increase the amount of outdoor activities such asjogging, mountain climbing and swimming. People of this constitution should live in a quiet place and get enough sleep to maintain a peaceful mind and attitude. Avoid black tea, coffee and chocolate before going to bed  since they may cause  insomnia.</p></li>
	<li><b>Exercise</b>
	<p>It is suggested for people of this type to regularly participate in team sports and in social activities such as playing ball games or chess, or going   dancing.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="phlegm_dampness" class="constitution <?php echo $tcm_def[4] ?>">
	<dt>Guide for Phlegm-dampness Constitution</dt>
	<dd>
	<p>People of this type are usually obese  especially in the abdominal area. They usually suffer from greasy sweat, aching pain and heaviness of the legs, a sticky and sweet taste in the mouth , and phlegm in the throat. Most have oily skin, a puffy tongue with a thick tongue coating. Generally, they have a gentle  nature.</p>
	<ol>
	<li><b>Diet</b>
	<p>It is suggested that people of this type eat a light diet of foods which can drain dampness, such as pumpkin or winter squash, kelp, onion, button mushrooms, turnips, hawthorn fruit, Job's tears, barley, rice or wheat bran, azuki/small red beans, garbanzo or lima beans, almonds, cardamom seed, lean beef and fish, and quail eggs. Drinks such as pu-er tea, black tea, oolong tea, lotus leaf tea and roasted corn tea are recommended.</p>
	<p>Avoid sweet, sticky and greasy food, including fatty meat, yogurt, beer, pizza, barbecue, cake and other sweets.</p></li>
	<li><b>Work and rest</b>
	<p>Avoid living in a damp area and increase the amount of outdoor activities while wearing loose clothing. Enjoy the sunshine with frequent participation in sunbathing -don't forget the sunscreen! Stay home during humid and cold days and try to avoid exposure to the cold and rain.</p></li>
	<li><b>Exercise</b>
	<p>It is suggested to exercise according to individual condition, practice mild exercise regularly, such as walking, jogging , playing table tennis, tennis, swimming,practicing martial arts and dancing.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="yin_def" class="constitution <?php echo $tcm_def[3] ?>">
	<dt>Guide for Yin-deficient Constitution</dt>
	<dd>
	<p>These people are usually thin and tall, often suffer from a feverish sensation in the cheeks, soles and palms. They tend to dislike the heat of summer and have dry eyes and skin. They are often thirsty and suffer from constipation and insomnia, and tend to have a red tongue with scanty tongue coating. They are often restless and extroverted .</p>
	<ol>
	<li><b>Diet</b>
	<p>Foods which can enrich yin are encouraged. These include fruits such as pear, apricot, peach, dark plum, pomegranate, and kiwi; chicken eggs, pork and duck; pine nuts, black beans, tofu, black sesame seeds, olives and honey. Drinks with sweet-cool property such as green tea, goji berry and chrysanthemum tea or lily bulb tea can nourish and moisten. Avoid foods which are warm and dry in property, such as mutton, hot pepper, coffee, chocolate and fried or grilled food.</p></li>
	<li><b>Work and rest</b>
	<p>It is suggested to lead a regular and peaceful life and avoid staying up late, vigorous exercise and physical  labor at high temperature in  summer.</p></li>
	<li><b>Exercise</b>
	<p>One suitable form of exercise for people of yin-deficient constitution is aerobics. Sports that combine dynamic and static activities such as Taiji and Qigong are also recommended. Avoid  profuse sweating, e.g. avoid hot saunas. Hydrate yourself   timely.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="damp_heat" class="constitution <?php echo $tcm_def[5] ?>">
	<dt>Guide for Damp-heat Constitution</dt>
	<dd>
	<p>People of damp-heat constitution may have oily skin, particularly on the face and tip of the nose, acne, itchy skin, bitter taste in the mouth, irritability, foul breath, sticky stools and slow bowel movements, dark yellow urine often with a burning sensation during urination , and yellow  leucorrhea or wet scrotum.</p>
	<ol>
	<li><b>Diet</b>
	<p>It is suggested to have a light diet of food which can clear heat and drain dampness, like celery, cucumber, lotus root, Job's tears, Chinese cabbage, mung bean , azuki beans, lima beans, rice bran, asparagus and fish. Drinks such as chrysanthemum tea, sage tea, dandelion tea and forsythia are recommended. Avoid hot and greasy food which can promote warmth, such as mutton, ginger, hot pepper, peppers, hot pot, fried or toasted food and chocolate.</p></li>
	<li><b>Work and rest</b>
	<p>It is suggested not to live in damp places, but dry and well venti lated environments. Reduce outdoor activities during hot, humid summer. Maintain sufficient and regular sleep. Don't stay up  late and avoid  overwork.</p></li>
	<li><b>Exercise</b>
	<p>People of this type need a workout with intense physical aerobics, such as medium or long-distance running, swimming, mountain climbing, and many kinds of ball games. During the high temperature and humidity of summer, it is better to exercise in the cool of the morning  or evening.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="stagnant_blood" class="constitution <?php echo $tcm_def[6] ?>">
	<dt>Guide for Stagnant Blood  Constitution</dt>
	<dd>
	<p>People of this constitution tend to have a dark facial complexion with purplish mouth and lips, rough skin, and bloodshot eyes. They bruise easily and may suffer from gum bleeding while brushing their teeth. They are often forgetful and impatient with a quick temper.</p>
	<ol>
	<li><b>Diet</b>
	<p>It is recommended to include foods that improve blood circulation , such as fish, onion, asparagus, bell peppers, lettuce, hawthorn fruit, black soy bean and rice or fruit vinegar. Drinks such as black tea, rose tea, apple tea, rosemary tea, and lime blossom tea are recommended. Avoid greasy foods, such as fatty meat, cream, cheese, ham, bacon and sausage, which  can restrict blood circulation.</p></li>
	<li><b>Work and rest</b>
	<p>Maintain a balance between work and rest and have sufficient amount of sleep. Go to bed and get up early. Exercise regularly.</p></li>
	<li><b>Exercise</b>
	<p>People of this type should do activities which can promote qi and blood circulation, such as dancing and walking. During exercise, if symptoms of chest distress, e.g. pain, shortness of breath or increased pulse rate appear, exercise should be stopped  immediately and the person should go to hospital for an   examination.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="qi_def" class="constitution <?php echo $tcm_def[1] ?>">
	<dt>Guide for Qi-deficient Constitution</dt>
	<dd>
	<p>People with a qi-deficient constitution are physically weak with loose muscles and a weak voice. They easily tire and sweat spontaneously. They may find that are more easily short of breath than their companions when climbing stairs. They are more vulnerable to the common cold due to lower resistance to    disease.</p>
	<ol>
	<li><b>Diet</b>
	<p>Foods such as reishi, ganoderma & ling zhi mushrooms; yams; apples; red & purple grapes; lima, garbanzo,navy & pinto beans; oats, barley, quinoa and rye; beef, chicken, duck and fish can boost qi. Drinks such as black tea, ginseng tea, licorice root tea, astragalus tea, and scorched rice tea are recommended. Avoid foods which can weaken qi such as Chinese cabbage, white radish ,hawthorn fruit, persimmon and spicy food.</p></li>
	<li><b>Work and rest</b>
	<p>Lead a regular life and have a noon nap, especially in the summer. It is advised to get enough sleep, keep warm and protected from the wind and cold, particularly after sweating from exercise or physical labor. Avoid overwork since it impairs original qi.</p></li>
	<li><b>Exercise</b>
	<p>Mild exercises such as walking, Taiji and yoga are strongly recommended for people. Practicing regular daily mild exercise can benefit health.Avoid intense exercise and holding the breath for a long time.</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="inderited_special" class="constitution <?php echo $tcm_def[8] ?>">
	<dt>Guide for Inherited Special Constitution</dt>
	<dd>
	<p>This constitution is a quite special. These people are vulnerable to many factors and environmental changes. For example, people with this constitution  tend to have allergies to drugs, foods, odors or pollen . They sneeze very often and have a runny, stuffy nose, and sometimes suffer  from asthma, hives, urticaria or skin  eruptions.</p>
	<ol>
	<li><b>Diet</b>
	<p>It is suggested to take a light balanced meal with balanced portion of vegetables and meat. Foods to strengthen immunity are recommended such as Chinese cabbage, grapefruit,wild ganoderma mushrooms, kumquats, ginseng and astragalus. Drinks such as green tea, chamomile tea (calm and slow), lemon balm tea and lemon grass tea (calm and slow, with Vitamin C to enhance immunity) are recommended. Avoid other mushrooms, buckwheat, fish, shrimp, crab, eggplant, alcohol, hot pepper, strong tea, coffee and agents that trigger allergies.</p></li>
	<li><b>Work and rest</b>
	<p>It is advised to keep the living areas clean and fresh, and to air the beddings in the sunshine frequently to prevent bed mites. Don't move in immediately after interior decorating but wait until the paint or formaldehyde dissipate. Avoid going out often during the spring when the pollen counts are high. Consider avoiding raising pets since their dander is a common allergen. Get plenty of sleep and maintain a regular   life.</p></li>
	<li><b>Exercise</b>
	<p>Take part in many kinds of exercise to build up your body. It is important to stay warm especially in cold and chilly days and during exercise to prevent catching cold.</p></li>
	</ol>
	</dd>
	</dl>
</div>
</div>