<?php

namespace MVC\Common;

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
	
	public static function findModel( $name ) {
		foreach( array($name, ucwords($name), strtolower($name), strtoupper($name)) as $topLevelRequest ) {
			$continue = true;
			if (safe_include( __MODELS . DS . 'models' . DS . 'model.'.remove_trailing_s($topLevelRequest).'.php')) {
			} elseif (safe_include( __MODELS . DS . 'models' . DS . remove_trailing_s($topLevelRequest).'.php' )) {
			} elseif (safe_include( __MODELS . DS . 'models' . DS . 'model.' . $topLevelRequest.'.php' )) {
			} elseif (safe_include( __MODELS . DS . 'models' . DS . $topLevelRequest.'.php' )) {}
			else $continue = false;
			if (true) { //tthis is kinda stupid because it quits if it didnt find the file. lets not do that since we have safetys in place anyway
				$suffix = 'Model';
				
				$enter = array( ucwords($topLevelRequest), $topLevelRequest );
				if (remove_trailing_s( $topLevelRequest ) !== $topLevelRequest ) {
					$enter[] = remove_trailing_s(ucwords($topLevelRequest));
					$enter[] = remove_trailing_s($topLevelRequest);
				}
				foreach( $enter as $modelClass ) {
					if (_Class::isClass( $modelClass ) && _Class::isChildOf( $modelClass, 'Model' ))
						return new $modelClass; //return new $modelClass;
					 elseif (_Class::isClass( $new = $modelClass.ucwords($suffix) ) && _Class::isChildOf( $new, 'Model' ))
						return new $new;
					elseif (_Class::isClass( $new = ucwords($suffix).$modelClass ) && _Class::isChildOf( $new, 'Model' ) )
						return new $new;
					elseif (_Class::isClass( $new = strtolower($suffix).$modelClass ) && _Class::isChildOf( $new, 'Model' ) ) 
						return new $new;
				} //endforeach
			} // end continue
			//continue;
		}
		return false;
	}
}