<?php
/**
 * This file will load the environment to get started with the suite.
 * All functions in here or loaded by this file are for accessing data, not themes.
 * This allows apis and frameworks to be written without HTML in mind
 *
 * @package 91ferns
 */
 
 /**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}


error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

//now we must load the config file

require_once( 'config.php' ); 