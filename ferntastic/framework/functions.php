<?php

/**
 * Standard Functions file. This file controls the fetching of other PHP files
 * and the declaration of the constants and GLOBALS that will be used later.
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.5
 *
 */

class Fn {
	
	private static $ext = array();
	
	public function __call( $method, $args ) {
		
		if ( isset(self::$ext[$method]) ) {
			$x = self::$ext[$method];
			if (is_callable($x)) {
				return call_user_func( $x, $args );	
			} else return $x;
		} else return false;
					
	}
	
	private static $extra_data = array();
	
	function __get( $name ) {
		
		if ( isset(self::$ext[$name]) ) {
			$x = self::$ext[$name];
			if (is_callable($x)) return false;
			else return $x;
		} elseif (isset(self::$extra_data[$name])) {
			return self::$extra_data[$name];
		} else return false;
			
	}
	
	function __set( $name, $value ) {
		self::$extra_data[$name]=$value;
	}
	
	public static function add( $id, $function ) {
		
		self::$ext[$id]=$function;
		return true;
		
	}
	
	private $start;
	
	private static $instance = NULL;
	public static function Invoke() {
		if (self::$instance === NULL) self::$instance = new Fn();
		return self::$instance;
	}
	
	private $GLOB = '';
	
	private $extensions = array(
		'datasources',
		'drivers',
		'classes',
	);
	
	public function safeInclude( $file ) {
		require_once( $file );	
	}
	
	const INC_DRIVER_SYNTAX = "#driver[.](?P<type>[^.]+)[.](?P<method>[^.]+)[.]php#i";
	const INC_SCHEMA_SYNTAX = "#schema[.](?P<type>[^.]+)[.]php#i";
	const INC_CLASS_SYNTAX = "#class[.](?P<name>[^.]+)[.]php#";
	const INC_DATASRC_SYNTAX = "#source[.](?P<name>[^.]+)[.]php#";
	private function createExtensionsFromGlob( $GlobData ) {
		
		foreach( $GlobData as $Path ) {
			
			if (stristr($Path, "drivers")) {
				//most complicated because
				if (preg_match( self::INC_DRIVER_SYNTAX, $Path, $matches))
					$this->extensions['drivers'][$matches['type']][$matches['method']] = dirname(__FILE__)  . $Path;
				elseif (preg_match( self::INC_SCHEMA_SYNTAX, $Path)) {
					//require it. All schemas need to be required by default
					$this->safeInclude($Path);
				}
				continue;
			}
			
			if (stristr( $Path, 'datasources' )) {
				if (preg_match( self::INC_DATASRC_SYNTAX, $Path, $matches )) {
					$this->extensions['datasources'][$matches['name']] = dirname(__FILE__)  . $Path;
				}
				continue;	
			}
			
			if (stristr( $Path, "class.")) {
				if (preg_match( self::INC_CLASS_SYNTAX, $Path, $matches )) {
					$this->extensions['classes'][$matches['name']] = dirname(__FILE__)  . $Path;
				}
				continue;
			}
				
		}
			
	}
	
	private function __construct() {
		
		//welcome to 91ferns. Log upon construction		
		
		/**
		 * timecheck
		 */
		if (!defined('STARTTIME')) {
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$this->start = $time;
		} else $this->start=STARTTIME;
		
		$path = dirname(__FILE__);
		$arr = array();
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach( $objects as $file) $arr[] = rtrim(str_replace(dirname(__FILE__), "", (string) $file));
		$arr = array_filter( $arr, function($v) {
			if (preg_match("#[.]+$#", $v)) return false;
			if (!stristr($v, ".php")) return false;
			return $v;
		});

		//now we run naming conventions
		$this->createExtensionsFromGlob( $arr );
		
	}
	
	
	function timelength() {
		
		$time=microtime();
		$time = explode(' ',$time);
		$time= $time[1]+$time[0];
		
		return $time-$this->start;
		
			
	}
	
	public function Autoload( $ClassName ) {
		
		//check datasources
		$classes = $this->extensions['classes'];
		
		foreach( $classes as $class => $path ) {
			if (stristr( $ClassName, $class )) {
				$this->safeInclude( $path );
				if (class_exists( $ClassName, false )) return true;	
			}
		}
		
		$drivers = $this->extensions['drivers'];
		
		foreach( $drivers as $type => $grps ) {
			foreach( $grps as $class=>$path) {
				if (stristr( $ClassName, $class )) {
					$this->safeInclude( $path );
					if (class_exists( $ClassName, false )) return true;
				}
			}
		}
		
		$datasources = $this->extensions['datasources'];
		
		foreach( $datasources as $class => $path ) {
			if (stristr( $ClassName, $class )) {
				$this->safeInclude( $path );
				if (class_exists( $ClassName, false )) return true;
			}
		}
		
	}
	
	
	
}

function Fn() {return Fn::Invoke();}

spl_autoload_register( function( $class ) {
	Fn()->AutoLoad( $class );
} );

Fn()->safeInclude( 'class.errors.php' );
$x = new MySQL();

/**
 * Argument Exception class.
 */
 
class ArgumentError extends LogError {
	private $type = __CLASS__;
}

/**
 * Inclusion Exception class.
 */
 
class InclusionError extends LogError {
	private $type = __CLASS__;
}

/** Create smart redirects **/

/** Check if Cookies are on **/

session_start();

if (count($_COOKIE) < 1) {
	
	$a = session_id();
	session_destroy();
	
	session_start();
	$b = session_id();
	
	if ($a==$b) {
		Fn()->cookies=true;
	} else {
		Fn()->cookies=false;
	}

} else Fn()->cookies=true;
//echo (Fn()->cookies?"Cookies are on":"Cookies are off");
//these extensions are loaded by default, but they may be disabled by adding their shortstrings to a GLOBAL NOLOAD