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

/*
 * [Error Reporting]
 * You may want to turn off in production environments
 **/
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

/**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

$site_path = realpath(dirname(__FILE__));
define ('__SITE_PATH', $site_path);

/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 *
 */
if (!defined('__ROOT')) {
	define( '__ROOT', dirname(dirname(dirname(__FILE__))) );
}

/**
 * The full path to the data directory
 *
 */
if (!defined('__DATA_PATH')) {
	define( '__DATA_PATH', ROOT . DS . 'data' );
}

/**
 * The full path to the webroot, which is accessed by the browser.
 *
 */
 
if (!defined('__WWW_ROOT')) {
	define('__WWW_ROOT', (dirname(__FILE__)));	
}

if (!defined('__WEBROOT_DIR')) {
	define('__WEBROOT_DIR', basename(dirname(__FILE__)));	
}

/** 
 * Service Directories 
 * INCLUDES = directory where php files are located for required scripts
 * APPS = directory where app data is located
 */

if (!defined('__INCLUDES'))
	define( '__INCLUDES', __ROOT . DS . 'ferntastic'  );
	
if (!defined('__APPS'))
	define( '__APPS', __ROOT . DS . 'apps' );
	
if (!defined('__MODELS'))
	define( '__MODELS', __INCLUDES . DS . 'mvc' );

/** Loads the Environment and Template */

if (!defined('CORE_INCLUDE_PATH')) {
	if (function_exists('ini_set')) {
		ini_set( 'include_path',  __INCLUDES . PATH_SEPARATOR . ini_get('include_path'));
	}
	if (!include ( 'header.php')) {
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

Fn::load_extension('Dispatcher');
$Dispatcher = new Dispatcher();
$Dispatcher->dispatch(
	new SiteRequest(),
	null
);