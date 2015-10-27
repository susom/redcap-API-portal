<?php

function getSurveyLink($id,$instrument,$event, $api_token, $api_url) {
  //echo "</br>ID is $id and $instrument and $event";
//logIt("</br>ID is $id and $instrument and $event", "DEBUG");

	 $params = array(
 	 		'token' => $api_token,
	 		 'content' => 'surveyLink',		
		 	'record' => $id,
			'instrument' => $instrument,
			'event' => $event
			);
	$result = RC::http_post( $api_url, $params);
//	logIt( "getSurveyLink result: " .  print_r($result), "DEBUG");

	return $result;
}

function getRetakeHash($id,$instrument,$event, $api_token, $api_url) {
	//echo "</br>ID is $id and $instrument and $event";
	//logIt("</br>ID is $id and $instrument and $event", "DEBUG");

	$params = array(
			'token' => $api_token,
			'content' => 'surveyReturnCode',
			'record' => $id,
			'instrument' => $instrument,
			'event' => $event
	);
	$result = RC::http_post( $api_url, $params);
	//	logIt( "getSurveyLink result: " .  print_r($result), "DEBUG");

	return $result;
}

function getFieldStatus($id, $field,$event, $api_token, $api_url) {
  $status = '';
  $params = array(
	'token' => $api_token,
    'format' => 'json',
    'content' => 'record',
                 'records' => array($id),
                 'events' => array($event),
                 'fields' => array($field)
	);

	$result = RC::http_post( $api_url, $params);

	$j = json_decode($result, true);
        if (isset($j->error)) {
                echo 'Error in writeToApi: ' . $j->error . ' from ' . print_r($data,true);
                return null;
	}

//    logIt( "getFieldStatus : <pre>".print_r($j, true) ."</pre>", "DEBUG");
//	logIt("SIZE: ". sizeof($j));

        if (sizeof($j) >0) {
	  $status = $j[0][$field];
//	  logIt("RETURNING ".$status);
	}

    if ($status == 1) {
       return true;
    }

    return false;
}

function retrieveEmailsSent($id, $api_token, $api_url) {
    $params = array(
    		'token' => $api_token, 
    		'record' => array($id)   		
    );
    $result = RC::http_post( $api_url, $params);
    $j = json_decode($result, true);
    logIt( "informedConsented result " .  print_r($j), "DEBUG");
    return $j;
}

function informedConsented($id, $api_token, $api_url) {
	$params = array(
			'token' => $api_token,
			'format' => 'json',		
			'content' => 'record',
			'records' => array($id),
			'forms' => array('informed_consents')
 	);
		
	$result = RC::http_post( $api_url, $params);
	
	$j = json_decode($result, true);
	//logIt( "informedConsented result " .  print_r($j), "DEBUG");
	
	if (isset($j->error)) {
		echo 'Error in writeToApi: ' . $j->error . ' from ' . print_r($data,true);
		return null;
	}
	
	$status_medrec = $j[0]['medrec_release_auth_txt___1'];
	$status_hipaa = $j[0]['hipaa___1'];
	$status_registry_consent = $j[0]['registry_consent___1'];
	$consent_complete = $j[0]['informed_consents_complete'];
	
	
// 	logIt( "getConsentStatus 1 medrec: ".$status_medrec, "DEBUG");
// 	logIt( "getConsentStatus 1 hipaa ".$status_hipaa, "DEBUG");
// 	logIt( "getConsentStatus 1 registry ". $status_registry_consent, "DEBUG");
// 	logIt( "getConsentStatus 1 complete ". $consent_complete, "DEBUG");
	
	//this won't work since she is using NO = 2; check each individually
	$status  = $status_medrec + $status_hipaa + $status_registry_consent + $consent_complete;

	
	if (($status_medrec=="1") & ($status_hipaa=="1") & ($status_registry_consent=="1") & ($consent_complete=="2") ){
		return true;
	}
	
	logIt("returning FALSE for informedConsent: sum is  ". $status);
	
	return false;
}
	

function getAllCompletionStatus($id,$instruments,$event,  $api_token, $api_url) {

	$complete_fieldnames = array();
	
	foreach ($instruments as &$value) {
		$complete_fieldnames[] = $value.'_complete';		
	}

	$extra_params = array(
			      'content' => 'record',
			      'records' => $id,
                  'fields' => $complete_fieldnames
	);
	$result = RC::callApi($extra_params,$api_url);

	return $result;
}



?>