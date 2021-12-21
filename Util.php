<?php
namespace Supplitynetsuite\Accountintegration;

class Util
{
    public function __construct() {
    }
	
	/***************************************************************************
	 * Function: Run CURL
	 * Description: Executes a CURL request
	 * Parameters: url (string) - URL to make request to
	 *             method (string) - HTTP transfer method
	 *             headers - HTTP transfer headers
	 *             postvals - post values
	 **************************************************************************/
	public function run_curl($url, $method = 'GET', $headers = null, $postvals = null){
		$ch = curl_init($url);
		
		if ($method == 'GET'){
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		} else {
			$options = array(
				CURLOPT_HEADER => true,
				CURLOPT_VERBOSE => true,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => $postvals,
				CURLOPT_CUSTOMREQUEST => $method,
				CURLOPT_TIMEOUT => 180
			);
			curl_setopt_array($ch, $options);
		}
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
	}
	/***************************************************************************
	 * Function: Send To NetSuite
	 * Description: Send a Request to NetSuite
	 * Parameters: method (string) - HTTP transfer method
	 *             data - sent values
	 *			   url (string) - URL to make request to
	 **************************************************************************/
	public function sendToNetSuite($data,$method,$url){

		// REPLACE WITH YOUR ACTUAL DATA OBTAINED WHILE CREATING NEW INTEGRATION
		$consumerKey = "YOUR CONSUMER KEY";
		$consumerSecret = "YOUR CONSUMER SECRET KEY";
		$accessToken = "YOUR ACCESS TOKEN";
		$accessTokenSecret = "YOUR SECRET ACCESS TOKEN";
		
		$realm = "*******";
		$oauth_nonce = md5(mt_rand());
		$oauth_timestamp = time();
		$oauth_signature_method = 'HMAC-SHA1';
		$oauth_version = "1.0";
		
		$base_string =
			"POST&" . urlencode($url) . "&" .
			urlencode(
				"deploy=1"
			  . "&oauth_consumer_key=" . $consumerKey
			  . "&oauth_nonce=" . $oauth_nonce
			  . "&oauth_signature_method=" . $oauth_signature_method
			  . "&oauth_timestamp=" . $oauth_timestamp
			  . "&oauth_token=" . $accessToken
			  . "&oauth_version=" . $oauth_version
			  . "&realm=" . $realm
			  . "&script=**"
			);
			
		//Signature
		$sig_string = urlencode($consumerSecret) . '&' . urlencode($accessTokenSecret);
		$signature = base64_encode(hash_hmac("sha1", $base_string, $sig_string, true));

		$auth_header = "OAuth "
			. 'oauth_signature="' . rawurlencode($signature) . '", '
			. 'oauth_version="' . rawurlencode($oauth_version) . '", '
			. 'oauth_nonce="' . rawurlencode($oauth_nonce) . '", '
			. 'oauth_signature_method="' . rawurlencode($oauth_signature_method) . '", '
			. 'oauth_consumer_key="' . rawurlencode($consumerKey) . '", '
			. 'oauth_token="' . rawurlencode($accessToken) . '", '  
			. 'oauth_timestamp="' . rawurlencode($oauth_timestamp) . '", '
			. 'realm="' . rawurlencode($realm) .'"';

		//Run curl
		$url = $url . '?&script=**' . '&deploy=1' . '&realm=' . $realm;
		$headers = [
			'Authorization: ' . $auth_header,
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string)
		];
		
		$response = $this->run_curl($url,$method,$headers,data_string);
	}
}