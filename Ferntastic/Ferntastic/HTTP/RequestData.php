<?php

namespace Ferntastic\HTTP;

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