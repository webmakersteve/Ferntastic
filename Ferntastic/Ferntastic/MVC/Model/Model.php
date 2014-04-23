<?php

namespace Ferntastic\MVC\Model;

use Ferntastic\Files\Includer;
use Ferntastic\Formatting\Strings as Formatter;

/**
 * Models deal with recieving data from external sources
 *
 * Some of these external sources may be databases, APIs, files, etc.
 *
 * The model itself has no logic other than slight formatting but is a way
 * to separate DATA from the part of the program that deals with the controlling of that data.
 *
 * The model passes its data to the controller which then controls which parts of that data are useful,
 * to an extent, and passes the new data to a view which deals with displaying that data
 * Not all controllers use models - some may simply be used to shift between various different views
 * But as sites are often database drive, models will be used for most pages
 */
 
class Model {
	protected $dataSource = 'database';
	
	protected function setTransient( $data ) {
		return true;	
	}
	
	function __construct() {
		Fn()->load_extension( 'fquery' );
		return $this;
	}
	
	final public static function findModel( $name ) {
		
		foreach( array($name, ucwords($name), strtolower($name), strtoupper($name)) as $topLevelRequest ) {
			$continue = true;
			if (Includer::safeInclude( Fn()->getApplication()->getModulePath() . DS . 'Models' . DS . $x = $topLevelRequest.'.php')) {
			} elseif (Includer::safeInclude( Fn()->getApplication()->getModulePath() . DS . 'Models' . DS . $x = Formatter::removeTrailingS($topLevelRequest).'.php' )) { 
			} else $continue = false;
			
			if ($continue) {
				//get rid of extensions
				$class = str_replace(".php", "", $x);
				$class1 = "\Module\Models\\".$class;
				$class2 = "\Module\Models\\".ucwords($class);
				if (class_exists( $class1, false )) {
					return new $class1;
				} elseif (class_exists( $class2, false )) {
					return new $class2;
				}
			}
		} //endfirstforeach
		return false;
	}
}