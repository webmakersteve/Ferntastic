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

require_once( INC . "formatting.php" );
require_once( INC . "functions.php" );
require_once( INC . "actions.php" );
require_once( INC . "filters.php" );
require_once( INC . "user.php" );
require_once( INC . "app.php" );
require_once( INC . "plugin.php" );
require_once( INC . "deprecated.php" );
require_once( INC . "settings.php" );
require_once( INC . 'model.php' );
require_once( INC . 'view.php' );
require_once( INC . 'controller.php' );

register_shutdown_function( 'actions_shutdown' );

/** Configuration Settings for Functions.php **/
define('RESOURCE_FP',  ROOT . DS . 'data' . DS . 'resources');

require_once( FRAMEWORKS . "functions.php" );

require_once( INC . 'helpers.php' );

function __autoload($class) {
    //go through the motions to try to find the class @todo
	if (!class_exists( $class, false )) {
		
		if (file_exists( 'class.' . $class . '.php')) include 'class.' . $class . '.php'; if (class_exists( $class, false )) return; //stop if we have found it
		
		Fn()->load_extension( $class ); if (class_exists( $class, false)) return; //check the FN Framework
		Controller::findController( $class ); if (class_exists( $class, false)) return; //now look in the classes
		Model::findModel( $class, true ); if (class_exists( $class, false)) return; //now look in the models
		
	}
}

Fn()->load_extension( 'request', 'dispatcher', 'response', 'router' );

###############################################################
####                                                       ####
####             BEGIN LOADING FULL APP SUITE              ####
####                                                       ####
###############################################################

if ( defined( "APILOAD") and APILOAD ) return false;