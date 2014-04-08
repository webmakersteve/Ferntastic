<?php


/**
 * User cart. Used to connect the user to a database associated with the cart.
 * This allows data to be assigned to a user and the gives the user the ability to change 
 * Key value pairs. Although generally associated with a cart, it can provide other uses,
 * if one keeps an open mind
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.1
 *
 */

if (!function_exists('Fn')) die();

/**
 * CartError extends LogError
 *
 * CartError is the exception object thrown every time Cart crashes for any reason.
 * The errors return strings and database entries are defined in the errors.xml page
 * loaded using the resource module of Ferntastic library.
 *
 * @see LogError
 *
 */

class PaypalError extends LogError {
	private $type = __CLASS__;	
}


/** PAYPAL CONSTANTS 
-------------------------------------------------------------- */

$arr = array(

	'CREDIT_CARD_NUMBER' => 'ACCT',
	'CC_TYPE' => 'CREDITCARDTYPE',
	'FIRST_NAME' => 'FIRSTNAME',
	'LAST_NAME' => 'LASTNAME',
	'CVV2' => 'CVV2',
	'BILLING_ADD1' => 'STREET',
	'BILLING_STATE' => 'STATE',
	'BILLING_CITY' => 'CITY',
	'BILLING_ZIP' => 'ZIP',
	'EXPMONTH' => 'EXPMONTH',
	'EXPYEAR' => 'EXPYEAR'
	);

foreach ($arr as $k=>$v) define($k,$v,false);
unset($arr);

/** END PAYPAL CONSTANTS 
-------------------------------------------------------------- */

class Paypal {
   /**
    * Last error message(s)
    * @var array
    */
   protected $_errors = array();

   /**
    * API Credentials
    * Use the correct credentials for the environment in use (Live / Sandbox)
    * @var array
    */
   protected $_credentials = array(
      'USER' => 'Ado0fBCRj82abTB18Yy_2r_81TcttaNe172OPPMhqFfUnhe5hCyci-fUyqSm',
      'SECRET' => 'EEV1HBD7-5-TuRL2L3Lx841Z3SbXpQTRODYlJgnvREt0rAqzq1Y1-YYzITGa',
   );

   /**
    * API endpoint
    * Live - https://api-3t.paypal.com/nvp
    * Sandbox - https://api-3t.sandbox.paypal.com/nvp
    * @var string
    */
   const _endPoint = 'https://api.sandbox.paypal.com/v1';

   /**
    * API Version
    * @var string
    */

   private $response = array();
   private $postParams = array();

	/**
	 * Now let us do the variables
	 */
	 
	private $_token = null; //token will be placed here for authentication 
	private $_tokentype = null; //used for calls
	private $_tokenexpire = 0; //expiry of the token shows we need to get a new one
	private $_callmade = 0; //this is when the call is made. will be loaded by the construct function
	private $_cache = null; //for getting already made readonly calls. Not implemented 5.24 v0.1
	
	
	private $APPID = null;
	
	function __construct() {
//		$this->_cache = new Cache(); NYI
		$this->_cache = array();
		$this->init(); //initial connect	
	}
	
	protected function auth() {
		
		//we need to do a curl call to do this	
		$ch = curl_init();
		
		//these are necessary headers
		$headers = array();
		$headers[] = "Accept: application/json";
		$headers[] = "Accept-Language: en_US";
		
		//this is the post data for the auth
		$postdata = array('grant_type' => 'client_credentials');
		$postdata = http_build_query( $postdata );
		
		//password and username
		$up = $this->_credentials['USER'] . ":" . $this->_credentials['SECRET']; //username and password business
		
		$url = Paypal::_endPoint."/oauth2/token";
		curl_setopt( $ch, CURLOPT_URL, $url );
		
		curl_setopt( $ch, CURLOPT_USERPWD, $up );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		
		$json = curl_exec( $ch ); // execute the curl command to the api server
		curl_close( $ch );
		
		return json_decode( $json ); //this should fetch us what we need
		
	}
	
	protected function init() {
		
		$this->_callmade = time();
		
		$this->_cache['oauth'] = $oauth = $this->auth(); //save this for use. export to two variables.
		
		//now let's extract the data we need
		$this->_tokenexpire = $oauth->expires_in + $this->_callmade; //used for caching purposes
		$this->_token = $oauth->access_token; //this is important
		$this->_tokentype = $oauth->token_type;
		$this->APPID = $oauth->app_id;
			
	}

	public function response($field=null,$index=null) {
		
		if ($index==null) $index = count($this->response)-1;
		
		if ($field==null or !isset($this->response[$index][$field])) return $this->response[$index];
		else return $this->response[$index][$field];
		
	}
	
	public function success(&$response) {
		
		$res = $this->response();
		$response = $res;
		
		if (isset($res->id)) return $res->id;
		else {
			$this->parseErrors();
			return false;	
		}
		
	}
	
	protected function parseErrors() {
		
		$return = $this->response();
		
		switch (strtolower($return->name)) {
					
			case 'validation_error':
				
				$info = array();
				foreach ($return->details as $detail) {
					$path = explode('.',$detail->field); //this is the improper field. we need to follow the path. split it by dots
					
					$offending_value = '';
					$temp = (!is_object($postdata)) ? json_decode($postdata) : $postdata;
					foreach( $path as $value ) {
						//let's check to see if this is an array value
						if (preg_match( "#([a-z_-]+)\[([0-9]+)\]#i", $value, $matches )) {
							
							$index = $matches[2];
							$name = $matches[1];
							$temp = $temp->$name;
							$temp = $temp[$index];
							
						} else {
							
							$temp = $temp->$value;
						}
					}
					
					$field = $path[count($path)-1];
					$issue = $detail->issue;
					if (strtolower($field) == "number") $issue = "Credit card number is invalid";
					
					$info[] = array('type' => 'validation_error', 'value' => $temp, 'issue' => $issue, 'message' => $issue, 'field' => $field); //this will give us the offending value and its key
					$this->_errors = $info;
					
				}
			break;
			
			case 'malformed_request':
				
				$this->_errors = array('type' => 'malformed_request',
						'issue' => $return->message,
						'message' => "There appears to be a problem in the programming.");
				
			break;
			
			default:
				die("Please add this to the error list: ".$return->name);
			break;
				
		}
		
		return $this->_errors;	
		
	}
	
	protected function createPostData($params) {
		
		//this supports only one transaction and one credit card
		$intent = isset($params['PAYMENTACTION']) ? strtolower($params['PAYMENTACTION']) : 'sale';
		
		//let's build it unlike Obama
		$return = array();
		$return['intent'] = $intent;
		$payer = &$return['payer'];
		$payer['payment_method'] = "credit_card";
		$funds = &$payer['funding_instruments'];
		$funds = array();
		$funds[]['credit_card'] = array(
			'number' => $params[CREDIT_CARD_NUMBER],
			'type' => ($params[CC_TYPE]) ? strtolower($params[CC_TYPE]) : 'visa',
			'expire_month' => $params[EXPMONTH],
			'expire_year' => $params[EXPYEAR],
			'cvv2' => $params[CVV2],
			'first_name' => $params[FIRST_NAME],
			'last_name' => $params[LAST_NAME],
		);
		
		//now do the transaction
		$return['transactions'] = array();
		$trans = &$return['transactions'];
		$trans[] = array(
			'amount' => array('total' => $params['AMT'], 'currency' => 'USD'),
			'description' => "This is the item description"
		);
//		print(json_encode($return));exit;
		return json_encode($return);
			
	}
	
	protected function valid_cc( $identifier ) {

		$sum = 0; $alt = false; $i = strlen($identifier)-1; $num;

		if (strlen($identifier) < 13 || strlen($identifier) > 19){
			return false;
		}
	
		while ($i >= 0){
	
			//get the next digit
			$num = intval($identifier{$i}, 10);
	
			//if it's not a valid number, abort
			if (!is_int($num)){
				return false;
			}
	
			//if it's an alternate number...
			if ($alt) {
				$num = $num * 2;
				if ($num > 9){
					$num = ($num % 10) + 1;
				}
			} 
	
			//flip the alternate bit
			$alt = !$alt;
	
			//add to the rest of the sum
			$sum = $sum + $num;
	
			//go to next digit
			$i--;
		}
	
		//determine if it's valid
		return ($sum % 10 == 0);
		
	}

   /**
    * Make API request
    *
    * @param string $method string API method to request
    * @param array $params Additional request parameters
    * @return array / boolean Response array / boolean false on failure
    */
   public function request($method,$params = null) {
      $this -> _errors = array();
	  if ($params == null) $params = $this->postParams;
      if( empty($method) ) { //Check if API method is not empty
         $this -> _errors = array('API method is missing');
         return false;
      }

      //Our request parameters. We need to make it json-ified.
	 $params = $this->createPostData($params);

      //Building our NVP string
	  $this->postParams=$params;

      //cURL settings
	  $url = Paypal::_endPoint . '/payments/payment';
	
      $curlOptions = array (
         CURLOPT_URL => $url,
		 CURLOPT_HTTPHEADER => array(
		 	"Content-Type: application/json",
			"Authorization: " . $this->_tokentype . " " . $this->_token
		 ),
		 CURLOPT_HEADER => false,
         CURLOPT_VERBOSE => 1,
         CURLOPT_SSL_VERIFYPEER => false,
         CURLOPT_SSL_VERIFYHOST => false,
         CURLOPT_RETURNTRANSFER => 1,
         CURLOPT_POST => 1,
         CURLOPT_POSTFIELDS => $params
      );

      $ch = curl_init();
      curl_setopt_array($ch,$curlOptions);

      //Sending our request - $response will hold the API response
      $response = curl_exec($ch);

      //Checking for cURL errors
      if (curl_errno($ch)) {
         $this -> _errors = curl_error($ch);
         curl_close($ch);
         return false;
         //Handle errors
      } else  {
         curl_close($ch);
		 $this->response[]=json_decode($response);
         return json_decode($response);
      }
   }
   
   
   public function validate_data($d = array()) { //@return Resource ID indicating type of error
   
		if (isset($d[FIRST_NAME])) {
			if (strlen($d[FIRST_NAME]) < 2) return 'invfname';
		} else return 'invfname';
		if (isset($d[LAST_NAME])) {
			if (strlen($d[LAST_NAME]) < 2) return 'invlname';	
		} else return 'invlname';
		if (isset($d[CREDIT_CARD_NUMBER])) {
			if (!$this->valid_cc($d[CREDIT_CARD_NUMBER])) return 'invcc';
		} else return 'invcc';
		if (isset($d[CVV2])) {
			if (strlen($d[CVV2]) < 2 or strlen($d[CVV2]) > 4) return 'invcvc';	
		} else return 'invcvc';
		if (isset($d[BILLING_ADD1])) {
			if (strlen($d[BILLING_ADD1]) < 5) return 'invbadd1';	
		} else return 'invbadd1';
		if (isset($d[BILLING_STATE]) and isset(Fn()->resources->arrays->states->value)) {
			if (!array_key_exists($d[BILLING_STATE], Fn()->resources->arrays->states->value)) return 'invstate';
		} else return 'invbadd1';
		if (isset($d[BILLING_CITY])) {
			if (strlen($d[BILLING_CITY]) < 3) return 'invcity';	
		} else return 'invcity';
		if (isset($d[BILLING_ZIP])) {
			if (strlen($d[BILLING_ZIP]) < 5 or strlen($d[BILLING_ZIP]) > 11) return 'invzip';	
		} else return 'invzip';
		if (isset($d[EXPYEAR]) and isset($d[EXPMONTH])) {
			$time = strtotime( $d[EXPMONTH] . "/" . $d[EXPYEAR] );	
			if ($time < strtotime(date('m Y'))) return "expired";
		} else return 'invexp';
		
		return true;
		   
   }
   
   public function get_errors() {
		return $this->_errors;   
   }
   
}

?>