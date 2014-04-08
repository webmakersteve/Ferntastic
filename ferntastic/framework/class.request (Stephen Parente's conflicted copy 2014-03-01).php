<?php

/**
 * class.request.php
 *
 * This file controls the transforming of URL, GET, and Post data into an object to be read. 
 * 
 * In the greater scheme of things, this will then convert to JSON or Object data to be ready by
 * third party apps
 */
 
class SiteRequest {
	
	public static $requestArray = array();
	public function getData() {return self::$requestArray;}
	
	public function getHostname() {
		return $_SERVER['HTTP_HOST'];	
	}
	
	public function getUserAgent() {
			
	}
	
	protected static function getroot() {
		return defined('ROOT') ? ROOT : dirname(dirname(dirname(__FILE__)));
		//this may be loaded with configs, db, or something later
	}
	
	protected $blackList = array('index.php', 'default.php', 'index.html', 'default.html');
	protected $requestMethod;
	function Post() {
		return new RequestData($_POST);
	}
	
	function Cookies() {
		return new RequestData($_COOKIE);	
	}
	
	function __construct() {
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		self::init();
		$REQUEST_URI = trim($_SERVER['REQUEST_URI']);
		if ($REQUEST_URI == "/") return false;
//		$requests = $_REQUEST; nomore
		$rootPaths = explode('/', self::getroot());
		//now we need to merge them
//		$return = array_merge(explode('/', $REQUEST_URI), $requests ); //this puts post on we dont want this anymore
		$return = explode('/', $REQUEST_URI);
		$blackList = $blackList = $this->blackList;
		$return = array_filter( $return, function( $var ) use (&$rootPaths, $blackList) {
			if (empty($var)) return false;
			if (in_array($var, $blackList)) return false;
			//now we need to be careful here. we want to make sure to take each value out only once
			if (in_array( $var, $rootPaths )) {
				//we need to make sure to get rid of it
				unset($rootPaths[array_search( $var, $rootPaths)]);
				return false;
			} return true;
		});
		$oldResponse = $return;
		$return = array();
		foreach ($oldResponse as $v) $return[] = $v;
		
		self::$requestArray = $return;
		return $return;
			
	}
	
	/** Session Stuff. Will make things much cleaner and make two distinctions between sessions
	There are temporary sessions, like form errors and post data, and there are non temporary ones
	like cart data; things that are removed at will not just at the end of the page
	*/
	
	private static $savedSessionData;
	private static $transientTags;
	
	public static function setSessionData( $key, $value, $isTransient=false) {
		self::$savedSessionData[$key] = $value;
		$_SESSION[$key] = self::$savedSessionData[$key];
		
		if ($isTransient===true) {
			self::$transientTags[] = $key;
			$_SESSION['MVC_TRANSIENTS'] = self::$transientTags;
			//check if $tmp worked
		}
	}
	public static function getSessionData( $key ) {
		if (array_key_exists($key, self::$savedSessionData)) {
			return self::$savedSessionData[$key];
		}
		return false;
	}
	public static function killTransients( ) {
		if (count(self::$transientTags) < 1) return;
		foreach( self::$transientTags as $k=>$tag ) {
			if (array_key_exists( $tag, self::$savedSessionData )) {
				//that means it exists. so lets unset the session and remove it from both arrays
				unset($_SESSION[$tag], self::$transientTags[$k], self::$savedSessionData[$tag], $_SESSION['MVC_TRANSIENTS']);
			}
		}
	} //transients are only killed when a page that outputs information is loaded so they still need 
	//dont init more than once
	private static $isInit = false;
	public static function init() {
		if (self::$isInit == true) return;
		self::$isInit = true;
		$transients = isset($_SESSION['MVC_TRANSIENTS']) ? $_SESSION['MVC_TRANSIENTS'] : array();
		//we got our transients back. Yay.
		//now load the other session crap
		if (count($_SESSION) > 0) {
		foreach( $_SESSION as $key => $value ) {
			//check for its key inside the transient array
			if (in_array( $key, $transients )) $isTransient = true;
			else $isTransient = false;
			self::setSessionData( $key, $value, $isTransient, true );	
		}
		} else self::$savedSessionData = array();
	}
	
		
}

class RequestData {
	
	/** Start Request Validation Stuff **/
	
	private $data;
	private static $validators = array();
	
	public static function addValidator( $DataType, $Closure ) {
		if (!is_callable($Closure)) return;
		self::$validators[strtolower($DataType)][] = $Closure;
	}
	
	function __construct($data) {
		foreach( $data as $k=>$v ) {
			$this->data[$k] = $v;	
		}
	}
	
	public function get( $index, $type=NULL ) {
		if (isset($this->data[$index])) $s = $this->data[$index];
		else return false;
		if ($type === NULL) return $s;
		else {
			//we need to format it I guess
			return;	
		}
	}
	public function isFormat( $index, $type, &$error = NULL ) {
		if (isset($this->data[$index])) $s = $this->data[$index];
		else return false;
		if ($error !== NULL) return $this->executeValidators($type, $s, $error);
		else return $this->executeValidators($type, $s);
	}
	
	private function executeValidators( $DataType, $string, &$error=NULL ) {
		$v = self::$validators[strtolower($DataType)];
		if (count($v) > 0) {
			foreach	($v as $Validator ) {
				if (!is_callable($Validator)) continue; //should never be but we do a check anyway
				$return = call_user_func( $Validator, $string );	
				if ($return!==true) {
					if ($error !== NULL) $error = $return;
					return false;
				}
			}
		}
		
		return true; //only true if it gets passed all of them
		
	}
	
	function __get( $index ) {
		if (isset($this->data[$index])) return $this->data[$index];
		else return false;
	}
	
	function __set($a,$b) {
		return;	
	}
	
}
RequestData::addValidator('email', function($s) {if (Formatter::email($s)) return true; else return false; });
RequestData::addValidator('password', function($s) {
	if (empty($s)) return R()->get()->strings->validation_password_not_entered;
	elseif (strlen($s) < 4) return R()->get()->strings->validation_password_too_short;
	elseif (strstr($s, " ")) {
		return R()->get()->strings->validation_password_has_space;
	}
	else return true;
});

Fn::add('Request', new SiteRequest() );