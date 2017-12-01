<?php
require_once( dirname(__FILE__) . "/../models/config.php");
$tcmanswers 	= isset($_REQUEST["tcm_answers"]) ? $_REQUEST["tcm_answers"] : NULL;
$tcmanswers 	= json_decode($tcmanswers,1);
$tcm_answers 	= array();
$uselang 		= isset($_REQUEST["uselang"])? $_REQUEST["uselang"] : "en";

foreach($tcmanswers as $userans){
	$tcm_answers[$userans["name"]] = $userans["value"];
}

$tcm_reqs = array();
$tcm_reqs[0] = array('tcm_energy','tcm_optimism','tcm_appetite','tcm_weight','tcm_stool','tcm_sleepwell','tcm_naturalenv');
$tcm_reqs[1] = array('tcm_tired','tcm_tranquility','tcm_voice','tcm_panting','tcm_colds','tcm_pasweat');
$tcm_reqs[2] = array('tcm_cold_tolerant','tcm_cold_tolerant_freezer','tcm_pain_eatingcold','tcm_sensitive_cold','tcm_wear_more','tcm_handsfeet_cold','tcm_cold_temp');
$tcm_reqs[3] = array('tcm_dryeyes','tcm_face_hot','tcm_handsfeet_hot','tcm_dryskin','tcm_drylips','tcm_constipated');
$tcm_reqs[4] = array('tcm_eyelid','tcm_oily_forehead','tcm_snore','tcm_bodyframe','tcm_limbs');
$tcm_reqs[5] = array('tcm_nose','tcm_hair_oily','tcm_bitter','tcm_acne','tcm_stickystool','tcm_scrotum','tcm_discharge');
$tcm_reqs[6] = array('tcm_complexion','tcm_forget','tcm_lips_color','tcm_bruises_skin','tcm_capillary_cheek','tcm_face_spot','tcm_tongue');
$tcm_reqs[7] = array('tcm_depressed','tcm_pessimistic','tcm_melancholy','tcm_scared','tcm_anxious','tcm_suspicious','tcm_chest_male','tcm_chest_female');
$tcm_reqs[8] = array('tcm_allergic_food','tcm_allergic_env','tcm_allergic_product','tcm_allergic_others','tcm_skin_red','tcm_allergic_exp');

$tcm_types 		= array(
		"en" => array(
			 "Balanced Constitution"
			,"Qi Deficiency Constitution"
			,"Yang Deficiency Constitution"
			,"Yin Deficiency Constitution"
			,"Phlegm-dampness Constitution"
			,"Damp-heat Constitution"
			,"Blood Stasis Constitution"
			,"Qi Stagnant Constitution"
			,"Inherited Special Constitution"
		)
		,"sp" => array(
			 "Balanced Constitution"
			,"Qi Deficiency Constitution"
			,"Yang Deficiency Constitution"
			,"Yin Deficiency Constitution"
			,"Phlegm-dampness Constitution"
			,"Damp-heat Constitution"
			,"Blood Stasis Constitution"
			,"Qi Stagnant Constitution"
			,"Inherited Special Constitution"
		)
		,"cn" => array(
			 "平和質(A 型)"
			,"氣虛質(B 型)"
			,"陽虛質(C 型)"
			,"陰虛質(D 型)"
			,"痰濕質(E 型)"
			,"濕熱質(F 型)"
			,"血瘀質(G 型)"
			,"氣鬱質(H 型)"
			,"特禀質(I 型)"
		)
		,"tw" => array(
			 "平和質(A 型)"
			,"氣虛質(B 型)"
			,"陽虛質(C 型)"
			,"陰虛質(D 型)"
			,"痰濕質(E 型)"
			,"濕熱質(F 型)"
			,"血瘀質(G 型)"
			,"氣鬱質(H 型)"
			,"特禀質(I 型)"
		)
);

$tcm_answers["tcm_gender"] = 4;

//WTF IS UP WITH THE GENDER THING 
if(isset($tcm_answers["tcm_scrotum"]) && isset($tcm_answers["tcm_ribcage"])){
	$tcm_answers["tcm_gender"] = 5;
}
if(isset($tcm_answers["tcm_discharge"]) && isset($tcm_answers["tcm_breastpain"])){
	$tcm_answers["tcm_gender"] = 4;
}
if( $tcm_answers["tcm_gender"] == 5 ){
	unset($tcm_reqs[5][6]);
	unset($tcm_reqs[7][7]);
}
if( $tcm_answers["tcm_gender"] == 4 ){
	unset($tcm_reqs[5][5]);
	unset($tcm_reqs[7][6]);
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
			return;
		}

		$val 	= $tcm_answers[$item];
		return $val;
	},$reqset);

	$tcm_map[$tcm_types[$uselang][$key]] = $temp;
}

$tcm_def = array();
$tcm_det = array();
foreach($tcm_map as $set => $qs){
	$tcm = getBodyConstitution($tcm_map, $set);
	$tcm_det[] = $tcm["determination"];
	$tcm_def[] = $tcm["determination"] < 1 ? "hidetcm" : ($tcm["determination"] > 1 ? "positive" : "tendency");
}


function getBodyConstitution($constitutions,$type){
	global $uselang;
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
				<tr><td >
					<?php echo lang("TCM_POSITIVE"); ?>
				</td></tr>
				<tr><td style="height:100px; vertical-align:bottom"><?php echo lang("TCM_ESSENTIALLY_POS"); ?></td></tr>
				<tr><td style="height:80px; vertical-align:bottom"><?php echo lang("TCM_NEGATIVE"); ?></td></tr>
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
		<td><span><?php echo implode("</span></td><td><span>",$tcm_types[$uselang]) ?></span></td>
	</tr>
</table>
<div class="constitutions lang en">
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
<div class="constitutions lang sp">
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
<div class="constitutions lang tw">
	<h3>這些是你身體體質分類，點擊每一個了解更多。</h3>
	<dl id="yang_def" class="constitution <?php echo $tcm_def[2] ?>">
	<dt>陽虛質(C 型)</dt>
	<dd>
	<p>總體特徵 ：陽氣不足，以畏寒怕冷、手溫等虛表現為主要特徵。形體特徵： 肌肉鬆軟不實。常見表現：平素畏冷，手足不溫，喜熱飲食，精神不振，舌淡胖嫩，脈沉遲。心理特徵：性格多沉靜、內向。發病傾向：易患痰飲、腫脹，泄瀉等；感邪易從寒化。對外界環境適應能力：耐夏不耐冬；易感風 、寒、濕邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>溫性的食物包括大蒜，洋蔥，韭菜，姜，栗子，核桃，奎奴亞藜，鵪鶉蛋，黑豆，牛肉，羊肉和羊肉。烘烤是有比較好的烹飪方法。食物應在室溫或加熱後食用。建議飲料如紅茶，生薑茶，肉桂茶，茴香茶，茴香茶和覆盆子茶。避免生的和冷的食物，如梨，西瓜，葡萄柚，生蔬菜，冷牛奶，冷啤酒，冰淇淋和綠茶。</p></li>
	<li><b>工作和休息</b>
	<p>對於陽虛質的人來說，重要的是保持溫暖，特別是腳，上背部和下腹部。特別重要的是注意季節性溫度變化。在冬季和秋季盡量保持室內通風良好;在夏天，重要的是避免停留在空調房間太長時間，因為它可以使人感染感冒。大量出汗會損害陽氣，需要避免。建議在晴天進行適當的戶外活動。</p></li>
	<li><b>鍛煉</b>
	<p>建議進行輕度體能運動，如慢跑，步行和太極。避免在夏季和任何不利的環境條件，如強風，苦寒，大霧，大雪或空氣污染下做劇烈運動。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="balanced" class="constitution <?php echo $tcm_def[0] ?>">
	<dt>平和質(A 型)</dt>
	<dd>
	<p>總體特徵：陰陽氣血調和，以體態適中、面色紅潤，精力充沛等為主。形體特徵：勻稱健壯。常見表現：面色、膚潤澤，頭髮稠密有光澤，目光有神，鼻色明潤，嗅覺通利，唇色紅潤，不易疲勞，精力充沛，耐受寒熱，睡眠良好，胃納佳，二便正常，舌色淡紅，苔薄白，脈和緩有力。心理特徵：性格隨和開朗。發病傾向：平素患病較少。對外界環境適應能力：對自然環境和社會環境適應較強。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議適量的飲食，不要吃太多或太少。避免食用過冷，熱，辛辣或油膩的食物。保持平衡的飲食，包括新鮮，優質的穀物，水果和蔬菜。</p></li>
	<li><b>工作和休息</b>
	<p>保持規律及足夠的休息和睡眠。記住不要在飯後睡覺。</p></li>
	<li><b>鍛煉</b>
	<p>根據你的年齡和身體能力進行適當的鍛煉是必要的。例如，年輕人可以跑步或玩球類游戲，而老人可以散步或練習太極。。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="stagnant_qi" class="constitution <?php echo $tcm_def[7] ?>">
	<dt>氣鬱質(H 型)</dt>
	<dd>
	<p>總體特徵：氣機鬱滯，以神情抑、憂慮脆弱等氣鬱表現為主要。形體特徵：瘦者為多。常見表現：神情抑鬱，感情脆弱 ，煩悶不樂，舌淡紅，苔薄白，脈弦。心理特徵：性格內向不穩定、敏感多慮。發病傾向：易患臟躁、梅核氣，百合病及鬱證等。對外界環境適應能力：對精神刺激適應能力較差；不適應陰雨天氣。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議促進氣和緩解抑鬱的食物如下：包括朝鮮薊，香菜，荷蘭芹，蕪菁，燕麥，茴香，荳蔻種子，茴香，芫荽葉，山楂漿果，李子，橘，橙，葡萄柚。推薦飲料如檸檬茶，菊花茶，薄荷茶，茉莉花茶和柑橘皮茶。避免過度甜，粘稠或油膩的食物，如土豆泥，肥肉，冰淇淋或其他高脂肪食物。</p></li>
	<li><b>工作和休息</b>
	<p>增加戶外活動的數量，如運動，爬山和游泳。氣鬱體質的人應該生活在一個安靜的地方，得到足夠的睡眠，保持和平的心態和態度。在睡覺前，避免紅茶，咖啡和巧克力，因為可能導致失眠。</p></li>
	<li><b>鍛煉</b>
	<p>建議氣鬱質類型的人定期參加團隊運動和社交活動，如打球類或像棋，或跳舞。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="phlegm_dampness" class="constitution <?php echo $tcm_def[4] ?>">
	<dt>痰濕質(E 型)</dt>
	<dd>
	<p>總體特徵：痰濕凝聚，以形肥胖、腹部肥滿，口黏苔膩等表現。形體特徵：肥胖，腹部滿鬆軟。常見表現：面部皮膚油脂較多， 多汗且黏，胸悶，痰多，口黏膩或甜，喜食肥甘甜黏，苔膩，脈滑。心理特徵：性格偏溫和、穩重，多善於忍耐。發病傾向：易患消渴、中風，胸痺等病。對外界環境適應能力：對梅雨季節及濕重環境適應能力差。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議可吃清淡的食物，如南瓜或冬季南瓜，海帶，洋蔥，蘑菇，蕪菁，山楂果，Job的眼淚，大麥，米或麥麩，azuki /小紅豆，鷹嘴豆或利馬豆，杏仁，荳蔻種子，瘦牛肉和魚和鵪鶉蛋。推薦飲料如紅茶，紅茶，烏龍茶，荷葉茶和烤玉米茶。</p>
	<p>避免甜，粘和油膩的食物，包括肥肉，酸奶，啤酒，比薩餅，燒烤，蛋糕和其他糖果。</p></li>
	<li><b>工作和休息</b>
	<p>避免生活在潮濕的地方，增加戶外活動的數量，穿寬鬆的衣服。享受陽光，經常參加日光浴 - 不要忘記防曬霜！在潮濕和寒冷的日子裡盡量呆在家裡，避免暴露在寒冷和陰雨中。</p></li>
	<li><b>鍛煉</b>
	<p>建議根據個人情況鍛煉，定期進行輕度運動，如散步，慢跑，打乒乓球，網球，游泳，練武術和跳舞。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="yin_def" class="constitution <?php echo $tcm_def[3] ?>">
	<dt>陰虛質(D 型)</dt>
	<dd>
	<p>總體特徵 ：陰液虧少，以口燥咽乾、手足心熱等虛表現為主要特徵。形體特徵：偏瘦。常見表現：手足心熱，口燥咽乾，鼻微幹，喜冷飲，大便乾燥，舌紅少津，脈細數。心理特徵：性情急躁，外向好動活潑。發病傾向：易患虛勞、失精、不寐等；感邪易從熱化。對外界環境適應能力：耐冬不耐夏；不耐受暑、熱、燥邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>鼓勵可以選擇涼性的食物。這些包括水果如梨，杏，桃，黑梅，石榴和獼猴桃;雞蛋，豬肉和鴨肉;松子，黑豆，豆腐，黑芝麻，橄欖和蜂蜜。涼屬性的飲料，如綠茶，枸杞漿果和菊花茶或百合燈泡茶可以滋養和滋潤。避免溫燥的食物，如羊肉，辣椒，咖啡，巧克力和油炸或烤的食物。</p></li>
	<li><b>工作和休息</b>
	<p>建議正常平靜的生活，避免熬夜，劇烈運動和在夏天高溫下體力勞動。</p></li>
	<li><b>鍛煉</b>
	<p>其中一種適合的運動形式是有氧運動。也建議結合動態和靜態活動如太極和氣功的體育。避免大量出汗。避免熱的桑拿。適時喝水。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="damp_heat" class="constitution <?php echo $tcm_def[5] ?>">
	<dt>濕熱質(F 型)</dt>
	<dd>
	<p>總體特徵：濕熱內蘊，以面垢油光、口苦，苔黃膩等表現為主要特徵。形體特徵：形體中等或偏瘦。常見表現：面垢油光，易生痤瘡，口苦口乾，身重困倦，大便黏滯不暢或燥結，小便短黃，男性易陰囊潮濕，女性易帶下增多，舌質偏紅，苔黃膩，脈滑數。心理特徵：容易煩急躁。發病傾向：易患瘡癤、黃疸、熱淋等病。對外界環境適應能力：夏末秋初濕熱氣候，濕重或溫偏高環境較難適應。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議使用能夠清除熱和排出濕氣的清淡食物，如芹菜，黃瓜，蓮藕，Job的眼淚，大白菜，綠豆，azuki豆，利馬豆，米糠，蘆筍和魚。推薦飲料如菊花茶，鼠尾草茶，蒲公英茶和連翹。避免熱的油脂食物及燥熱的食物，如羊肉，姜，辣椒，辣椒，火鍋，油炸或烤的食物和巧克力。</p></li>
	<li><b>工作和休息</b>
	<p>建議不要住在潮濕的地方，要住在乾燥和通風良好的環境。在炎熱，潮濕的夏天減少戶外活動。保持充足和規律的睡眠。不要熬夜，避免過度勞累。</p></li>
	<li><b>鍛煉</b>
	<p>建議選擇強烈的有氧運動的鍛煉，例如中型或長跑運動，游泳，登山和許多種類的球類運動。在夏天的高溫和潮濕的環境中，最好選擇在早上或晚上的涼爽時候鍛煉。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="stagnant_blood" class="constitution <?php echo $tcm_def[6] ?>">
	<dt>血瘀質(G 型)</dt>
	<dd>
	<p>總體特徵：血行不暢，以膚色晦黯、舌質紫等血瘀表現為主要特徵。形體特徵：胖瘦均見。常見表現：膚色晦黯，色素沉著，容易出瘀斑，口唇黯淡，舌黯或有瘀點，舌下絡脈紫黯或增粗，脈澀。心理特徵：易煩，健忘。發病傾向：易患癥瘕及痛證、血證等。對外界環境適應能力：不耐受寒邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議包括改善血液循環的食物，如魚，洋蔥，蘆筍，甜椒，萵苣，山楂果，黑色大豆和米或水果醋。推薦飲料如紅茶，玫瑰茶，蘋果茶，迷迭香茶和酸橙花茶。避免油脂食物，如肥肉，奶油，奶酪，火腿，培根和香腸，這些食物會限制血液循環。</p></li>
	<li><b>工作和休息</b>
	<p>保持工作和休息之間的平衡，並有足夠的睡眠。早睡早起。經常鍛煉。</p></li>
	<li><b>鍛煉</b>
	<p>可做促進氣和血液循環的活動，如跳舞和散步。在運動期間，如果胸部不適的症狀，例如，疼痛，呼吸短促或脈率增加，應立即停止運動，並去醫院接受檢查。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="qi_def" class="constitution <?php echo $tcm_def[1] ?>">
	<dt>氣虛質(B 型)</dt>
	<dd>
	<p>總體特徵：元氣不足，以疲乏、氣短，自汗​​等虛表現為主要。形體特徵：肌肉鬆軟不實。常見表現：平素語音低弱，氣短懶言，容易疲乏，精神不振，易出汗，淡紅。心理特徵：性格內向，不喜冒險。發病傾向：易患感冒、內臟下垂等；後康復緩慢。對外界環境適應能力：不耐受風、寒暑濕邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>增強氣的食物如靈芝，靈芝和靈芝;山藥蘋果;紅色和紫色葡萄;利馬，鷹嘴豆，海軍和斑豆;燕麥，大麥，藜麥和黑麥;牛肉，雞肉，鴨肉和魚類。推薦飲料如紅茶，人參茶，甘草茶，黃芪茶和炒飯茶。避免可能削弱氣的食物，如白菜，白蘿蔔，山楂果，柿子和辛辣食物。</p></li>
	<li><b>工作和休息</b>
	<p>有規律的生活，特別是在夏天，睡個午睡，。建議充足的睡眠，保持溫暖，防止風和寒冷，特別是從運動或體力勞動出汗後。避免過度勞累，因為它損傷元氣。</p></li>
	<li><b>鍛煉</b>
	<p>推薦溫和的鍛煉，如步行，太極和瑜伽。練習每日溫和運動可以有益於健康。避免劇烈運動和長時間的呼吸。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="inderited_special" class="constitution <?php echo $tcm_def[8] ?>">
	<dt>特禀質(I 型)</dt>
	<dd>
	<p>總體特徵：先天失常，以生理缺陷、過敏反應等為主要。形體特徵：過敏質者一般無殊；先天禀賦異常或有畸形，或生理缺陷。常見表現：過敏體質者哮喘、風團、咽癢、鼻塞、噴嚏等；患遺傳性疾病者有垂直遺傳、先天性、家族性特徵；患胎傳性疾病者俱有母體影響兒個體生長發育及相關疾病特徵。心理特徵：隨禀質不同情況各異。發病傾向：過敏體質者易患哮喘、蕁麻疹花粉症及藥物等；遺傳疾病如血友病、先天愚型等；胎五遲（立遲，行遲，發遲，齒遲和語遲）、五軟（頭軟，項軟，手足軟，肌肉軟，口軟）、解顱、胎驚、胎癇等。對外界環境適應能力：適應能力差，如過敏體質者對易致季節適應能力能力差 ，易引發宿疾。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議蔬菜和肉類的清淡均衡膳食。建議使用增強免疫力的食物，如大白菜，葡萄柚，野生靈芝蘑菇，金桔，人參和黃芪。飲料如綠茶，洋甘菊茶（平靜和緩慢），檸檬香蜂茶和檸檬草茶（平靜和緩慢，與維生素C增強免疫力）。避免其他蘑菇，蕎麥，魚，蝦，螃蟹，茄子，酒精，辣椒，濃茶，咖啡和其他會觸發過敏的食物。</p></li>
	<li><b>工作和休息</b>
	<p>建議保持住家清潔和通風，並經常在陽光下晾曬，以防止床蟎。不要立即搬進剛裝璜的屋內，要等到油漆或甲醛消失後。避免在春天當花粉數量高時經常出去。考慮避免養寵物，因為他們的皮屑是常見的過敏原。獲得充足的睡眠和保持規律的生活。</p></li>
	<li><b>鍛煉</b>
	<p>參加多種運動來強健你的身體。重要的是保持溫暖，特別是在寒冷的日子和運動期間要防止感冒。</p></li>
	</ol>
	</dd>
	</dl>
</div>
<div class="constitutions lang cn">
	<h3>這些是你身體體質分類，點擊每一個了解更多。</h3>
	<dl id="yang_def" class="constitution <?php echo $tcm_def[2] ?>">
	<dt>陽虛質(C 型)</dt>
	<dd>
	<p>總體特徵 ：陽氣不足，以畏寒怕冷、手溫等虛表現為主要特徵。形體特徵： 肌肉鬆軟不實。常見表現：平素畏冷，手足不溫，喜熱飲食，精神不振，舌淡胖嫩，脈沉遲。心理特徵：性格多沉靜、內向。發病傾向：易患痰飲、腫脹，泄瀉等；感邪易從寒化。對外界環境適應能力：耐夏不耐冬；易感風 、寒、濕邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>溫性的食物包括大蒜，洋蔥，韭菜，姜，栗子，核桃，奎奴亞藜，鵪鶉蛋，黑豆，牛肉，羊肉和羊肉。烘烤是有比較好的烹飪方法。食物應在室溫或加熱後食用。建議飲料如紅茶，生薑茶，肉桂茶，茴香茶，茴香茶和覆盆子茶。避免生的和冷的食物，如梨，西瓜，葡萄柚，生蔬菜，冷牛奶，冷啤酒，冰淇淋和綠茶。</p></li>
	<li><b>工作和休息</b>
	<p>對於陽虛質的人來說，重要的是保持溫暖，特別是腳，上背部和下腹部。特別重要的是注意季節性溫度變化。在冬季和秋季盡量保持室內通風良好;在夏天，重要的是避免停留在空調房間太長時間，因為它可以使人感染感冒。大量出汗會損害陽氣，需要避免。建議在晴天進行適當的戶外活動。</p></li>
	<li><b>鍛煉</b>
	<p>建議進行輕度體能運動，如慢跑，步行和太極。避免在夏季和任何不利的環境條件，如強風，苦寒，大霧，大雪或空氣污染下做劇烈運動。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="balanced" class="constitution <?php echo $tcm_def[0] ?>">
	<dt>平和質(A 型)</dt>
	<dd>
	<p>總體特徵：陰陽氣血調和，以體態適中、面色紅潤，精力充沛等為主。形體特徵：勻稱健壯。常見表現：面色、膚潤澤，頭髮稠密有光澤，目光有神，鼻色明潤，嗅覺通利，唇色紅潤，不易疲勞，精力充沛，耐受寒熱，睡眠良好，胃納佳，二便正常，舌色淡紅，苔薄白，脈和緩有力。心理特徵：性格隨和開朗。發病傾向：平素患病較少。對外界環境適應能力：對自然環境和社會環境適應較強。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議適量的飲食，不要吃太多或太少。避免食用過冷，熱，辛辣或油膩的食物。保持平衡的飲食，包括新鮮，優質的穀物，水果和蔬菜。</p></li>
	<li><b>工作和休息</b>
	<p>保持規律及足夠的休息和睡眠。記住不要在飯後睡覺。</p></li>
	<li><b>鍛煉</b>
	<p>根據你的年齡和身體能力進行適當的鍛煉是必要的。例如，年輕人可以跑步或玩球類游戲，而老人可以散步或練習太極。。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="stagnant_qi" class="constitution <?php echo $tcm_def[7] ?>">
	<dt>氣鬱質(H 型)</dt>
	<dd>
	<p>總體特徵：氣機鬱滯，以神情抑、憂慮脆弱等氣鬱表現為主要。形體特徵：瘦者為多。常見表現：神情抑鬱，感情脆弱 ，煩悶不樂，舌淡紅，苔薄白，脈弦。心理特徵：性格內向不穩定、敏感多慮。發病傾向：易患臟躁、梅核氣，百合病及鬱證等。對外界環境適應能力：對精神刺激適應能力較差；不適應陰雨天氣。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議促進氣和緩解抑鬱的食物如下：包括朝鮮薊，香菜，荷蘭芹，蕪菁，燕麥，茴香，荳蔻種子，茴香，芫荽葉，山楂漿果，李子，橘，橙，葡萄柚。推薦飲料如檸檬茶，菊花茶，薄荷茶，茉莉花茶和柑橘皮茶。避免過度甜，粘稠或油膩的食物，如土豆泥，肥肉，冰淇淋或其他高脂肪食物。</p></li>
	<li><b>工作和休息</b>
	<p>增加戶外活動的數量，如運動，爬山和游泳。氣鬱體質的人應該生活在一個安靜的地方，得到足夠的睡眠，保持和平的心態和態度。在睡覺前，避免紅茶，咖啡和巧克力，因為可能導致失眠。</p></li>
	<li><b>鍛煉</b>
	<p>建議氣鬱質類型的人定期參加團隊運動和社交活動，如打球類或像棋，或跳舞。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="phlegm_dampness" class="constitution <?php echo $tcm_def[4] ?>">
	<dt>痰濕質(E 型)</dt>
	<dd>
	<p>總體特徵：痰濕凝聚，以形肥胖、腹部肥滿，口黏苔膩等表現。形體特徵：肥胖，腹部滿鬆軟。常見表現：面部皮膚油脂較多， 多汗且黏，胸悶，痰多，口黏膩或甜，喜食肥甘甜黏，苔膩，脈滑。心理特徵：性格偏溫和、穩重，多善於忍耐。發病傾向：易患消渴、中風，胸痺等病。對外界環境適應能力：對梅雨季節及濕重環境適應能力差。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議可吃清淡的食物，如南瓜或冬季南瓜，海帶，洋蔥，蘑菇，蕪菁，山楂果，Job的眼淚，大麥，米或麥麩，azuki /小紅豆，鷹嘴豆或利馬豆，杏仁，荳蔻種子，瘦牛肉和魚和鵪鶉蛋。推薦飲料如紅茶，紅茶，烏龍茶，荷葉茶和烤玉米茶。</p>
	<p>避免甜，粘和油膩的食物，包括肥肉，酸奶，啤酒，比薩餅，燒烤，蛋糕和其他糖果。</p></li>
	<li><b>工作和休息</b>
	<p>避免生活在潮濕的地方，增加戶外活動的數量，穿寬鬆的衣服。享受陽光，經常參加日光浴 - 不要忘記防曬霜！在潮濕和寒冷的日子裡盡量呆在家裡，避免暴露在寒冷和陰雨中。</p></li>
	<li><b>鍛煉</b>
	<p>建議根據個人情況鍛煉，定期進行輕度運動，如散步，慢跑，打乒乓球，網球，游泳，練武術和跳舞。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="yin_def" class="constitution <?php echo $tcm_def[3] ?>">
	<dt>陰虛質(D 型)</dt>
	<dd>
	<p>總體特徵 ：陰液虧少，以口燥咽乾、手足心熱等虛表現為主要特徵。形體特徵：偏瘦。常見表現：手足心熱，口燥咽乾，鼻微幹，喜冷飲，大便乾燥，舌紅少津，脈細數。心理特徵：性情急躁，外向好動活潑。發病傾向：易患虛勞、失精、不寐等；感邪易從熱化。對外界環境適應能力：耐冬不耐夏；不耐受暑、熱、燥邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>鼓勵可以選擇涼性的食物。這些包括水果如梨，杏，桃，黑梅，石榴和獼猴桃;雞蛋，豬肉和鴨肉;松子，黑豆，豆腐，黑芝麻，橄欖和蜂蜜。涼屬性的飲料，如綠茶，枸杞漿果和菊花茶或百合燈泡茶可以滋養和滋潤。避免溫燥的食物，如羊肉，辣椒，咖啡，巧克力和油炸或烤的食物。</p></li>
	<li><b>工作和休息</b>
	<p>建議正常平靜的生活，避免熬夜，劇烈運動和在夏天高溫下體力勞動。</p></li>
	<li><b>鍛煉</b>
	<p>其中一種適合的運動形式是有氧運動。也建議結合動態和靜態活動如太極和氣功的體育。避免大量出汗。避免熱的桑拿。適時喝水。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="damp_heat" class="constitution <?php echo $tcm_def[5] ?>">
	<dt>濕熱質(F 型)</dt>
	<dd>
	<p>總體特徵：濕熱內蘊，以面垢油光、口苦，苔黃膩等表現為主要特徵。形體特徵：形體中等或偏瘦。常見表現：面垢油光，易生痤瘡，口苦口乾，身重困倦，大便黏滯不暢或燥結，小便短黃，男性易陰囊潮濕，女性易帶下增多，舌質偏紅，苔黃膩，脈滑數。心理特徵：容易煩急躁。發病傾向：易患瘡癤、黃疸、熱淋等病。對外界環境適應能力：夏末秋初濕熱氣候，濕重或溫偏高環境較難適應。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議使用能夠清除熱和排出濕氣的清淡食物，如芹菜，黃瓜，蓮藕，Job的眼淚，大白菜，綠豆，azuki豆，利馬豆，米糠，蘆筍和魚。推薦飲料如菊花茶，鼠尾草茶，蒲公英茶和連翹。避免熱的油脂食物及燥熱的食物，如羊肉，姜，辣椒，辣椒，火鍋，油炸或烤的食物和巧克力。</p></li>
	<li><b>工作和休息</b>
	<p>建議不要住在潮濕的地方，要住在乾燥和通風良好的環境。在炎熱，潮濕的夏天減少戶外活動。保持充足和規律的睡眠。不要熬夜，避免過度勞累。</p></li>
	<li><b>鍛煉</b>
	<p>建議選擇強烈的有氧運動的鍛煉，例如中型或長跑運動，游泳，登山和許多種類的球類運動。在夏天的高溫和潮濕的環境中，最好選擇在早上或晚上的涼爽時候鍛煉。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="stagnant_blood" class="constitution <?php echo $tcm_def[6] ?>">
	<dt>血瘀質(G 型)</dt>
	<dd>
	<p>總體特徵：血行不暢，以膚色晦黯、舌質紫等血瘀表現為主要特徵。形體特徵：胖瘦均見。常見表現：膚色晦黯，色素沉著，容易出瘀斑，口唇黯淡，舌黯或有瘀點，舌下絡脈紫黯或增粗，脈澀。心理特徵：易煩，健忘。發病傾向：易患癥瘕及痛證、血證等。對外界環境適應能力：不耐受寒邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議包括改善血液循環的食物，如魚，洋蔥，蘆筍，甜椒，萵苣，山楂果，黑色大豆和米或水果醋。推薦飲料如紅茶，玫瑰茶，蘋果茶，迷迭香茶和酸橙花茶。避免油脂食物，如肥肉，奶油，奶酪，火腿，培根和香腸，這些食物會限制血液循環。</p></li>
	<li><b>工作和休息</b>
	<p>保持工作和休息之間的平衡，並有足夠的睡眠。早睡早起。經常鍛煉。</p></li>
	<li><b>鍛煉</b>
	<p>可做促進氣和血液循環的活動，如跳舞和散步。在運動期間，如果胸部不適的症狀，例如，疼痛，呼吸短促或脈率增加，應立即停止運動，並去醫院接受檢查。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="qi_def" class="constitution <?php echo $tcm_def[1] ?>">
	<dt>氣虛質(B 型)</dt>
	<dd>
	<p>總體特徵：元氣不足，以疲乏、氣短，自汗​​等虛表現為主要。形體特徵：肌肉鬆軟不實。常見表現：平素語音低弱，氣短懶言，容易疲乏，精神不振，易出汗，淡紅。心理特徵：性格內向，不喜冒險。發病傾向：易患感冒、內臟下垂等；後康復緩慢。對外界環境適應能力：不耐受風、寒暑濕邪。</p>
	<ol>
	<li><b>飲食</b>
	<p>增強氣的食物如靈芝，靈芝和靈芝;山藥蘋果;紅色和紫色葡萄;利馬，鷹嘴豆，海軍和斑豆;燕麥，大麥，藜麥和黑麥;牛肉，雞肉，鴨肉和魚類。推薦飲料如紅茶，人參茶，甘草茶，黃芪茶和炒飯茶。避免可能削弱氣的食物，如白菜，白蘿蔔，山楂果，柿子和辛辣食物。</p></li>
	<li><b>工作和休息</b>
	<p>有規律的生活，特別是在夏天，睡個午睡，。建議充足的睡眠，保持溫暖，防止風和寒冷，特別是從運動或體力勞動出汗後。避免過度勞累，因為它損傷元氣。</p></li>
	<li><b>鍛煉</b>
	<p>推薦溫和的鍛煉，如步行，太極和瑜伽。練習每日溫和運動可以有益於健康。避免劇烈運動和長時間的呼吸。</p></li>
	</ol>
	</dd>
	</dl>

	<dl id="inderited_special" class="constitution <?php echo $tcm_def[8] ?>">
	<dt>特禀質(I 型)</dt>
	<dd>
	<p>總體特徵：先天失常，以生理缺陷、過敏反應等為主要。形體特徵：過敏質者一般無殊；先天禀賦異常或有畸形，或生理缺陷。常見表現：過敏體質者哮喘、風團、咽癢、鼻塞、噴嚏等；患遺傳性疾病者有垂直遺傳、先天性、家族性特徵；患胎傳性疾病者俱有母體影響兒個體生長發育及相關疾病特徵。心理特徵：隨禀質不同情況各異。發病傾向：過敏體質者易患哮喘、蕁麻疹花粉症及藥物等；遺傳疾病如血友病、先天愚型等；胎五遲（立遲，行遲，發遲，齒遲和語遲）、五軟（頭軟，項軟，手足軟，肌肉軟，口軟）、解顱、胎驚、胎癇等。對外界環境適應能力：適應能力差，如過敏體質者對易致季節適應能力能力差 ，易引發宿疾。</p>
	<ol>
	<li><b>飲食</b>
	<p>建議蔬菜和肉類的清淡均衡膳食。建議使用增強免疫力的食物，如大白菜，葡萄柚，野生靈芝蘑菇，金桔，人參和黃芪。飲料如綠茶，洋甘菊茶（平靜和緩慢），檸檬香蜂茶和檸檬草茶（平靜和緩慢，與維生素C增強免疫力）。避免其他蘑菇，蕎麥，魚，蝦，螃蟹，茄子，酒精，辣椒，濃茶，咖啡和其他會觸發過敏的食物。</p></li>
	<li><b>工作和休息</b>
	<p>建議保持住家清潔和通風，並經常在陽光下晾曬，以防止床蟎。不要立即搬進剛裝璜的屋內，要等到油漆或甲醛消失後。避免在春天當花粉數量高時經常出去。考慮避免養寵物，因為他們的皮屑是常見的過敏原。獲得充足的睡眠和保持規律的生活。</p></li>
	<li><b>鍛煉</b>
	<p>參加多種運動來強健你的身體。重要的是保持溫暖，特別是在寒冷的日子和運動期間要防止感冒。</p></li>
	</ol>
	</dd>
	</dl>
</div>
</div>
<style>
div.constitutions.lang{
	display:none;
}
div.constitutions.<?php echo $uselang ?> {
	display:block !important;
}
</style>