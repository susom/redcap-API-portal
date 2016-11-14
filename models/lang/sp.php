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
		"ACCOUNT_EMAIL_IN_USE_ACTIVE"			=> "Correo electrónico %m1% ya está en uso. Si ha olvidado su contraseña, puede ingresar una nueva <a href='login.php'>Formulario para Entrar</a>",
		"ACCOUNT_NEW_ACTIVATION_SENT"			=> "Gracias por registrarse con la Iniciativa de WELL Para Vida! Enviamos un link de activación a su correo electrónico. Por favor verifique su correo y siga las instrucciones para activar su cuenta. Si no recibe el mensaje dentro de una hora, contáctenos a wellforlife@stanford.edu", 
		"ACCOUNT_SPECIFY_NEW_PASSWORD"			=> "Por favor entre su contraseña nueva",	
		"ACCOUNT_NOT_YET_ELIGIBLE"				=> "Gracias por su interés en WELL Para Vida! No cualifica para participar en este momento. %m1% Estaremos en comunicación con usted sobre estudios relacionados a WELL Para Vida y con más información al expandir nuestro proyecto.",
		"ACCOUNT_NEED_LOCATION"					=> "Por favor entre su código postal o ciudad",
		"ACCOUNT_TOO_YOUNG"						=> "Todavía no tiene 18 años.", 
		"ACCOUNT_NOT_IN_USA"					=> "Este estudio es sólo para participantes viviendo en los Estados Unidos.", 
		"ACTIVATION_MESSAGE"					=> "Tiene que activar su cuenta para poder entrar. Siga la siguiente página para activar su cuenta. \n\n%m1%register.php?uid=%m3%&activation=%m2%",
		
		//REGISTER
		"ACCOUNT_REGISTER" 						=> "Regístrese para este estudio", 
		"ACCOUNT_FIRST_NAME" 					=> "Nombre",
		"ACCOUNT_LAST_NAME" 					=> "Apellido",
		"ACCOUNT_YOUR_EMAIL" 					=> "Email / Correo electrónico",
		"ACCOUNT_EMAIL_ADDRESS" 				=> "Email / Correo electrónico",
		"ACCOUNT_REENTER_EMAIL" 				=> "Nuevamente entre su correo electrónico",
		"ACCOUNT_YOUR_LOCATION" 				=> "Lugar de vivienda",
		"ACCOUNT_CITY" 							=> "Ciudad",
		"ACCOUNT_ZIP" 							=> "Código Postal",
		"ACCOUNT_ALREADY_REGISTERED" 			=> "¿Se ha registrado anteriormente?",
		"ACCOUNT_BIRTH_YEAR" 					=> "¿En qué año nació?", 
		"ACCOUNT_18_PLUS" 						=> "¿Tiene 18 años o más?",
		"ACCOUNT_USA_CURRENT" 					=> "¿Vive en los Estados Unidos?",
		"ACCOUNT_AGREE" 						=> "Al oprimir el botón de Entregar, estoy de acuerdo a ser contactado sobre estudios relacionados a WELL Para Vida y recibir más información.", 
		"ACCOUNT_ELITE_THANKS" 					=> "Gracias por ser uno de los primeros 500 participantes. La data que recolectamos nos ayudará a mejorar el bienestar de todos! Demuestre su logro con orgullo! ",

		"ACCOUNT_NEW_PASSWORD" 					=> "Contraseña Nueva",
		"ACCOUNT_PASSWORD" 						=> "Contraseña",
		"ACCOUNT_PASSWORD_AGAIN" 				=> "Contraseña nuevamente",

		"ACCOUNT_LOGIN_PAGE" 					=> "Página de Entrada",
		"ACCOUNT_REGISTER_PAGE" 				=> "Página de Registro",
		
		"REGISTER_STUDY" 						=> "Regístrese para el estudio",
		"REGISTER_TOKEN_INVALID_1" 				=> "El código de activación es inválido o ha expirado. Esto puede surgir si ha regenerado un código nuevo pero siguió el link de un mensaje antiguo.",		
		"REGISTER_TOKEN_INVALID_2" 				=> "Código de activación inválido <br><a class='alink' href='login.php'>Oprima aquí</a> y seleccione 'Olvidé Contraseña’ para obtener un nuevo código.", 
		
		//LOGIN
		"ACCOUNT_LOGIN_CONTINUE" 				=> "Por favor Entre para continuar",		
		"ACCOUNT_LOGIN_NOW" 					=> "Entre Ahora",
		"ACCOUNT_NEXT_STEP" 					=> "Próximo Paso",
		
		//CONSENT
		"CONSENT_BULLET_1" 						=> "Necesitamos su permiso antes de hacer preguntas, por favor lea el documento de consentimiento informado",		
		"CONSENT_BULLET_2" 						=> "La encuesta inicial tomará 20-30 minutos para completar – pero no tiene que llenarla toda a una vez", 
		"CONSENT_BULLET_3" 						=> "Nos comunicaremos con usted cada varios meses",		
		"CONSENT_BULLET_4" 						=> "Vamos a añadir nuevas encuestas, materiales y contenido y le invitaremos a participar a través del tiempo",
		"CONSENT_WELCOME" 						=> "BIENVENIDOS!",
		"CONSENT_CONTACT" 						=> "PARA PREGUNTAS SOBRE EL ESTUDIO, COMUNIQUESE CON el Director del Protocolo, John Ioannidis al (650) 725-5465 o la Co-Directora del Protocolo, Sandra Winter a (650) 723-8513.",		
		"CONSENT_I_AGREE" 						=> "Estoy de acuerdo",
		
		//FORGOT PASSWORD AND ACCOUNT SETUP
		"FORGOTPASS" 							=> "¿Olvidó contraseña?",
		"FORGOTPASS_RESET" 						=> "Reiniciar Contraseña",
		"FORGOTPASS_RESET_FORM" 				=> "Formulario para reiniciar contraseña",		
		"FORGOTPASS_PLEASE_ANSWER" 				=> "Por favor conteste las preguntas de seguridad.",		
		"FORGOTPASS_RECOVERY_ANSWER" 			=> "Contestación para recuperar contraseña",
		"FORGOTPASS_SEC_Q" 						=> "Pregunta de Seguridad",
		"FORGOTPASS_ANSWER_QS" 					=> "Contestar mis preguntas de seguridad",
		"FORGOTPASS_EMAIL_ME" 					=> "Envíame un link para reiniciar contraseña", 
		"FORGOTPASS_RECOVERY_METHOD" 			=> "Seleccionar método de recuperación",
		"FORGOTPASS_BEGIN_RESET" 				=> "Entre su correo electrónico para reiniciar contraseña", 
		"FORGOTPASS_SUGGEST"					=> "Oprima '¿Olvidó Contraseña?’ para reiniciar su contraseña. O <a href=\"register.php\">regístrese aquí</a>.",
		"FORGOTPASS_INVALID_TOKEN"				=> "Código inválido.", 
		"FORGOTPASS_REQUEST_EXISTS"				=> "Enviamos un mensaje a su correo electrónico hace %m1% minutos.<br>Por favor verifique su correo electrónico o intente nuevamente más tarde.", 
		"FORGOTPASS_REQUEST_SUCCESS"			=> "El proceso para reiniciar contraseña ha comenzado. <br> Por favor verifique su correo electrónico para instrucciones.", 
		"FORGOTPASS_UPDATED" 					=> "Contraseña actualizada",
		"FORGOTPASS_INVALID_VALUE" 				=> "Contraseña inválida, reinicie los valores para la pregunta", 
		"FORGOTPASS_Q_UPDATED" 					=> "Las preguntas para recuperar su contraseña ahora están actualizadas!", 
		"FORGOTPASS_SEC_Q_SETUP" 				=> "Por favor establezca su contraseña y preguntas de seguridad", 
		"FORGOTPASS_SEC_Q_ANSWERS" 				=> "Para poder ayudar a recuperar su contraseña olvidada o perdida, por favor provea contestaciones a las siguientes preguntas de seguridad.", 
		"FORGOTPASS_CHOSE_QUESTION" 			=> "Seleccione una pregunta de la lista",
		"FORGOTPASS_WRITE_CUSTOM_Q" 			=> "Escriba una pregunta de seguridad personalizada",

		//MAIL
		"MAIL_ERROR"							=> "Error intentando enviar correo, contacte su administrador de servidor de correo", 
		"MAIL_TEMPLATE_BUILD_ERROR"				=> "Error desarrollándose en plantilla de correo electrónico", 
		"MAIL_TEMPLATE_DIRECTORY_ERROR"			=> "No se puede abrir directorio de plantillas de correo electrónico. Quizás intente establecer el directorio de correo a %m1%",
		"MAIL_TEMPLATE_FILE_EMPTY"				=> "Archivo de plantilla está vacío… nada para enviar", 

		//Miscellaneous
		"GENERAL_YES" 							=> "Si",
		"GENERAL_NO" 							=> "No",
		"GENERAL_BACK" 							=> "Regresar",
		"GENERAL_NEXT" 							=> "Próximo",
		"GENERAL_SUBMIT" 						=> "Entregar",
		"CONFIRM"								=> "Confirmar",
		"ERROR"									=> "Error",
	));

	//DASHBOARD TRANSLATIONS
	$lang = array_merge($lang, array(
		 "WELL_FOR_LIFE" 							=> "WELL por Vida"
		,"MY_DASHBOARD" 							=> "Mi Tablero"
		,"CORE_SURVEYS" 							=> "Encuestas principales"
		,"LOGOUT" 									=> "Cerrar sesión"
		,"MY_STUDIES"								=> "Mis estudios"
		,"MY_PROFILE" 								=> "Mi Perfil"
		,"CONTACT_US" 								=> "Contáctenos"
		,"GET_HELP" 								=> "Dónde obtener ayuda"
		,"GET_HELP_TEXT" 							=> "<p>Para una emergencia médica, llame al 911 oa su proveedor de cuidado de salud.</p><p>Para la salud mental, por favor visite <a href=\"https://www.mentalhealth.gov/get-help/\" class='offsite'>MentalHealth.gov</a>.</p>"
		,"QUESTION_FOR_WELL" 						=> "Pregunta para WELL"
		,"YOUVE_BEEN_AWARDED" 						=> "Te han concedido una"
		,"GET_WHOLE_BASKET" 						=> "Get the whole fruit basket!"
		,"CONTINUE_SURVEY" 							=> "Continue the rest of the survey."
		,"CONGRATS_FRUITS" 							=> "Congratulations, you got all the fruits! <br/><br/> Check out some of the new modules under 'Learn More'. <br><br/> In the meantime we invite you to watch this video from our WELL for life director. <br/><br/>"
		,"FITNESS_BADGE" 							=> "You've been awarded a fitness badge"
		,"GET_ALL_BADGES" 							=> "Obtener la cesta de fruta entera!"
		,"CONGRATS_ALL_FITNESS_BADGES"				=> "Congratulations, you got all the fitness badges! <br/> Check back soon for the opportunity to earn new awards!"
		,"DONE_CORE" 								=> "Todo hecho con encuestas básicas!"
		,"TAKE_BLOCK_DIET" 							=> "Take the Block diet assessment, free to WELL participants.  This survey typically takes 30-50 minutes to complete and provides instant feedback."
		,"HOW_WELL_EAT" 							=> "Que tan bien comes?"
		,"COMPLETE_CORE_FIRST" 						=> "Por favor complete las encuestas básicas primero"
		,"PLEASE_COMPLETE" 							=> "Por favor complete "
		,"WELCOME_TO_WELL" 							=> "<b>Bienvenido</b> to WELL por Vida! <u>Haga clic aquí</u> Para comenzar su aventura aquí…</a>"
		,"WELCOME_BACK_TO" 							=> "<b>Dar una buena acogida</b> to WELL por Vida!</a>"
		,"REMINDERS" 								=> "Recordatorios"
		,"ADDITIONAL_SURVEYS" 						=> "Encuestas adicionales"
		,"SEE_PA_DATA" 								=> "Rellene la parte 'Su actividad física' de la encuesta para ver sus datos graficados aquí!"
		,"HOW_DO_YOU_COMPARE" 						=> "¿Cómo se compara con otros encuestadores"
		,"SITTING" 									=> "Sentado"
		,"WALKING" 									=> "Para caminar"
		,"MODACT" 									=> "Actividad moderada"
		,"VIGACT" 									=> "Actividad vigorosa"
		,"NOACT" 									=> "Liviano/Ninguna actividad"
		,"SLEEP" 									=> "Dormir"
		,"AVG_ALL_USERS" 							=> "Promedio de todos los usuarios (Horas/Día)"
		,"HOW_YOU_SPEND_TIME" 						=> "Cómo usted pasa su tiempo cada día"
		,"SUNRISE" 									=> "Amanecer"
		,"SUNSET" 									=> "La puesta del sol"
		,"WIND" 									=> "viento"
		,"DASHBOARD"								=> "Tablero"
		,"WELCOME_BACK"								=> "Dar una buena acogida"
	));
?>
