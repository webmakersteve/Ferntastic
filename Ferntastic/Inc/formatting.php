<?php
/**
 * Dummy text
 *
 * @package 91ferns
 */

class Formatter {
	private static $validators = array();
	
	public static function Email( $string ) {
		return filter_var($string, FILTER_VALIDATE_EMAIL );
	}
	
	public static function addValidator( $DataType, $Closure ) {
		if (!is_callable($Closure)) return;
		self::$validators[strtolower($DataType)][] = $Closure;
	}
	
	public static function is( $type, $s ) {
		self::executeValidators($type, $s);
	}
	
	private static function executeValidators( $DataType, $string ) {
		$v = self::$validators[strtolower($DataType)];
		if (count($v) > 0) {
			foreach	($v as $Validator ) {
				if (!is_callable($Validator)) continue; //should never be but we do a check anyway
				$return = call_user_func( $Validator, $string );	
				return $return;
			}
		}
		
	}

	static function backslashit( $string ) {
		$string = preg_replace('/^([0-9])/', '\\\\\\\\\1', $string);
		$string = preg_replace('/([a-z])/i', '\\\\\1', $string);
		return $string;	
	}
	
	static function limit_words( $string, $words ) {
		
		//first we are going to explode the string
		
		
	}
	
	static function maybe_prefix( $string, $prefix ) {
		
		//first check if there is a prefix
		if (preg_match( "#^".$prefix."#", $string )) return $string;
		else return $prefix.$string;
			
	}
	
	static function remove_extension( $string ) {
		
		//just replace it with whitespace
		return preg_replace( "#[.][^.]+$#i", "", $string );
		
	}

}

Formatter::addValidator('email', function($s) {if (Formatter::is('email', $s)) return true; else return false; });
Formatter::addValidator('password', function($s) {
	if (strlen($s) < 4 || strstr($s, " ") || empty($s)) return false;
	return true;
});