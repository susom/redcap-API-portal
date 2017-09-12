<?php
	/*
		UserPie Langauge File.
		Language: English.
	*/
	
	/*
		%m1% - Dymamic markers which are replaced at run time by the relevant index.
	*/

	$lang = array();
	
	//Account
	$lang = array_merge($lang,array(
		//ERROR AND POPUP
		"ACCOUNT_SPECIFY_F_L_NAME" 				=> "Por favor entre su nombre y apellido",
		"ACCOUNT_SPECIFY_USERNAME" 				=> "Por favor entre su nombre de usuario",
		"ACCOUNT_SPECIFY_PASSWORD" 				=> "Por favor entre su contraseña",
		"ACCOUNT_SPECIFY_EMAIL"					=> "Por favor entre su correo electrónico",
        "ACCOUNT_INVALID_EMAIL"		 			=> "Correo electrónico erróneo",
		"ACCOUNT_EMAIL_MISMATCH"				=> "Correos electrónicos no son idénticos",
		"ACCOUNT_USER_OR_PASS_INVALID"			=> "Correo electrónico y/o contraseña no reconocidos",
		"ACCOUNT_PASS_MISMATCH"					=> "Contraseñas no son idénticas",
		"ACCOUNT_EMAIL_IN_USE_ACTIVE"			=> "Correo electrónico %m1% ya está en uso. Sí ha olvidado su contraseña, puede ingresar una nueva <a href='login.php'>Formulario para Entrar</a>",
		"ACCOUNT_NEW_ACTIVATION_SENT"			=> "¡Gracias por registrarse con la Iniciativa de WELL Bien Para Vida! Enviamos un link de activación a su correo electrónico. Por favor verifique su correo y siga las instrucciones para activar su cuenta. Sí no recibe el mensaje dentro de una hora, contáctenos a wellforlife@stanford.edu", 
		"ACCOUNT_SPECIFY_NEW_PASSWORD"			=> "Por favor entre su contraseña nueva",	
		"ACCOUNT_NOT_YET_ELIGIBLE"				=> "¡Gracias por su interés en WELL Bien Para Vida! No es elegible para participar en este momento. %m1% Estaremos en comunicación con usted sobre estudios relacionados a WELL Bien Para Vida y con más información al expandir nuestro proyecto.",
		"ACCOUNT_NEED_LOCATION"					=> "Por favor entre su código postal o ciudad",
		"ACCOUNT_TOO_YOUNG"						=> "Todavía no tiene 18 años.", 
		"ACCOUNT_NOT_IN_USA"					=> "Este estudio es sólo para participantes viviendo en los Estados Unidos.", 
		"ACTIVATION_MESSAGE"					=> "Tiene que activar su cuenta para poder entrar. Siga la siguiente página para activar su cuenta. \n\n%m1%register.php?uid=%m3%&activation=%m2%",
		"ACCOUNT_ERROR_TRY_AGAIN"				=> "Intente  otra vez…. Le queda ", 
		"ACCOUNT_ERROR_ATTEMPTS"				=> " oportunidades.",
		"ACCOUNT_ERROR_ATTEMPT"					=> " oportunidad.", 
		
		//REGISTER
		"ACCOUNT_REGISTER" 						=> "Regístrese para este estudio", 
		"ACCOUNT_YOUR_NAME"						=> "Su Nombre",
		"ACCOUNT_FIRST_NAME" 					=> "Nombre",
		"ACCOUNT_LAST_NAME" 					=> "Apellido",
		"ACCOUNT_YOUR_EMAIL" 					=> "Email / Correo electrónico",
		"ACCOUNT_EMAIL_ADDRESS" 				=> "Email / Correo electrónico",
		"ACCOUNT_REENTER_EMAIL" 				=> "Nuevamente entre su correo electrónico",
		"ACCOUNT_EMAIL_ADDRESS_OR_USERNAME" 	=> "Email Address or Username",
		"ACCOUNT_PARTICIPANT_ID"				=> "Participant ID",
		"ACCOUNT_USERNAME"						=> "Username",
		"ACCOUNT_YOUR_LOCATION" 				=> "Lugar de vivienda",
		"ACCOUNT_CITY" 							=> "Ciudad",
		"ACCOUNT_ZIP" 							=> "Código Postal",
		"ACCOUNT_ALREADY_REGISTERED" 			=> "¿Se ha registrado anteriormente?",
		"ACCOUNT_BIRTH_YEAR" 					=> "¿En qué año nació?", 
		"ACCOUNT_18_PLUS" 						=> "¿Tiene 18 años o más?",
		"ACCOUNT_USA_CURRENT" 					=> "¿Vive en los Estados Unidos?",
		"ACCOUNT_AGREE" 						=> "Al oprimir el botón de Entregar, estoy de acuerdo a ser contactado sobre estudios relacionados a WELL Bien Para Vida y recibir más información.", 
		"ACCOUNT_ELITE_THANKS" 					=> "¡Gracias por ser uno de los primeros 500 participantes. ¡La data que recolectamos nos ayudará a mejorar el bienestar de todos! ¡Demuestre su logro con orgullo! ",
		"STEP_REGISTER"							=> "Registrarse",
		"STEP_VERIFY"							=> "Verificar Email",
		"STEP_CONSENT"							=> "Consentimiento",
		"STEP_SECURITY"							=> "Seguridad",

		"ACCOUNT_NEW_PASSWORD" 					=> "Contraseña Nueva",
		"ACCOUNT_PASSWORD" 						=> "Contraseña",
		"ACCOUNT_PASSWORD_AGAIN" 				=> "Contraseña",

		"ACCOUNT_LOGIN_PAGE" 					=> "Entrar",
		"ACCOUNT_REGISTER_PAGE" 				=> "Registrarse",
		
		"REGISTER_STUDY" 						=> "Regístrese para el estudio",
		"REGISTER_TOKEN_INVALID_1" 				=> "El código de activación es inválido o ha expirado. Esto puede surgir sí ha regenerado un código nuevo pero siguió el link de un mensaje antiguo.",		
		"REGISTER_TOKEN_INVALID_2" 				=> "Código de activación inválido <br><a class='alink' href='login.php'>Oprima aquí</a> y seleccione 'Olvidé Contraseña’ para obtener un nuevo código.", 
		
		//LOGIN
		"ACCOUNT_LOGIN_CONTINUE" 				=> "Por favor Entre para continuar",		
		"ACCOUNT_LOGIN_NOW" 					=> "Entre Ahora",
		"ACCOUNT_NEXT_STEP" 					=> "Próximo Paso",
		
		//CONSENT
		"IRB_ONLY" 								=> "IRB Use Only",
		"IRB_EXPIRATION"						=> "Expiration Date",
		"CONSENT_BULLET_1" 						=> "Necesitamos su permiso antes de hacer preguntas, por favor lea el documento de consentimiento informado",		
		"CONSENT_BULLET_2" 						=> "La encuesta inicial tomará 20-30 minutos para completar – pero no tiene que llenarla toda a la vez", 
		"CONSENT_BULLET_3" 						=> "Nos comunicaremos con usted cada varios meses",		
		"CONSENT_BULLET_4" 						=> "Vamos a añadir nuevas encuestas, materiales y contenido y le invitaremos a participar a través del tiempo",
		"CONSENT_WELCOME" 						=> "¡BIENVENIDOS!",
		"CONSENT_CONTACT" 						=> "PARA PREGUNTAS SOBRE EL ESTUDIO, COMUNIQUESE CON el Director del Protocolo, John Ioannidis al (650) 725-5465 o la Co-Directora del Protocolo, Sandra Winter a (650) 723-8513.",		
		"CONSENT_I_AGREE" 						=> "Estoy de acuerdo",
		"CONSENT_PRINT" 						=> "Print",
		
		//FORGOT PASSWORD AND ACCOUNT SETUP
		"FORGOTPASS" 							=> "¿Olvidó su contraseña?",
		"FORGOTPASS_RESET" 						=> "Reiniciar Contraseña",
		"FORGOTPASS_RESET_FORM" 				=> "Formulario para reiniciar contraseña",		
		"FORGOTPASS_PLEASE_ANSWER" 				=> "Por favor conteste las preguntas de seguridad.",		
		"FORGOTPASS_RECOVERY_ANSWER" 			=> "Contestación para recuperar contraseña",
		"FORGOTPASS_SEC_Q" 						=> "Pregunta de Seguridad",
		"FORGOTPASS_ANSWER_QS" 					=> "Contestar mis preguntas de seguridad",
		"FORGOTPASS_EMAIL_ME" 					=> "Envíame un link para reiniciar contraseña", 
		"FORGOTPASS_RECOVERY_METHOD" 			=> "Seleccionar método de recuperación",
		"FORGOTPASS_BEGIN_RESET" 				=> "Entre su correo electrónico para reiniciar contraseña", 
		"FORGOTPASS_SUGGEST"					=> "Oprima '¿Olvidó Contraseña?' para reiniciar su contraseña. O <a href='register.php'>regístrese aquí</a>.",
		"FORGOTPASS_INVALID_TOKEN"				=> "Código inválido.", 
		"FORGOTPASS_REQUEST_EXISTS"				=> "Enviamos un mensaje a su correo electrónico hace %m1% minutos.<br>Por favor verifique su correo electrónico o intente nuevamente más tarde.", 
		"FORGOTPASS_REQUEST_SUCCESS"			=> "El proceso para reiniciar su contraseña ha comenzado. <br> Por favor verifique su correo electrónico para instrucciones.", 
		"FORGOTPASS_UPDATED" 					=> "Contraseña actualizada",
		"FORGOTPASS_INVALID_VALUE" 				=> "Contraseña inválida, reinicie los valores para la pregunta", 
		"FORGOTPASS_Q_UPDATED" 					=> "¡Las preguntas para recuperar su contraseña ahora están actualizadas!", 
		"FORGOTPASS_SEC_Q_SETUP" 				=> "Por favor establezca su contraseña<br> y sus preguntas de seguridad", 
		"FORGOTPASS_SEC_Q_ANSWERS" 				=> "Para poder ayudar a recuperar su contraseña olvidada o perdida, por favor provea contestaciones a las siguientes preguntas de seguridad.", 
		"FORGOTPASS_CHOSE_QUESTION" 			=> "Seleccione una pregunta de la lista",
		"FORGOTPASS_WRITE_CUSTOM_Q" 			=> "Escriba una pregunta de seguridad personalizada",

		//MAIL
		"MAIL_ERROR"							=> "Error intentando enviar correo electrónico, contacte su administrador de servidor de correo electrónico", 
		"MAIL_TEMPLATE_BUILD_ERROR"				=> "Error desarrollando plantilla de correo electrónico", 
		"MAIL_TEMPLATE_DIRECTORY_ERROR"			=> "No se puede abrir directorio de plantillas de correo electrónico. Quizás intente establecer el directorio de correo a %m1%",
		"MAIL_TEMPLATE_FILE_EMPTY"				=> "Archivo de plantilla está vacío… nada para enviar", 

		//Miscellaneous
		"GENERAL_YES" 							=> "Sí",
		"GENERAL_NO" 							=> "No",
		"GENERAL_BACK" 							=> "Regresar",
		"GENERAL_NEXT" 							=> "Próximo",
		"GENERAL_SUBMIT" 						=> "Entregar",
		"CONFIRM"								=> "Confirmar",
		"ERROR"									=> "Error",
	));

	//DASHBOARD TRANSLATIONS
	$lang = array_merge($lang, array(
		 "WELL_FOR_LIFE" 							=> "WELL Bien Para Vida"
		,"MY_DASHBOARD" 							=> "Mis cuestionarios"
		,"CORE_SURVEYS" 							=> "Encuestas principales"
		,"LOGOUT" 									=> "Cerrar sesión"
		,"MY_STUDIES"								=> "Mis estudios"
		,"MY_ASSESSMENTS" 							=> "My Assessments"
		,"NO_ASSESSMENTS"							=> "No feedback available yet.<br>Please complete the Supplemental Surveys to see your custom WELLness feedback."
		,"YOUR_ASSESSMENT"							=> "Your Assessment"
		,"MY_PROFILE" 								=> "Mi Perfil"
		,"CONTACT_US" 								=> "Contáctenos"
		,"GET_HELP" 								=> "Dónde obtener ayuda"
		,"GET_HELP_TEXT" 							=> "<p>Para una emergencia médica, llame al 911 o a su médico o proveedor de salud.</p><p>Para salud mental, por favor visite <a href='https://www.mentalhealth.gov/get-help/' class='offsite'>MentalHealth.gov</a>.</p>"
		,"QUESTION_FOR_WELL" 						=> "Pregunta para WELL"
		,"YOUVE_BEEN_AWARDED" 						=> "Usted se ha ganado"
		,"GET_WHOLE_BASKET" 						=> "¡Obtenga la canasta entera de frutas!"
		,"CONTINUE_SURVEY" 							=> "Continúe con el resto de la encuesta."
		,"CONGRATS_FRUITS" 							=> "¡Felicidades, usted ha recibido todas las frutas!  <br/><br/> Busque nuestras encuestas nuevas bajo 'Aprender Más'. <br><br/> Por ahora le invitamos a ver este video de la directora de WELL. <br/><br/>"
		,"FITNESS_BADGE" 							=> "¡Usted se ha ganado una medalla de salud física!"
		,"GET_ALL_BADGES" 							=> "Obtenga la canasta entera de frutas!"
		,"CONGRATS_ALL_FITNESS_BADGES"				=> "¡Felicidades, used ha recibido todas las medallas de salud física! <br/> Regrese pronto para la oportunidad de ganarse más premios!"
		,"DONE_CORE" 								=> "¡He terminado mi primera encuesta!"
		,"TAKE_BLOCK_DIET" 							=> "Complete la encuesta de dieta de Block, gratis para todos los participantes de WELL. Esta encuesta toma típicamente 30-50 minutos y le provee sugerencias instantáneamente."
		,"HOW_WELL_EAT" 							=> "¿Cuan bien come usted?"
		,"COMPLETE_CORE_FIRST" 						=> "Por favor complete las encuestas básicas primero"
		,"PLEASE_COMPLETE" 							=> "Por favor complete "
		,"WELCOME_TO_WELL" 							=> "<b>Bienvenidos</b> a WELL Bien Para Vida! <u>Oprima aquí</u> para comenzar su aventura con WELL…</a>"
		,"WELCOME_BACK_TO" 							=> "<b>Bienvenidos nuevamente </b> a WELL Bien Para Vida!</a>"
		,"REMINDERS" 								=> "Recordatorios"
		,"ADDITIONAL_SURVEYS" 						=> "Encuestas adicionales"
		,"SEE_PA_DATA" 								=> "¡Complete la sección 'Su actividad física' de la encuesta para ver sus datos representados aquí!"
		,"HOW_DO_YOU_COMPARE" 						=> "¿Cómo se compara con otros participantes?"
		,"SHORT_SCORE_OVER_TIME"					=> "Your WELLbeing Score"
		,"OTHERS_WELL_SCORES"						=> "Other's WELL Score over time"
		,"OTHERS_SCORE"								=> "Average Participant Score"
		,"USERS_SCORE"								=> "Your Score"
		,"HIGHER_WELLBEING"							=> "Higher Wellbeing"
		,"LOWER_WELLBEING"							=> "Lower Wellbeing"
		,"NOT_ENOUGH_USER_DATA" 					=> "Please complete surveys to calculate your score."
		,"NOT_ENOUGH_OTHER_DATA" 					=> "Not enough data to calculate Average."
		,"SITTING" 									=> "Sentado(a)"
		,"WALKING" 									=> "Caminando"
		,"MODACT" 									=> "Actividad moderada"
		,"VIGACT" 									=> "Actividad vigorosa"
		,"NOACT" 									=> "Actividad Liviana o Ninguna Actividad"
		,"SLEEP" 									=> "Durmiendo"
		,"YOU_HOURS_DAY"							=> "Usted (Horas/Día)"
		,"AVG_ALL_USERS" 							=> "Promedio de todos los usuarios (Horas/Día)"
		,"HOW_YOU_SPEND_TIME" 						=> "Descripción de cómo usted pasa su tiempo cada día"
		,"SUNRISE" 									=> "Amanecer"
		,"SUNSET" 									=> "Atardecer"
		,"WIND" 									=> "Viento"
		,"DASHBOARD"								=> "Página Principal"
		,"WELCOME_BACK"								=> "¡Bienvenidos nuevamente!"
		,"SUBMIT"									=> "Entregar"
		,"SAVE_EXIT"								=> "Guardar y Salir"
		,"SUBMIT_NEXT"								=> "Entregar/Próximo"
		,"MAT_DATA_DISCLAIM" 						=> "La siguiente data ha sido preparada en parte utilizando información de estudios previos sobre salud cardio-respiratoria y estándares nacionales de salud. Estos resultados no deberían sustituir recomendaciones o sugerencias de su médico. Hable con su doctor antes de hacer cualquier cambio que podría afectar su salud."
		,"MAT_SCORE_40"								=> "En los próximos 4 años, personas con sus resultados tienen alta probabilidad (6.6 de 10) de perder la habilidad de hacer las actividades físicas que disfrutan y valoran. Sin embargo, hay muchas cosas que usted puede hacer para mejorar su habilidad física y capacidad funcional."
		,"MAT_SCORE_50"								=> "En los próximos 4 años, personas con sus resultados tienen probabilidad (5.2 de 10) de perder la habilidad de hacer las actividades físicas que disfrutan y valoran. Sin embargo, hay muchas cosas que usted puede hacer para mejorar su habilidad física y capacidad funcional."
		,"MAT_SCORE_60"								=> "En los próximos 4 años, personas con sus resultados tienen alguna probabilidad (3.5 de 10) de perder la habilidad de hacer las actividades físicas que disfrutan y valoran. Sin embargo, hay muchas cosas que puede hacer para mejorar su habilidad física y capacidad funcional.Las personas con sus resultados tienen baja probabilidad de perder la habilidad de poder hacer actividades físicas que disfrutan o valoran. ¡Siga con el buen trabajo e intente mantener su capacidad funcional!"
		,"TCM_POSITIVE"								=> "Positive"
		,"TCM_NEGATIVE"								=> "Negative"
		,"TCM_ESSENTIALLY_POS"						=> "Tendency (Essentially) Positive"
	
		,"PROFILE_JOINED"							=> "Miembro desde"
		,"PROFILE_NICKNAME"							=> "Apodo"
		,"ACCOUNT_MIDDLE_NAME"						=> "Segundo Nombre"
		,"PROFILE_CONTACT_NAME"						=> "Nombre de Contacto"
		,"PROFILE_CONTACT_PHONE"					=> "Número de Teléfono de Contacto"
		,"PROFILE_STREET_ADDRESS"					=> "Dirección"
		,"PROFILE_APARTMENT"						=> "Apt"
		,"ACCOUNT_STATE"							=> "Estado"
		,"EDIT_PROFILE"								=> "Editar"
		,"PROFILE_EDIT"								=> "Perfil"
	));

	$template_security_questions = array(
			'concert'	=> '¿Cual fue su primer concierto?',
			'cartoon'	=> '¿Cual fue su serie de muñequitos preferida como niño(a)?',
			'reception'	=> '¿Dónde tuvo su recepción de boda?',
			'sib_nick'	=> '¿Cual era el apodo de su hermano(a) mayor?',
			'street'	=> '¿En qué calle vivía usted en 3er grado?',
			'pet'		=> '¿Cual era el nombre de su primera mascota?',
			'parents'	=> '¿Cómo se llama la ciudad donde sus padres se conocieron?',
			'grammie'	=> '¿Cual es el apodo de su abuela materna?',
			'boss'		=> '¿Cómo se llamaba su primer supervisor o supervisora?',
			'sib_mid'	=> '¿Cual es el segundo nombre de su hermano(a) mayor?',
			'custom'	=> ''
		);

	$websiteName = "WELL Bien Para Vida";
?>
