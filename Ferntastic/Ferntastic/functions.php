<?php

/**
 * Standard Functions file. Deals with bootstrapping and service locating
 * and the declaration of the constants and GLOBALS that will be used later.
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.5
 *
 */

class Fn {
	
	private $serviceLocator = NULL;
	
	public function getServiceLocator() {
		//lazy loaded. Check if it exists
		if ($this->serviceLocator === NULL) {
			$this->serviceLocator = new Ferntastic\Service\ServiceLocator();
		}
		return $this->serviceLocator;
	}
	
	private $application;
	public function getApplication() {
		if ($this->application === NULL) $this->application = new Ferntastic\MVC\Common\Application();
		return $this->application;	
	}
	
	public static function registerService( $id, $function ) {
		
		//we have two options. One is a class, the other is not
		if (is_string( $function ))
			if (class_exists( $function, true )) {
				//this means it is a class! yay!
				
				//we need to check what class this thing extends - is it invokable or is it something else?
			} else {
				throw new Exception('Class does not exist: '.$function);
			}
		
		if (is_callable( $function )) {
			//this means it is a function that may in turn create something
			
		}
		
	}
	
	public function getService( $id ) {
		if (array_key_exists( $id, $this->ext )) {
			//lets get some info about it
			$t = $this->ext[ $id ];
			switch ($t['type']) {
				case 'invokable':
				
					break;
				case 'factory':
				
					break;
				case 'callable':
					
					break;	
			}
		} else throw new Exception('Service does not exist');
	}
	
	private $start;
	
	private static $instance = NULL;
	public static function Invoke() {
		if (self::$instance === NULL) self::$instance = new Fn();
		return self::$instance;
	}
	
	private $cookies = false;
	private function __construct() {
		
		//welcome to 91ferns. Log upon construction		
		
		session_start();

		if (count($_COOKIE) < 1) {
			
			$a = session_id();
			session_destroy();
			
			session_start();
			$b = session_id();
			
			if ($a==$b) {
				$this->cookies=true;
			} else {
				$this->cookies=false;
			}
		
		} else $this->cookies=true;
		
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
	
	public function Autoload( $ClassName ) {
		$nss = '\\';
		$ns = 'Ferntastic';
		$inc = dirname(dirname(__FILE__));
		
        if (null === $ns || $ns.$nss === substr($ClassName, 0, strlen($ns.$nss))) {
            $fileName = '';
            $namespace = '';
            if (false !== ($lastNsPos = strripos($ClassName, $nss))) {
                $namespace = substr($ClassName, 0, $lastNsPos);
                $ClassName = substr($ClassName, $lastNsPos + 1);
                $fileName = str_replace($nss, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $ClassName) . ".php";
			$path = ($inc !== null ? $inc . DIRECTORY_SEPARATOR : '') . $fileName ;
            require $path;
			if (class_exists($ClassName)) return true;
        }
		
	}
	
	
	
}

function Fn() {return Fn::Invoke();}

spl_autoload_register( function( $class ) {
	Fn()->AutoLoad( $class );
} );


/** Create smart redirects **/

/** Check if Cookies are on **/

Fn()->getServiceLocator()->registerService('request', 'Ferntastic\HTTP\Request');
Fn()->getServiceLocator()->registerService('includer', 'Ferntastic\Files\Includer');
Fn()->getServiceLocator()->registerService('strings', 'Ferntastic\Formatting\Strings');
Fn()->getServiceLocator()->registerService('themer', 'Ferntastic\MVC\Helpers\Themer');
Fn()->getServiceLocator()->registerService('resources', function() {
	return Ferntastic\Resources::Invoke();
});