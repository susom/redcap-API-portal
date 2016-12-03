
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
		if($metscore < 8.9){
			$level = 0;
		}else if($metscore <= 14.9){
			$level = 1;
		}else{
			$level = 2;
		}
	}else if($age <= 59){
		if($metscore < 7.9){
			$level = 0;
		}else if($metscore <= 13.9){
			$level = 1;
		}else{
			$level = 2;
		}
	}else{
		if($metscore < 6.9){
			$level = 0;
		}else if($metscore <= 12.9){
			$level = 1;
		}else{
			$level = 2;
		}
	}
}else{
	if($age <= 39 ){
		if($metscore < 6.9){
			$level = 0;
		}else if($metscore <= 12.9){
			$level = 1;
		}else{
			$level = 2;
		}
	}else if($age <= 59){
		if($metscore < 5.9){
			$level = 0;
		}else if($metscore <= 11.9){
			$level = 1;
		}else{
			$level = 2;
		}
	}else{
		if($metscore < 4.9){
			$level = 0;
		}else if($metscore <= 10.9){
			$level = 1;
		}else{
			$level = 2;
		}
	}
}

$suggest = $suggestion[$level];
?>
<div id="met_results">
	<div id="met_score"></div>
	<h3 class="lang en">Thank you for your participation! Find out about some of the factors that can impact your cardiorespiratory fitness below:</h3>
	<h3 class="lang sp">¡Gracias por su participación! Descubra algunos factores que pueden impactar su salud cardiovascular aquí:</h3>
	<div id="met_aging" class="met_desc">
		<aside class="lang en">
			<h3>Aging</h3>
			<div class="funfact"><p><i>Did you know that the American Psychological Association has found that wisdom and creativity are traits that often continue into our later years?</i></p></div>
			<div class="old">
				<p>Our cardiorespiratory fitness naturally declines as we age. Over the next several years, your body will begin to experience changes that may begin to limit your physical abilities. While we cannot prevent aging, we can help to prepare for aging and combat some of its common negative effects by <a class="offsite" href='https://www.hsph.harvard.edu/nutritionsource/healthy-eating-plate/'>eating healthy</a> and <a class="offsite" href='https://go4life.nia.nih.gov/'>staying active</a>.</p>
				<p>It’s important to distinguish between normal aging and abnormal declines in your health. Consider checking out <a class="offsite" href="http://www.apa.org/pi/aging/resources/guides/older.aspx">this guide</a> prepared by the American Psychological Association to learn more about some of the truths and common myths associated with aging.</p>
				<p>Make sure physical activity is right for you; before starting, take this <a target="blank" href='pa_readiness_questionaire.pdf'>self-evaluation questionnaire</a> or talk to a healthcare provider.</p>
			</div>
			<div class="really_old">
				<p>Our cardiorespiratory fitness naturally declines as we age. While we cannot prevent aging, we can help to combat some of the negative effects we tend to see with aging by continuing to eat <a class="offsite" href='https://www.hsph.harvard.edu/nutritionsource/healthy-eating-plate/'>healthy</a> and by <a class="offsite" href='https://go4life.nia.nih.gov/'>staying active</a>. For more information about healthy aging, visit <a class="offsite" href='http://www.hhs.gov/aging/healthy-aging/'>http://www.hhs.gov/aging/healthy-aging/</a>. Taking steps now to help to preserve your cardiorespiratory fitness can help to reduce your risk of heart disease.</p>
				<p>Make sure physical activity is right for you; before starting, take this <a target="blank" href='pa_readiness_questionaire.pdf'>self-evaluation questionnaire</a> or talk to a healthcare provider.</p>
			</div>
		</aside>
		<aside class="lang sp">
			<h3>Envejecimiento</h3>
			<div class="funfact"><p><i>Sabía usted que la Asociación Psicológica Americana ha encontrado que la sabiduría y la creatividad son atributos que continúan hasta tarde en la vida?</i></p></div>
			<div class="old">
				<p>Nuestra salud cardio-respiratoria disminuye naturalmente mientras envejecemos. A través de los próximos años, su cuerpo comenzará a sentir cambios que podrían  limitar sus habilidades físicas. Mientras no podemos prevenir el envejecimiento, podemos ayudarle a prepararse a envejecer y ayudarle a combatir algunos de los efectos negativos al comer saludable y mantenerse activo.</p>
				<p>Es importante distinguir entre el envejecimiento normal y declinaciones anormales en  para aprender más sobre las verdades y los mitos comunes asociados con el envejecimiento.</p>
				<p>Asegúrese que la actividad física es apropiada para usted, antes de comenzar, tome este cuestionario para evaluarse a sí mismo o consulte con un profesional de salud.</p>
			</div>
			<div class="really_old">
				<p>Nuestra salud cardio-respiratoria disminuye naturalmente mientras envejecemos.  Mientras no podemos prevenir el envejecimiento, podemos ayudar a combatir algunos de los efectos negativos que vemos durante envejecimiento comiendo saludablemente y manteniéndonos activos. Para más información sobre envejecimiento saludable y activo, visite: <a class="offsite" href='http://www.consumer.es/web/es/solidaridad/proyectos_y_campanas/2012/01/24/206397.php'>http://www.consumer.es</a>.</p>
				<p>Tomando pasos ahora para preservar su salud cardio-respiratoria puede ayudar a reducir el riesgo de enfermedad del corazón.</p>
				<p>Asegúrese que la actividad física es apropiada para usted, antes de comenzar, tome este cuestionario para evaluarse a sí mismo o consulte con un profesional de salud.</p>
			</div>
		</aside>
	</div>
	<div id="met_bmi" class="met_desc">
		<aside class="lang en">
			<h3>Body Mass Index (BMI <a href="#" class="moreinfo" title="What is BMI?" data-content="what_is_bmi">?</a>)</h3>
			<div class="moreinfo" id="what_is_bmi">
				<a href="#" class="closeparent">X</a>
				<h3>Learn about BMI?</h3>
				<p>Body Mass Index is calculated using your height and weight, and is commonly used by healthcare providers to screen for adults who may weigh more or less than the recommended weight for their height. BMI may not be as accurate if you are an athlete or very muscled (muscle weighs more than fat), and a fat percentage examination may be needed to more accurately assess if you are at a healthy weight. It is also not accurate for pregnant or breastfeeding women or people who are frail.</p>

				<p>BMI is only one of many measures of your overall health. Waist measurement, body fat level, blood pressure, cholesterol, physical activity, not smoking, diet, and other measures are also important.</p>
			</div>
			<div class="funfact"><p><i>Did you know many fruit juices have just as many calories as a soda? Water, coffee, and tea (without sugar added) are great low-calorie alternatives.</i></p></div>
			<div class="bmi_b">
				<p>Using your height of <span class='your_height'></span> and weight of <span class='your_weight'></span>, we calculated your BMI to be <span class='your_bmi'></span>, placing you in the UNDERWEIGHT BMI category. A BMI of 18.5-24.9 is considered healthy. For your height, that would be a weight of <span class='healthy_weight_min'></span> to <span class='healthy_weight_max'></span> pounds.</p>
				<p>Being underweight can result in many health issues, including hair loss, weakening of the immune system, loss of bone density, and reduced fertility. Working to gain weight by eating nutritious, high-calorie foods like avocado, cheese, and nuts can help you to safely reach a healthy BMI. Check out <a class="offsite" href='http://www.nhs.uk/Livewell/Goodfood/Pages/Underweightadults.aspx'>this page</a> prepared for underweight adults by the UK’s National Health Service to learn more.</p>
			</div>
			<div class="bmi_c">
				<p>Using your height of <span class='your_height'></span>, and weight of <span class='your_weight'></span>, we calculated your BMI to be <span class='your_bmi'></span>, placing you in the NORMAL BMI category. A BMI of 18.5-24.9 is considered healthy.</p>
				<p>Good job! Maintaining a healthy weight is an important part of keeping you healthy and maintaining your wellbeing. Continue to maintain your weight through a <a class="offsite" href='https://www.hsph.harvard.edu/nutritionsource/healthy-eating-plate/'>healthy diet</a> and by <a class="offsite" href='http://health.gov/paguidelines/'>remaining active</a>.</p>
			</div>
			<div class="bmi_d">
				<p>Using your height of <span class='your_height'></span>, and weight of <span class='your_weight'></span>, we calculated your BMI to be <span class='your_bmi'></span>, placing you in the OVERWEIGHT BMI category. A BMI of 18.5-24.9 is considered healthy. For your height, that would be a weight of <span class='healthy_weight_min'></span> to <span class='healthy_weight_max'></span> pounds.</p>
				<p>For most adults trying to lose weight, the CDC recommends a weight decrease of 1-2 pounds per week. Losing at least <span class='lose_weight'></span> pounds would move your score into the healthy BMI category.</p> 
				<p>Being overweight has been linked to an increased risk for many health issues, including cardiovascular disease, Type 2 diabetes, heart attack, and stroke. Working to lower your BMI by losing weight through lifestyle changes can help to reduce your risk for these complications, and help you live a healthier life.</p>
				<p>There are many options to help you lose weight and return to a healthy BMI. For more information, including a BMI calculator and tools to help you get started, you can visit <a class="offsite" href='http://www.cdc.gov/healthyweight/index.html'>http://www.cdc.gov/healthyweight/index.html</a> to learn more.</p>
			</div>
			<div class="bmi_e">
				<p>Using your height of <span class='your_height'></span>, and weight of <span class='your_weight'></span>, we calculated your BMI to be <span class='your_bmi'></span>, placing you in the OBESE BMI category. A BMI of 18.5-24.9 is considered healthy. For your height, that would be a weight of <span class='healthy_weight_min'></span> to <span class='healthy_weight_max'></span> pounds.</p>
				<p>For most adults, the CDC recommends a weight decrease of 1-2 pounds per week. Losing at least <span class='lose_weight'></span> pounds would move your score into the healthy BMI category.</p>
				<p>Obesity has been linked to an increased risk for many health issues, including cardiovascular disease, Type 2 diabetes, heart attack, and stroke. Working to lower your BMI by losing weight through lifestyle changes like eating healthy and by remaining active can help to reduce your risk for these complications, and help you live a healthier life.</p>
				<p>There are many options to help you lose weight and return to a healthy BMI. For more information, including a BMI calculator and tools to help you get started, you can visit <a class="offsite" href='http://www.cdc.gov/healthyweight/index.html'>http://www.cdc.gov/healthyweight/index.html</a> to learn more.</p>
			</div>
		</aside>
		<aside class="lang sp">
			<h3>Índice de masa corporal (BMI <a href="#" class="moreinfo" title="What is BMI?" data-content="what_is_bmi">?</a>)</h3>
			<div class="moreinfo" id="what_is_bmi">
				<a href="#" class="closeparent">X</a>
				<h3>Aprenda más sobre el IMC?</h3>
				<p>El Índice de Masa Corporal (IMC) se calcula usando su altura y su peso, y es comúnmente utilizado por proveedores de salud médica para identificar adultos que pueden pesar más o menos que el peso recomendado para su estatura. Puede ser que el IMC no sea una herramienta precisa para su salud física si usted es un atleta o muy musculoso (el músculo pesa más que la grasa). Un examen de porcentaje de grasa puede ser necesario para evaluar con más precisión si tiene un peso saludable. El IMC tampoco es preciso para mujeres embarazadas o lactantes o personas frágiles.  El IMC es sólo una de muchas maneras de medir su salud general. La medida de la cintura, el nivel de grasa corporal, la presión arterial, el colesterol, la actividad física, el no fumar, la dieta y otras medidas también son importantes.</p>
			</div>
			<div class="funfact"><p><i>Sabía usted que no todas las grasas son malas? Comidas como los nueces, aguacate, aceites saludables y mantequilla de maní contienen “grasas buenas” que tiene muchos beneficios para la salud.</i></p></div>
			<div class="bmi_b">
				<p>Utilizando su altura de <span class='your_height'></span> y su peso de <span class='your_weight'></span>, calculamos que su índice de masa corporal <span class='your_bmi'></span>, colocándolo(a) en la categoría de BMI BAJO PESO. Un BMI de 18.5-24.9 es considerado como saludable. Para su altura, eso sería un peso de <span class='healthy_weight_min'></span> a <span class='healthy_weight_max'></span> libras.</p>
				<p>Estando bajo peso puede resultar en muchos problemas de salud, incluyendo pérdida de cabello, debilitación de su sistema inmunológico, pérdida de masa de hueso, y fertilidad reducida. Aumentar peso consumiendo comidas nutritivas de calorías altas como aguacate, queso, huevos y nueces pueden ayudarle a obtener un BMI saludable. Visite esta página preparada por la Clínica Mayo para adultos bajo en peso. Aquí encontrará sugerencias sobre cómo empezar <a class="offsite" href='http://www.cdc.gov/healthyweight/spanish/healthyeating/index.html'>http://www.cdc.gov/healthyweight/spanish/healthyeating</a>. Además considere hablar con su proveedor de salud sobre su peso.</p>
			</div>
			<div class="bmi_c">
				<p>Utilizando su altura de <span class='your_height'></span> y su peso de <span class='your_weight'></span>, calculamos que su índice de masa corporal <span class='your_bmi'></span>, colocándolo(a) en la categoría de BMI NORMAL. Un BMI de 18.5-24.9 es considerado como saludable.</p>
				<p>Buen trabajo! Manteniendo un peso saludable es una parte importante de mantener su salud y mantener su bienestar. Continúe manteniendo su altura con una dieta saludable y manteniéndose activo: <a class="offsite" href='http://www.cdc.gov/healthyweight/spanish/healthyeating/index.html'>Comiendo saludablemente</a> and by <a class="offsite" href='http://www.vitonica.com/wellness/nuevas-recomendaciones-de-actividad-fisica-para-todas-las-edades'>Actividad física y ejercicios</a>.</p>
			</div>
			<div class="bmi_d">
				<p>Utilizando su altura de <span class='your_height'></span> y su peso de <span class='your_weight'></span>, calculamos que su índice de masa corporal <span class='your_bmi'></span>, colocándolo(a) en la categoría de BMI SOBRE PRESO. Un BMI de 18.5-24.9 es considerado como saludable. Para su altura, eso sería un peso de <span class='healthy_weight_min'></span> a <span class='healthy_weight_max'></span> libras.</p>
				<p>Estar sobrepeso está conectado a un aumento en riesgos a salud, incluyendo enfermedades cardiovasculares, diabetes tipo 2, ataque de corazón, y derrame cerebral. Trabajando para reducir su BMI a través de cambios a sus hábitos de estilo de vida, tal como comiendo saludablemente y manteniéndose activo(a) pueden ayudar a reducir su riesgo a tener tales complicaciones, y le ayudará a vivar una vida mas saludable.</p> 
				<p>Existen muchas opciones para ayudarle a reducir peso y regresar a un BMI saludable. Para más información, incluyendo un calculador de BMI y herramientas para ayudarle a comenzar, puede visitar a <a class="offsite" href='https://www.cdc.gov/healthyweight/spanish/assessing/bmi/index.html'>https://www.cdc.gov/healthyweight/spanish/assessing/bmi/index.html</a> para aprender más.</p>
			</div>
			<div class="bmi_e">
				<p>Utilizando su altura de <span class='your_height'></span> y su peso de <span class='your_weight'></span>, calculamos que su índice de masa corporal <span class='your_bmi'></span>, colocándolo(a) en la categoría de BMI OBESO. Un BMI de 18.5-24.9 es considerado como saludable. Para su altura, eso sería un peso de <span class='healthy_weight_min'></span> a <span class='healthy_weight_max'></span> libras.</p>
				<p>Para la mayoría de adultos tratando de perder peso, la CDC recomienda bajar 1-2 libras a la semana. Perdiendo al menos <span class='lose_weight'></span> libras convertirán su índice de masa corporal a una categoría saludable.</p>
				<p>La obesidad está conectada a un aumento en riesgos a salud, incluyendo enfermedades cardiovasculares, diabetes tipo 2, ataque de corazón, y derrame cerebral. Trabajando para reducir su BMI a través de cambios a sus hábitos de estilo de vida, tal como comiendo saludablemente y manteniéndose activo(a) pueden ayudar a reducir las probabilidades de tener tales complicaciones, y le ayudará a vivar una vida mas saludable.</p>
				<p>Existen muchas opciones para ayudarle a reducir peso y regresar a un BMI saludable. Para más información, incluyendo un calculador de BMI y herramientas para ayudarle a comenzar, puede visitar a <a class="offsite" href='https://www.cdc.gov/healthyweight/spanish/assessing/bmi/index.html'>https://www.cdc.gov/healthyweight/spanish/assessing/bmi/index.html</a> para aprender más.</p>
			</div>
		</aside>
	</div>
	<div id="met_pa" class="met_desc">
		<aside class="lang en">
			<h3>Physical Activity</h3>
			<div class="funfact"><p><i>Did you know that being physically active can help to improve your mental health and mood?</i></p></div>
			<div class="pa_1">
				<p>Physical activity is one of the many ways you can help to improve both your wellbeing and your cardiorespiratory fitness. Finding physical activities you enjoy and committing to being and remaining active are important first steps towards increasing your physical activity level.</p>
				<p>You can check out the 2008 Physical Activity Guidelines for Americans <a class="offsite" href='pa_fact_sheet_adults.pdf'>HERE</a>, or see the complete guide at <a class="offsite" href='http://health.gov/paguidelines/'>http://health.gov/paguidelines/</a>.</p>
				<p>Make sure physical activity is right for you; before starting, take this <a taret="blank"  href='pa_readiness_questionaire.pdf'>self-evaluation questionnaire</a> or talk to a healthcare provider.</p>
			</div>
			<div class="pa_2 pa_3">
				<p>Physical activity is one of the many ways you can help to improve both your wellbeing and your cardiorespiratory fitness. Continuing to be involved in physical activities you enjoy, exploring new physical activities, and committing to remaining active are important first steps towards increasing your physical activity level.</p> 
				<p>You can check out the 2008 Physical Activity Guidelines for Americans <a target="blank"  href='pa_fact_sheet_adults.pdf'>HERE</a>, or see the complete guide at <a class="offsite" href='http://health.gov/paguidelines/'>http://health.gov/paguidelines/</a>.</p>
			</div>
			<div class="pa_4">
				<p>Physical activity is one of the many ways you can help to improve both your wellbeing, and your cardiorespiratory fitness. Continuing to be involved in physical activities you enjoy and committing to remaining active are extremely important to maintaining your health and wellbeing.</p>
				<p>You can check out the 2008 Physical Activity Guidelines for Americans <a target="blank"  href='pa_fact_sheet_adults.pdf'>HERE</a>, or see the complete guide at <a class="offsite"  href='http://health.gov/paguidelines/'>http://health.gov/paguidelines/</a> for tips on potentially increasing your level of physical activity.</p>
			</div>
			<div class="pa_5">
				<p>Good job! Physical activity is one of many ways to maintain your wellbeing and your cardiorespiratory fitness. Continue to be involved with the physical activities you enjoy and commit to <a class="offsite"  href='http://health.gov/paguidelines/'>remaining active</a>.</p>
			</div>
		</aside>
		<aside class="lang sp">
			<h3>Actividad física</h3>
			<div class="funfact"><p><i>Sabía usted que ser físicamente activo(a) puede ayudarle a mejorar y salud mental y actitud?</i></p></div>
			<div class="pa_1">
				<p>Actividad física es una de muchas maneras que puede mejorar su salud y bienestar, al igual que su salud cardiorespiratoria. Es importante encontrar actividades físicas que usted disfrute y comprometerse a estar activo(a) y mantenerse activo(a) para mejorar sus niveles de actividad física.</p>
				<p><a class="offsite" href='http://www.who.int/dietphysicalactivity/factsheet_recommendations/es/'>Visite las sugerencias de actividad física para de Organización Mundial de Salud</a>.</p>
				<p>Asegúrese que la actividad física es apropiada para usted; antes de comenzar, complete este cuestionario de auto-evaluación o hable con su proveedor de salud.</p>
			</div>
			<div class="pa_2 pa_3">
				<p>Actividad física es una de muchas maneras que puede mejorar su salud y bienestar, al igual que su salud cardiorespiratoria. Continuando a participar en actividades físicas que usted disfruta, explorando nuevas actividades físicas, a comprometiéndose a mantenerse activo(a) son pasos importantes para mejorar sus niveles de actividad física.</p> 
				<p>Es importante encontrar actividades físicas que usted disfrute y comprometerse a estar activo(a) y mantenerse activo(a) para mejorar sus niveles de actividad física</p>
				<p><a target="blank"  href='/'>Visite las sugerencias de actividad física para de Organización Mundial de Salud</a></p>
			</div>
			<div class="pa_4">
				<p>Actividad física es una de muchas maneras que puede mejorar su salud y bienestar, al igual que su salud cardiorespiratoria. Continuando a participar en actividades físicas que usted disfruta y comprometiéndose a mantenerse activo(a) es importante para mantener su salud y bienestar.</p>
				<p><a target="blank"  href='/'>Visite las sugerencias de actividad física para de Organización Mundial de Salud</a></p>
			</div>
			<div class="pa_5">
				<p>Buen trabajo! Actividad física es una de muchas maneras que puede mantener su salud y bienestar, al igual que su salud cardiorespiratoria. Continúe participando en actividades físicas que usted disfruta y comprométase a mantenerse activo(a).</p>
			</div>
		</aside>
	</div>
	<div id="met_smoking" class="met_desc">
		<aside class="lang en">
			<h3>Smoking</h3>
			<div class="funfact"><p><i>Did you know tobacco smoke harms our furry friends? Secondhand smoke is bad for pets just as it is for people.</i></p></div>
			<div class="yes">
				<p>Smoking has been linked to an increased risk for a number of health issues, including cardiovascular disease, high cholesterol, and many types of cancers. Second-hand smoke can also affect the hearts, lungs, and wellbeing of those around you. Quitting smoking has been shown to reduce the risk for these issues and others.</p>
				<p>Quitting is possible, but it can be hard. Millions of individuals in the United States have been able to quit over the past several years. When considering quitting, it is important to have a plan. Consider talking to your doctor or health care provider, visiting <a class="offsite" href='https://smokefree.gov/'>https://smokefree.gov/</a>, or calling 1-800-QUIT-NOW to get started.</p>
			</div>
			<div class="no">
				<p>You indicated that you do not smoke. Good job! Avoiding smoking plays an important role in keeping you healthy and maintaining your wellbeing.</p>
			</div>
		</aside>
		<aside class="lang sp">
			<h3>Fumar</h3>
			<div class="funfact"><p><i>Sabía usted que el humo de tabaco le hace daño a nuestros animales preferidos? El humo inhalado de segunda mano es dañino para los animales al igual que lo es para las personas.</i></p></div>
			<div class="yes">
				<p>Fumar causa una variedad de problemas de salud, incluyendo enfermedades cardiovasculares, colesterol alto, y muchos tipos de cáncer. Humo de segunda mano también le hace daño a los corazones, los pulmones y el bienestar de las personas que le rodean a usted. Ceder de fumar es la cosa más importante que puede hacer para su salud y para proteger la salud de esos que le rodean.</p>
				<p>Ceder de fumar es posible, y hay tratamientos disponibles para ayudar. Es bueno desarrollar un plan. Hable con su médico o proveedor de salud, visite <a class="offsite" href='https://espanol.smokefree.gov/preparacion-de-un-plan-para-dejar-de-fumar'>https://espanol.smokefree.gov</a> o llame a 1-800-QUIT-NOW para comenzar</p>
			</div>
			<div class="no">
				<p>Usted indicó que no fuma cigarrillos. Excelente! El no fumar es importante para mantearse saludable y mantener su bienestar.</p>
			</div>
		</aside>
	</div>
</div>
