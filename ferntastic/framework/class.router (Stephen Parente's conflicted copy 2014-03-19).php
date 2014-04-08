<?php

if (!function_exists('Fn')) exit;

class Router {
	
	private static $request, $methodArgs;
	
	public static function getMethodArgs() {
		return self::$methodArgs;	
	}
	
	private static function convertRouteToRegex( $identifier ) {
		
		//basically all we do is convert %[0-9]* into [^/]+
		$new = preg_replace( "#%([0-9]*)#", "(?P<arg_$1>[^/]+)", $identifier);
		
		return $new;
			
	}
	
	private static function passThroughRoutes( $input ) {
		
		$copy = implode('/', $input);
		$return = $copy;
		foreach (self::$routes as $identifier => $conversion ) {
			
			$regex = self::convertRouteToRegex( $identifier );
			$regex = "#^".$regex."#i";
			if (preg_match( $regex, $copy, $matches )) {
				while (preg_match("#(?P<full>%(?P<num>[0-9]*))#", $conversion, $matches2 )) {
					if (!isset($matches['arg_'.$matches2['num']])) $conversion = str_replace( $matches2['full'], 'default', $conversion );
					else $conversion = str_replace( $matches2['full'], $matches['arg_'.$matches2['num']], $conversion );
				}
				$return = $conversion;
				break;
			}
		}
		
		return split("/", $return);
			
	}
	
	public static function getRoutes ( SiteRequest $request ) {
		
		//hostname is prefix. Go through that first
		
		self::$request = $request;
		
		$return = array();
		
		//first do hostname array if it exists. Returns empty array if not
		$return = array_merge($return, self::convertHostnameToRoutes());
		$requestData = $request->getData();
		$next = array();
		
		
		
		if (!$requestData) {
			$next[] = 'default';
			$next[] = 'index';
		} else {
			$next[] = $requestData[0];
			$next[] = isset($requestData[1]) ? $requestData[1] : 'index';
		}
		$return = array_merge( $return, $next );
		unset($requestData[1], $requestData[0]);
		if (count($requestData) > 0) $return = array_merge( $return, $requestData );
		//get the first two which we know are set
		
		$return = self::passThroughRoutes( $return );
		
		$returnArray = array($return[0], $return[1]);
		
		unset($return[0], $return[1]);
		
		if (count($return) > 0) {
			foreach( $return as $k=>$d ) if (false /*$d == "index" || $d == "default"*/) continue; else self::$methodArgs[] = $d;
		} else {
			self::$methodArgs = array();	
		}
		
		return $returnArray;
		
	}
	
	private static $routes, $hostnames;
	
	private static function addRoute( $key, $value ) { 
		self::$routes[$key] = $value;
	}
	
	private static function addHostname( $key, $value ) {
		self::$hostnames[$key] = split( '/', $value );	
	}
	
	public static function addHostnames( $routesArray ) {
		if (!is_array($routesArray) or empty($routesArray)) return;
		foreach( $routesArray as $k => $argument ) {
			self::addHostname($k, $argument);
		}	
	}
	
	public static function addRoutes( $routesArray ) {
		if (!is_array($routesArray) or empty($routesArray)) return;
		foreach( $routesArray as $k => $argument ) {
			self::addRoute($k, $argument);
		}	
			
	}
	
	private static function convertHostnameToRoutes() {
		$host = self::$request->getHostname();
		if ( isset(self::$hostnames[$host]) ) {
			return self::$hostnames[$host];	
		} else return array();
	}
	
}