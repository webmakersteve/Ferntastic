<?php

/**
 * Load the environment and the template
 *
 * @package 91ferns
 */
 
 /*
 * [Error Reporting]
 * You may want to turn off in production environments
 **/
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

 
if ( !defined('FERNS_IS_LOADED') and FERNS_IS_INIT ) { //this variable is set in load.php to ensure this file is not run twice

	define( 'FERNS_IS_LOADED', true);
	
	require_once( __LIB . 'load.php' ); //this will begin the process of loading the environment
//	require_once( RELPATH . '/load_theme.php' ); //this will begin the process of loading the template @todo serious problems

}