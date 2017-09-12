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
		"ACCOUNT_SPECIFY_F_L_NAME" 				=> "請輸入您的名字和姓氏",
		"ACCOUNT_SPECIFY_USERNAME" 				=> "請輸入您的用戶帳號",
		"ACCOUNT_SPECIFY_PASSWORD" 				=> "請輸入您的密碼",
		"ACCOUNT_SPECIFY_EMAIL"					=> "請輸入您的電子郵件地址。",
		"ACCOUNT_INVALID_EMAIL"					=> "無效的電子郵件地址。",
		"ACCOUNT_EMAIL_MISMATCH"				=> "電子郵件必須符合。",
		"ACCOUNT_USER_OR_PASS_INVALID"			=> "電子郵件和/或密碼不被識別。",
		"ACCOUNT_PASS_MISMATCH"					=> "密碼必須符合。",
		"ACCOUNT_EMAIL_IN_USE_ACTIVE"			=> "電子郵件 %m1% 已在使用中。如果您忘記了密碼，您可以重新設定於 <a href='login.php'>登錄表單</a>",
		"ACCOUNT_NEW_ACTIVATION_SENT"			=> "感謝您註冊WELL for Life計劃！我們已向您的電子郵件發送了帳戶激活鏈接。請檢查您的電子郵件，並點擊裡面的鏈接。如果您在1小時內沒有收到電子郵件，請通過wellforlife@stanford.edu與我們聯繫。",
		"ACCOUNT_SPECIFY_NEW_PASSWORD"			=> "請輸入您的新密碼。",	
		"ACCOUNT_NOT_YET_ELIGIBLE"				=> "感謝您對WELL for Life計劃的關注！您目前沒有資格參加。%m1% 當計劃擴大時，我們將與您聯繫關於WELL for Life相關研究和信息。",
		"ACCOUNT_NEED_LOCATION"					=> "請輸入您的郵政編碼或城市。",
		"ACCOUNT_TOO_YOUNG"						=> "您尚未年滿18歲。",
		"ACCOUNT_NOT_IN_USA"					=> "本研究計劃僅適用於居住在美國的參與者。",
		"ACTIVATION_MESSAGE"					=> "您需要首先激活您的帳戶，然後才能登錄。請按照以下鏈接激活您的帳戶。 \n\n%m1%register.php?uid=%m3%&activation=%m2%",							
		"ACCOUNT_ERROR_TRY_AGAIN"				=> "再試一次...", 
		"ACCOUNT_ERROR_ATTEMPTS"				=> " 剩餘嘗試次數。",
		"ACCOUNT_ERROR_ATTEMPT"					=> " 剩餘嘗試次數。", 

		//REGISTER
		"ACCOUNT_REGISTER" 						=> "註冊本研究計劃",
		"ACCOUNT_YOUR_NAME"						=> "您的姓名",
		"ACCOUNT_FIRST_NAME" 					=> "英文名字",
		"ACCOUNT_LAST_NAME" 					=> "英文姓氏",
		"ACCOUNT_YOUR_EMAIL" 					=> "電子郵件",
		"ACCOUNT_EMAIL_ADDRESS" 				=> "電子郵件地址",
		"ACCOUNT_EMAIL_ADDRESS_OR_USERNAME"		=> "電子郵件或用戶帳號",
		"ACCOUNT_USERNAME"						=> "用戶帳號",
		"ACCOUNT_PARTICIPANT_ID"				=> "參與者編號",
		"ACCOUNT_REENTER_EMAIL" 				=> "重新輸入電子郵件",
		"ACCOUNT_YOUR_LOCATION" 				=> "您居住的地區",
		"ACCOUNT_CITY" 							=> "英文城市",
		"ACCOUNT_ZIP" 							=> "郵政編碼",
		"ACCOUNT_ALREADY_REGISTERED" 			=> "已經註冊過？",
		"ACCOUNT_BIRTH_YEAR" 					=> "你的出生年份是?",
		"ACCOUNT_18_PLUS" 						=> "你滿18歲嗎？",
		"ACCOUNT_USA_CURRENT" 					=> "你現在住在美國嗎？",
		"ACCOUNT_AGREE" 						=> "點擊送出按鈕,我同意被聯繫有關WELL for Life相關研究和信息。",
		"ACCOUNT_ELITE_THANKS" 					=> "感謝您成為我們首批500名參與者之一。我們收集的數據將幫助我們改善我們的健康！",
		"STEP_REGISTER"							=> "註冊",
		"STEP_VERIFY"							=> "驗證郵件",
		"STEP_CONSENT"							=> "同意",
		"STEP_SECURITY"							=> "安全性",

		"ACCOUNT_NEW_PASSWORD" 					=> "新密碼",
		"ACCOUNT_PASSWORD" 						=> "密碼",
		"ACCOUNT_PASSWORD_AGAIN" 				=> "密碼再一次",

		"ACCOUNT_LOGIN_PAGE" 					=> "登入頁面",
		"ACCOUNT_REGISTER_PAGE" 				=> "註冊頁面",
		
		"REGISTER_STUDY" 						=> "註冊本研究計劃",
		"REGISTER_TOKEN_INVALID_1" 				=> "提供的電子郵件激活鏈接無效或已過期。",
		"REGISTER_TOKEN_INVALID_2" 				=> "電子郵件激活鏈接無效 <br><a class='alink' href='login.php'>點擊此處</a> 並選擇“忘記密碼”獲取新的鏈接。",

		//LOGIN
		"ACCOUNT_LOGIN_CONTINUE" 				=> "請登入完後再繼續",
		"ACCOUNT_LOGIN_NOW" 					=> "登入",
		"ACCOUNT_NEXT_STEP" 					=> "下一步",
		
		//CONSENT
		"IRB_ONLY" 								=> "IRB Use Only",
		"IRB_EXPIRATION"						=> "Expiration Date",
		"CONSENT_BULLET_1" 						=> "我們需要您的許可，我們才能向您提出任何問題，因此請閱讀以下同意文件",
		"CONSENT_BULLET_2" 						=> "初步問卷需時20-30分鐘完成。但你不需要一次性填寫完。",
		"CONSENT_BULLET_3" 						=> "每隔幾個月我們會联系您有關WELL的最新資訊。",
		"CONSENT_BULLET_4" 						=> "我們將會持續加入新的問卷，材料和內容，並邀請您參與。",
		"CONSENT_WELCOME" 						=> "歡迎加入!",
		"CONSENT_CONTACT" 						=> "如您對本研究有任何問題，請聯繫總監 John Ioannidis 650-725-5465 或Sandra Winter 副總監650-723-8513。",
		"CONSENT_I_AGREE" 						=> "我同意",
		"CONSENT_PRINT" 						=> "列印",
		
		//FORGOT PASSWORD AND ACCOUNT SETUP
		"FORGOTPASS" 							=> "忘記密碼？",
		"FORGOTPASS_RESET" 						=> "重設密碼",
		"FORGOTPASS_RESET_FORM" 				=> "密碼重新設定表單",
		"FORGOTPASS_PLEASE_ANSWER" 				=> "請回答您設定的安全問題。",
		"FORGOTPASS_RECOVERY_ANSWER" 			=> "恢復原密碼的答案。",
		"FORGOTPASS_SEC_Q" 						=> "安全性問題。",
		"FORGOTPASS_ANSWER_QS" 					=> "回答設訂的安全性問題。",
		"FORGOTPASS_EMAIL_ME" 					=> "請送我密碼重新設定的電子郵件。",
		"FORGOTPASS_RECOVERY_METHOD" 			=> "選擇回復方法",
		"FORGOTPASS_BEGIN_RESET" 				=> "輸入電子郵件以開始密碼重新設置",
		"FORGOTPASS_SUGGEST"					=> "點擊 “忘記密碼？” 來重新設置密碼。或<a href=\"register.php\">註冊加入</a>.",
		"FORGOTPASS_INVALID_TOKEN"				=> "無效鏈接",
		"FORGOTPASS_REQUEST_EXISTS"				=> "忘記的密碼授權電子郵件已於 %m1% 分鐘前發送。<br>請檢查您的電子郵件或稍後再試。",
		"FORGOTPASS_REQUEST_SUCCESS"			=> "已啟動密碼重設程序。<br>請查看您的電子郵件以了解後續步驟。",
		"FORGOTPASS_UPDATED" 					=> "密碼更新",
		"FORGOTPASS_INVALID_VALUE" 				=> "密碼重置值無效",
		"FORGOTPASS_Q_UPDATED" 					=> "恢復密碼問題更新！",
		"FORGOTPASS_SEC_Q_SETUP" 				=> "密碼設置和安全性問題",
		"FORGOTPASS_SEC_Q_ANSWERS" 				=> "為了幫助您恢復丟失或忘記的密碼，請提供以下安全問題的答案。",
		"FORGOTPASS_CHOSE_QUESTION" 			=> "請從列表中選擇一個問題",
		"FORGOTPASS_WRITE_CUSTOM_Q" 			=> "編寫自定安全性問題",

		//MAIL
		"MAIL_ERROR"							=> "嘗試郵件時出錯，請與網站管理員聯繫",
		"MAIL_TEMPLATE_BUILD_ERROR"				=> "建構電子郵件範本時出錯",
		"MAIL_TEMPLATE_DIRECTORY_ERROR"			=> "無法打開郵件範本目錄。也許嘗試設置郵件目錄於 %m1%",
		"MAIL_TEMPLATE_FILE_EMPTY"				=> "範本文件是空的...沒有發送任何文件",
		//Miscellaneous
		"GENERAL_YES" 							=> "是",
		"GENERAL_NO" 							=> "否",
		"GENERAL_BACK" 							=> "回上一步",
		"GENERAL_NEXT" 							=> "下一步",
		"GENERAL_SUBMIT" 						=> "送出",
		"CONFIRM"								=> "确认",
		"ERROR"									=> "错误",
	));
	

	//DASHBOARD TRANSLATIONS
	$lang = array_merge($lang, array(
		 "WELL_FOR_LIFE" 							=> "WELL人生"
		,"MY_DASHBOARD" 							=> "信息中心"
		,"CORE_SURVEYS" 							=> "主要問卷"
		,"LOGOUT" 									=> "登出"
		,"MY_STUDIES"								=> "參與研究"
		,"MY_ASSESSMENTS" 							=> "我的報告"
		,"NO_ASSESSMENTS"							=> "請先完成附加問卷，即可查看您的評估報告"
		,"YOUR_ASSESSMENT"							=> "您的評估報告"
        ,"MY_PROFILE" 								=> "個人資料"
		,"CONTACT_US" 								=> "聯繫我們"
		,"GET_HELP" 								=> "需要幫助"
	     ,"GET_HELP_TEXT" 						   => "<p>對於醫療緊急情況，請致電911或您的醫療保健提供者。</p><p>對於精神健康問題，請參考 <a href=\"https://www.mentalhealth.gov/get-help/\" class='offsite'>MentalHealth.gov</a>.</p>"
		,"QUESTION_FOR_WELL" 						=> "對我們有疑問"
		,"YOUVE_BEEN_AWARDED" 						=> "你已经获得了"
		,"GET_WHOLE_BASKET" 						=> "獲得整籃水果！"
		,"CONTINUE_SURVEY" 							=> "繼續完成剩下的問卷。"
		,"CONGRATS_FRUITS" 							=> "恭喜，你得到了所有的水果！<br/><br/> 查看 “附加問卷” 下的一些新問卷。 <br><br/>同時，我們邀請您觀看我們總監給您的視頻。 <br/><br/>"
		,"FITNESS_BADGE" 							=> "您已獲得健身徽章"
		,"GET_ALL_BADGES" 							=> "獲得所有的健身徽章！"
		,"CONGRATS_ALL_FITNESS_BADGES"				=> "恭喜，你已獲得所有的健身徽章！ <br/>請稍後再回來取得新的獎勵！"
		,"DONE_CORE" 								=> "完成所有主要問卷！"
		,"TAKE_BLOCK_DIET" 							=> "所有WELL參與者可免費參與飲食評估。此問卷通常需要30-50分鐘完成，並提供即時評估報告。"
		,"HOW_WELL_EAT" 							=> "您吃得健康嗎？"
		,"COMPLETE_CORE_FIRST" 						=> "請先完成主要問卷"
		,"PLEASE_COMPLETE" 							=> "請完成 "
		,"WELCOME_TO_WELL" 							=> "<b>歡迎</b>參與WELL人生研究計劃！<u>點擊此處</u>開始您的WELL旅程！</a>"
		,"WELCOME_BACK_TO" 							=> "<b>歡迎回到</b>WELL人生！</a>"
		,"REMINDERS" 								=> " 
温馨提醒"
		,"ADDITIONAL_SURVEYS" 						=> "附加問卷"
		,"SEE_PA_DATA" 								=> "填寫問卷的 “您的身體活動” 部分，可看到您與其他參與者的數據比較圖表！"
		,"HOW_DO_YOU_COMPARE" 						=> "您與其他參與者比較圖表"
		,"SHORT_SCORE_OVER_TIME"					=> "Your WELLbeing Score"
		,"OTHERS_WELL_SCORES"						=> "Other's WELL Score over time"
		,"OTHERS_SCORE"								=> "Average Participant Score"
		,"USERS_SCORE"								=> "Your Score"
		,"HIGHER_WELLBEING"							=> "Higher Wellbeing"
		,"LOWER_WELLBEING"							=> "Lower Wellbeing"
		,"NOT_ENOUGH_USER_DATA" 					=> "Please complete surveys to calculate your score."
		,"NOT_ENOUGH_OTHER_DATA" 					=> "Not enough data to calculate Average."
		,"SITTING" 									=> "坐著" 
		,"WALKING" 									=> "走路"
		,"MODACT" 									=> "中度身體活動"
		,"VIGACT" 									=> "重度身體活动"
		,"NOACT" 									=> "輕度/無活動"
		,"SLEEP" 									=> "睡眠"
		,"YOU_HOURS_DAY"						             => "您（小時/天）"
		,"AVG_ALL_USERS" 							=> "平均所有參與者（小時/天）"
		,"HOW_YOU_SPEND_TIME" 						=> "您每天如何分配時間"
		,"SUNRISE" 									=> "日出"
		,"SUNSET" 									=> "日落"
		,"WIND" 									=> "有風"
		,"DASHBOARD"								=> "信息中心"
		,"WELCOME_BACK"								=> "歡迎回來"
		,"SUBMIT"									=> "送出"
		,"SAVE_EXIT"								=> "儲存並退出"
		,"SUBMIT_NEXT"								=> "下一步"
		,"MAT_DATA_DISCLAIM" 						=> "以下數據部分是參考心肺健康和國家健康標準的研究而準備的。這些結果不能替代醫療保健提供者的建議。在做出任何可能影響您健康的改變之前，請諮詢您的醫生。"
		,"MAT_SCORE_40"								=> "在接下來的4年中，和你的分數相同的人（10分中有6.6分）可能失去做他們喜歡或珍惜的活動的能力。然而，有很多方法你可以做，以提高你的功能性能力。"
		,"MAT_SCORE_50"								=> "在接下來的4年中，和你的分數相同的人（10分中有5.2分）可能失去做他們喜歡或珍惜的活動的能力。然而，有很多方法你可以做，以提高你的功能性能力。"
		,"MAT_SCORE_60"								=> "在接下來的4年中，和你的分數相同的人（10分中有3.5分）可能失去做他們喜歡或珍惜的活動的能力。然而，有很多方法你可以做，以提高你的功能性能力。."
		,"MAT_SCORE_70"								=> "在接下來的4年中，和你的分數相同的人應該不會失去做他們喜歡或珍惜的活動的能力。然而，有很多方法你可以做，以提高你的功能性能力。繼續您的努力，並嘗試保持您的功能性能力！"
		,"TCM_POSITIVE"								=> "肯定"
		,"TCM_NEGATIVE"								=> "没有"
		,"TCM_ESSENTIALLY_POS"						=> "傾向肯定"

		,"PROFILE_JOINED"							=> "已加入"
		,"PROFILE_NICKNAME"							=> "暱稱"
		,"ACCOUNT_MIDDLE_NAME"						=> "中間名字"
		,"PROFILE_CONTACT_NAME"						=> "聯絡人姓名"
		,"PROFILE_CONTACT_PHONE"					=> "聯絡電話"
		,"PROFILE_STREET_ADDRESS"					=> "街道地址"
		,"PROFILE_APARTMENT"						=> "公寓號碼"
		,"ACCOUNT_STATE"							=> "州"
		,"EDIT_PROFILE"								=> "編輯"
		,"PROFILE_EDIT"								=> "個人資料"
	));

	$template_security_questions = array(
			'concert'	=> '你參加的第一場音樂會是？',
			'cartoon'	=> '你小時候最喜歡的卡通是？',
			'reception'	=> '你婚禮招待的地點是？',
			'sib_nick'	=> '你年紀最大的兄弟姐妹的暱稱是？',
			'street'	=> '你三年級時住的街道名是？',
			'pet'		=> '你的第一隻寵物的名字是？',
			'parents'	=> '你的母親和父親在哪個城鎮第一次遇見？',
			'grammie'	=> '你的祖母的暱稱是？',
			'boss'		=> '你的第一個老闆的名字是？',
			'sib_mid'	=> '你年紀最大的兄弟姐妹的名字是？',
			'custom'	=> ''
		);

	$websiteName = "WELL for Life";



// {
// 	 "translations" : {
// 		 "tw" : {
// 		 		 "wellbeing_questions" 	      	: "身心健康問題"
// 				,"a_little_bit_about_you" 		: "有些關於您"
// 				,"your_physical_activity" 		: "你的身體活動量"
// 				,"your_sleep_habits" 			: "你的睡眠習慣"
// 				,"your_tobacco_and_alcohol_use" : "你的煙草和酒類使用量"
// 				,"your_diet" 					: "你的飲食狀況"
// 				,"your_health" 					: "你的健康狀況"
// 				,"about_you" 					: "關於您"
// 				,"your_social_and_neighborhood_environment" : "您的社交和鄰里環境"
// 				,"contact_information" 			: "聯繫資料"
// 				,"your_feedback" 				: "您的建議"
// 		}
// 	}
// }
?>


