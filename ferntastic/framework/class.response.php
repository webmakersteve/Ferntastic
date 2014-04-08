<?php

class SiteResponse {
	
	private static $statusCodes = array(
		303 => "HTTP/10.3.4 303 See Other",
		401 => "HTTP/10.4.2 401 Unauthorized",
		403 => "HTTP/10.4.4 403 Forbidden",
		404 => "HTTP/10.4.5 404 Not Found",
		405 => "HTTP/10.4.6 405 Method Not Allowed",
	);
	
	public static function redirect( $path, $status=303 ) {
		if (!array_key_exists($status, self::$statusCodes)) $status = 303;
		header(self::$statusCodes[$status]);
		header("Location: ".$path);
		exit;
	}
	
	public static function statusCode( $status = 403 ) {
		if (!array_key_exists($status, self::$statusCodes)) $status = 303;
		header(self::$statusCodes[$status]);	
	}
	
	public static function removeCookie( $name ) {
		setcookie( $name, '', -2000 );
	}
	
	public static function setCookie( ) {
		$defaults = array(
			'name' 		=> "name",
			'value' 	=> "value",
			'expire' 	=> LOGIN_COOKIE_LENGTH,
			'path' 		=> '/',
			'domain'	=> '',
			'secure' 	=> 0,
		);
		if (!func_get_arg(1) or !func_get_arg(0)) {
			//we're in trouble
			return;	
		}
		switch (func_num_args()) {
			case 6:
				if ($arg = func_get_arg(5)) $defaults['secure'] = $arg;
			case 5:
				if ($arg = func_get_arg(4)) $defaults['domain'] = $arg;
			case 4:
				if ($arg = func_get_arg(3)) $defaults['path'] = $arg;
			case 3:
				if ($arg = func_get_arg(2)) $defaults['expire'] = $arg;
			case 2:
				$defaults['value'] = func_get_arg(1);
				$defaults['name'] = func_get_arg(0);
				extract($defaults);
				setcookie( $name, $value, $expire, $path, $domain, $secure );
			default: return false;
				
		}
	}
	
}