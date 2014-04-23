<?php

/**
 * This is the first presented page to the user who accesses the web app suite system.
 * After loading this we need to make sure to load the header file, which is located in the cgi directory
 * That file will begin loading the theme and the theme functions
 *
 * @package 91ferns
 */

/**
 * We just want to tell people that this file has been run
 *
 * @var bool
 */
define('FERNS_IS_INIT', true);

$site_path = realpath(dirname(__FILE__));
define ('__PUBLIC', $site_path);

$Framework = dirname(dirname(dirname(__FILE__)));

chdir( $Framework );


/** Loads the Environment and Template */

if (!defined('CORE_INCLUDE_PATH')) {
	if (function_exists('ini_set')) {
		ini_set( 'include_path',  $Framework . PATH_SEPARATOR . ini_get('include_path'));
	}
	if (!include ( 'Ferntastic/header.php')) {
		$failed = true;
	}
} else {
	if (!include (CORE_INCLUDE_PATH . DS . 'lib' . DS . 'header.php')) {
		$failed = true;
	}
}
if (!empty($failed)) {
	trigger_error("There was a problem loading the ferntastic core. Please check your directory structure and possibly try again", E_USER_ERROR);
}