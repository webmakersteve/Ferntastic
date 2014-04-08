<?php

define('__FRAMEWORK_MVC',  dirname(__FILE__) . DS );

spl_autoload_register(function( $ClassName ) {
	$nss = '\\';
	$ns = 'MVC';
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
		echo $path . "\n\n";
		require $path;
		if (class_exists($ClassName)) return true;
	}
	
});