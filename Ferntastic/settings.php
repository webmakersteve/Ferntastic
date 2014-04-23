<?php

/**
 * This file sets certain variables and functions to specifications required
 * E.g. shutdown functions, timers, globals, etc.
 * It then extrapolates it to variables and functions to be used later down the line
 * 
 * This file will also load the majority of the framework
 *
 * @package 91ferns
 */

###############################################################
####                                                       ####
####            BEGIN LOADING SHORT APP SUITE              ####
####                                                       ####
###############################################################

/* Now start the baseline requirements */

define('__INCLUDES', getcwd() . DS . 'Ferntastic' . DS . 'Inc' . DS );

require_once( __INCLUDES . "formatting.php" );
require_once( __INCLUDES . "functions.php" );
require_once( __INCLUDES . "actions.php" );
require_once( __INCLUDES . "filters.php" );
require_once( __INCLUDES . "user.php" );
require_once( __INCLUDES . "app.php" );
require_once( __INCLUDES . "plugin.php" );
require_once( __INCLUDES . "deprecated.php" );

register_shutdown_function( 'actions_shutdown' );

require_once( "Ferntastic" . DS . "functions.php" );

###############################################################
####                                                       ####
####             BEGIN LOADING FULL APP SUITE              ####
####                                                       ####
###############################################################

if (php_sapi_name() == 'cli' or PHP_SAPI == 'cli' || defined('__API_LOADING')) {
	
} else require( 'init.php' );