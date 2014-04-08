<?php

/**
 * Load the environment and the template
 *
 * @package 91ferns
 */
 
if ( !defined('FERNS_IS_LOADED') and FERNS_IS_INIT ) { //this variable is set in load.php to ensure this file is not run twice

	define( 'FERNS_IS_LOADED', true);
	
	define( 'RELPATH', dirname(__FILE__) );
	require_once( RELPATH . '/load.php' ); //this will begin the process of loading the environment
//	require_once( RELPATH . '/load_theme.php' ); //this will begin the process of loading the template @todo serious problems

}