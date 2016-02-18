<?php

//------------------------------------------------------------------
// A STATIC CLASS FOR REDCAP API METHODS
//------------------------------------------------------------------

class RC {
	// Make API Call
	public static function callApi($extra_params = null, $api_url = REDCAP_API_URL,  $json_decode = true) {
		$default_params = array(
			'token' 	=> REDCAP_API_TOKEN,
			'format' 	=> 'json',
			'content' 	=> 'record'
		);	

		$params = array_merge($default_params, $extra_params);
		//logIt("New Params:" . print_r($params,true), "DEBUG");


		// logIt(json_encode($params,1),"DEBUG");
		$result = self::http_post($api_url, $params);
		// logIt('call API Raw result: ' . print_r($params), "DEBUG");	

		if ($json_decode) {
			$result = json_decode($result, true);
			if (isset($result->error)) {
				logIt('Error in writeToApi: ' . $result->error , "DEBUG");
				return false;
			}
		} else {
		 	// Return raw result
		}
      	
      	//logIt('call API result: ' . print_r($result,true), "DEBUG");
      	return $result;
	}
	
	// Write to the API
	public static function writeToApi($data, $extra_params = array(), $api_url = REDCAP_API_URL) {
		// Force data into an array of arrays if not already set
		if (!is_array(current($data))) $data = array($data);
	
		$extra_params = array_merge(
			array(
				'content' 	=> 'record',
				'type' 		=> 'flat',
				'data' 		=> json_encode($data)
			), $extra_params
		);

		$j = self::callApi($extra_params);
		return $j;
	}
	
	// Send HTTP Post request and receive/return content
	static function http_post($url="", $params=array(), $timeout=null, $content_type='application/x-www-form-urlencoded') {
		// If params are given as an array, then convert to query string format, else leave as is
		if ($content_type == 'application/json') {
			// Send as JSON data
			$param_string = (is_array($params)) ? json_encode($params) : $params;
		} else {
			// Send as Form encoded data
			$param_string = (is_array($params)) ? http_build_query($params, '', '&') : $params;
		}

		// Check if cURL is installed first. If so, then use cURL instead of file_get_contents.
		if (function_exists('curl_init')) {
			// Use cURL
			$curlpost = curl_init();
			curl_setopt($curlpost, CURLOPT_SSL_VERIFYPEER, FALSE);  //THIS SHOULD BE TRUE TO DISALLOW MiM attack?
			curl_setopt($curlpost, CURLOPT_VERBOSE, 0);
			curl_setopt($curlpost, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curlpost, CURLOPT_AUTOREFERER, true);
			curl_setopt($curlpost, CURLOPT_MAXREDIRS, 10);
			curl_setopt($curlpost, CURLOPT_URL, $url);
			curl_setopt($curlpost, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curlpost, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($curlpost, CURLOPT_POSTFIELDS, $param_string);
			//if (!sameHostUrl($url)) curl_setopt($curlpost, CURLOPT_PROXY, PROXY_HOSTNAME); // If using a proxy
			curl_setopt($curlpost, CURLOPT_FRESH_CONNECT, 1); // Don't use a cached version of the url
			if (is_numeric($timeout)) {
				curl_setopt($curlpost, CURLOPT_CONNECTTIMEOUT, $timeout); // Set timeout time in seconds
			}
			// If not sending as x-www-form-urlencoded, then set special header
			if ($content_type != 'application/x-www-form-urlencoded') {
				curl_setopt($curlpost, CURLOPT_HTTPHEADER, array("Content-Type: $content_type", "Content-Length: " . strlen($param_string)));
			}

			$response 	= curl_exec($curlpost);
			$info 		= curl_getinfo($curlpost);
			curl_close($curlpost);

			// If returns an HTTP 404 error, return false
			if (isset($info['http_code']) && $info['http_code'] == 404){ 
				return json_encode(array("error" => "404 on $url"));
			}

			// echo  "<pre>";
			// print_r($response);
			// exit;
			
			return $response;

		} elseif (ini_get('allow_url_fopen')){ // Try using file_get_contents if allow_url_open is enabled
			// Set http array for file_get_contents
			$http_array = array(
				'method'	=>'POST',
				'header'	=>"Content-type: $content_type",
				'content'	=>$param_string
			);
			if (is_numeric($timeout)) {
				$http_array['timeout'] = $timeout; // Set timeout time in seconds
			}
	
			// Use file_get_contents
			$content = @file_get_contents($url, false, stream_context_create(array('http'=>$http_array)));
	
			// Return the content
			if ($content !== false) {
				return $content;
			} else { // If no content, check the headers to see if it's hiding there (why? not sure, but it happens)
				$content = implode("", $http_response_header);
				// If header is a true header, then return false, else return the content found in the header
				return (substr($content, 0, 5) == 'HTTP/') ? false : $content;
			}

			return false;
		}
	}
}
