<?php

/**
 * This file contains data for the configuration of the suite.
 * E.g. database data, resource data.
 * It then extrapolates it to variables and functions to be used later down the line
 *
 * @package 91ferns
 */


date_default_timezone_set('America/New_York');


//Now we need to get the FernPath, i.e. directory with the resources and fQuery
define( "__FERNTASTIC", __LIB . "ferntastic/" );
define( "__INC", __LIB . "includes" . DS );

require( "settings.php" );