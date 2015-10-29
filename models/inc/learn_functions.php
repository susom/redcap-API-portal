<?php
function getSurveyLink($id,$instrument,$event, $api_token = REDCAP_API_TOKEN, $api_url = REDCAP_API_URL) {
	$params = array(
		'token' 		=> $api_token,
		'content' 		=> 'surveyLink',		
		'record' 		=> $id,
		'instrument' 	=> $instrument,
		'event' 		=> $event
	);
	$result = RC::http_post( $api_url, $params);
	// logIt( "{id : $id,  instrument : $instrument} getSurveyLink result: " .  json_decode($result), "DEBUG");

	return $result;
}


function getRetakeHash($id,$instrument,$event, $api_token = REDCAP_API_TOKEN, $api_url = REDCAP_API_URL) {
	$params = array(
		'token' 		=> $api_token,
		'content' 		=> 'surveyReturnCode',
		'record' 		=> $id,
		'instrument' 	=> $instrument,
		'event' 		=> $event
	);
	$result = RC::http_post( $api_url, $params);
	// logIt( "{id : $id,  instrument : $instrument} getRetakeHash result: " .  json_decode($result), "DEBUG");

	return $result;
}


function getFieldStatus($id, $field,$event, $api_token = REDCAP_API_TOKEN, $api_url = REDCAP_API_URL) {
	$status = '';
	$params = array(
		'token' 	=> $api_token,
	    'format' 	=> 'json',
	    'content'	=> 'record',
		'records' 	=> array($id),
		'events' 	=> array($event),
		'fields' 	=> array($field)
	);
	$result = RC::http_post( $api_url, $params);
	$json 	= json_decode($result, true);
    if (isset($json->error)) {
	    echo 'Error in writeToApi: ' . $json->error ;
	    return null;
	}
	// logIt( "getFieldStatus : <pre>" .json_decode($json, true) ."</pre>", "DEBUG");

    if (sizeof($json) >0) {
		$status = $json[0][$field];
	}

    if ($status == 1) {
       return true;
    }

    return false;
}


function retrieveEmailsSent($id, $api_token = REDCAP_API_TOKEN, $api_url = REDCAP_API_URL) {
    $params = array(
		'token' 	=> $api_token, 
		'record'	=> array($id)   		
    );
    $result = RC::http_post( $api_url, $params);
    $json 	= json_decode($result, true);
    // logIt( "informedConsented result " .  json_decode($json), "DEBUG");
    
    return $json;
}


function informedConsented($id, $api_token = REDCAP_API_TOKEN, $api_url = REDCAP_API_URL) {
	$params = array(
			'token' 	=> $api_token,
			'format' 	=> 'json',		
			'content' 	=> 'record',
			'records' 	=> array($id),
			'forms' 	=> array('informed_consents')
 	);
		
	$result = RC::http_post( $api_url, $params);
	$json 	= json_decode($result, true);
	//logIt( "informedConsented result " .  json_decode($j), "DEBUG");

	if (isset($json->error)) {
		echo 'Error in writeToApi: ' . $json->error . ' from ' . print_r($data,true);
		return null;
	}
	
	$status_medrec 				= isset($json[0]['medrec_release_auth_txt___1']) ?  $json[0]['medrec_release_auth_txt___1'] : 0;
	$status_hipaa 				= isset($json[0]['hipaa___1']					) ? $json[0]['hipaa___1']					 : 0;
	$status_registry_consent 	= isset($json[0]['registry_consent___1']		) ? $json[0]['registry_consent___1']		 : 0;
	$consent_complete 			= isset($json[0]['informed_consents_complete']	) ? $json[0]['informed_consents_complete']	 : 0;
	
	//this won't work since she is using NO = 2; check each individually
	$status  = $status_medrec + $status_hipaa + $status_registry_consent + $consent_complete;
	if (($status_medrec=="1") & ($status_hipaa=="1") & ($status_registry_consent=="1") & ($consent_complete=="2") ){
		return true;
	}
	// logIt("returning FALSE for informedConsent: sum is  ". $status);
	
	return false;
}
	

function getAllCompletionStatus($id,$instruments,$event,  $api_token = REDCAP_API_TOKEN, $api_url = REDCAP_API_URL) {
	$complete_fieldnames = array();
	foreach ($instruments as &$value) {
		$complete_fieldnames[] = $value.'_complete';		
	}

	$extra_params = array(
		'content' 	=> 'record',
		'records' 	=> $id,
		'fields'	=> $complete_fieldnames
	);
	$result = RC::callApi($extra_params,$api_url);	
	
	return $result;
}
?>