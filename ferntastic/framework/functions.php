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
 
 
define('EXTPATH', dirname(__FILE__));

$time = explode(' ', microtime());
define('STARTTIME', $time[1] + $time[0]);

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
		
	}
	
	
	function timelength() {
		
		$time=microtime();
		$time = explode(' ',$time);
		$time= $time[1]+$time[0];
		
		return $time-$this->start;
		
			
	}
	
	private static $extensions= array(
		'paypal' => "class.paypal.php",
		'resources' => "class.resources.php",
		'errors' => "class.errors.php",
		'database' => "mysql.php",
		'dispatcher' => 'class.dispatcher.php',
		'request' => 'class.request.php',
		'response' => 'class.response.php',
		'sites' => "class.site.php",
		'CMS' => "class.cms.php",
		'mysql' => 'module.mysql.php',
		'twitter' => "class.twitter.php",
		'fquery' => 'class.fquery.php',
		'config' => 'class.config.php',
		'html' => 'module.html.php',
		'log', 'module.log.php',
		'account' => 'class.user.php',
		'checks' => 'module.checks.php',
		'http' => 'module.http.php',
		'messaging' => 'module.messaging.php',
		'nonce' => 'module.nonce.php',
		'posts' => 'module.posts.php',
		'strings' => 'module.strings.php',
		'cart' => 'class.cart.php',
		'request' => 'class.request.php',
		'usps' => 'class.usps.php',
		'ups' => 'class.ups.php',
		'deprecated' => "deprecated.legacy.php",
		'nonce' => 'class.nonce.php',
		'registry' => 'class.registry.php',
		'cache' => 'class.cache.php',
		'users' => 'class.user.php',
		'user' => 'class.user.php',
		'identity' => 'class.user.php',
		'fernidentity' => 'class.user.php',
		'router' => 'class.router.php'
	);
	
	/**
	 * Revised include function. Only includes if the file exists and throws a manageable error if something goes wrong.
	 * Only loads one, and Works on a priority basis. First, looks to see if there's a listed directory in the entered parameter.
	 * Then will try to load it in the PHP folder
	 * If it isn't there it will try to cross reference it with the extensions array
	 * If it isn't there, it will throw an Inclusion Exception
	 *
	 * @param $filename The name of the file to be loaded. 
	 * @return Returns false on failure and the filepath of inclusion on success
	 */
	
	private static function load( $filename=null ) {
		
		if ($filename==null) return false;
		
		$filename = (string) $filename;
		
		if (preg_match("#([a-z][:])?\\".DIRECTORY_SEPARATOR."{1,2}([a-z]+\\".DIRECTORY_SEPARATOR."?)[a-z][.].{2,5}#i", $filename)) {
			//this means it looks like a full directory
			
			if (file_exists( $filename )) {
				include_once( $filename );
				return $filename;
			}
				
		}
		
		//if it isn't a full directory check the EXT folder if its 
		if (preg_match( "#^[/\\".DIRECTORY_SEPARATOR."]#i", $filename )) {
			
			//add the EXT path to it
			if (file_exists(EXTPATH . "" . DIRECTORY_SEPARATOR . $filename) ) {
				$ld = EXTPATH . "" . DIRECTORY_SEPARATOR . $filename;
				include_once( $ld );
				return $ld;
			}
				
		}
		
		//now check if it is in the array EXTENSIONS
		if (array_key_exists($filename, self::$extensions)) {
			
			if (file_exists( EXTPATH . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . self::$extensions[$filename] )) {
				$ld = EXTPATH . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . self::$extensions[$filename];
				include_once( $ld );
				return $ld;
			}
			
			if (file_exists( EXTPATH . "" . DIRECTORY_SEPARATOR . self::$extensions[$filename])) {
				$ld = EXTPATH . "" . DIRECTORY_SEPARATOR . self::$extensions[$filename];
				include_once ( $ld );
				return $ld;
			}
			
		}
		
		
		//now check if its in the PHP Extension folder
		
		if (file_exists( EXTPATH . "" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . $filename)) {
			$ld = EXTPATH . DIRECTORY_SEPARATOR ."php" . DIRECTORY_SEPARATOR . $filename;
			include_once($ld);
			return $ld;
		}
		
		//Check includes
		
		if (file_exists( EXTPATH . "" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . $filename)) {
			$ld = EXTPATH . DIRECTORY_SEPARATOR ."includes" . DIRECTORY_SEPARATOR . $filename;
			include_once($ld);
			return $ld;
		}
		
		//last resort. just try to include the file with the basename of the executing script
		
		if ( file_exists( dirname( $_SERVER['SCRIPT_NAME'] ) . DIRECTORY_SEPARATOR . $filename ) ) {
			$ld = $_SERVER['SCRIPT_NAME'] . "" . DIRECTORY_SEPARATOR . $filename;
			include_once ( $ld );
			return $ld;
				
		}
		
		//sorry charlie,
		if (class_exists('InclusionError')) throw new InclusionError( 'incerror', array('file' => $filename) );
		else throw new Exception($filename, 0);
		return false;
		
	}
	
	private static $loaded = array();
	
	/**
	 * This function loads an extension in the 91ferns library. The extensions are in an array to prevent bad data.
	 *
	 * @param $extname The name of the extension to load. This is a mixed list. This can load many extensions or even SETS of extensions
	 * @return boolean Returns false on failure or true on success
	 *
	 */
	 
	public function is_loaded( $x ) {
		
		if ( in_array($x, self::$loaded)) {return true;} else return false;
		
	}
	
	public static function load_extension( /** mixed list **/ ) {
		
		$loaded_extensions=self::$loaded;;
		$arguments = array();
		
		if (func_num_args() > 0): //if there are arguments
		
			$arr = array();
			foreach (func_get_args() as $arg) {
				$arg = strtolower($arg);
				if (is_array($arg)) $arr = array_merge($arr, $arg);
				else $arr[] = $arg;
			}
			//all of the arguments are now in the $arr variable
			
			foreach ($arr as $filepath) {
				
				if (!in_array( $filepath, $loaded_extensions )) {
					try {
						
						if ( $ld = self::load( $filepath ) ) $loaded_extensions[] = $ld;
						
					} catch (NoLogError $e) {$e->handleMe();}
				}
			}
		
		else: //no arguments
		
			return;
			
		endif;
		
	}
		
	/**
	 * This function loads an extension in the 91ferns library. The extensions are in an array to prevent bad data.
	 */
		
	private $settings = array();
		
	/**
	 * Set function defines a setting.
	 * This can be used later within the function context
	 * @return boolean Returns false on failure or true on success
	 *
	 */
		
	public function set( $key, $value ) {
	
		$this->settings[$key]=$value;
		return true;
		
	}
	
	/**
	 * Get function gets value of a setting.
	 * This can be used later within the function context
	 * @return boolean Returns false on failure or true on success
	 *
	 */
	
	public function get( $key ) {
		
		return isset($this->settings[$key]) ? $this->settings[$key] : false;
			
	}
	
	
}


if (!defined('ABSPATH')) define('ABSPATH', dirname( __FILE__ ) );

/**
 * Extension path is defined by the parent of this running script
 */
 
if (!defined('EXTPATH')) define('EXTPATH', dirname( dirname ( __FILE__ ) ) );
function Fn() {return Fn::Invoke();}

//now we can load the necessary modules
Fn()->load_extension('errors', 'mysql'); //first and foremost, we need to include the error library resource framework for errors and the mysql framework for DB connection.

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

/**
 * Set HTTP status header.
 *
 * @param int $header HTTP status code
 * @return unknown
 */
function status_header( $header ) {
	$text = get_status_header_desc( $header );

	if ( empty( $text ) )
				return false;

		$protocol = $_SERVER["SERVER_PROTOCOL"];
		if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
				$protocol = 'HTTP/1.0';
		$status_header = "$protocol $header $text";
		
		return @header( $status_header, true, $header );
}

function redirect($location, $status = 302) {

	if ( !$location ) // allows the wp_redirect filter to cancel a redirect
		return false;
	$location = sanitize_redirect($location);
	if ( !$is_IIS && php_sapi_name() != 'cgi-fcgi' )
	status_header($status); // This causes problems on IIS and some FastCGI setups
	
	header("Location: $location", true, $status);
}

function sanitize_redirect($location) {
	$location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!]|i', '', $location);
	return $location;
}

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
Fn()->load_extension('resources', 'mysql','deprecated', 'config');