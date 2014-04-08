<?php

class App extends Model {
	
	function find( $str ) {
		$apps = new stdClass();
		if (!is_dir( __APPS)) return false;
		$configFiles = glob( __APPS . DS . '*'.DS.'*.json' );
		if (count($configFiles) < 1) {
			return 0;	
		} else {
			//this means there are indeed apps that may be properly configured. Thank you glob!
			foreach( $configFiles as $appConfig ) {
				//let us try to get the contents
				$fileContents = file_get_contents( $appConfig ); //okay we got it
				if ($json = json_decode( $fileContents )) {
					//we also need the path it was found in
					$appPath = dirname( $appConfig );
					//and a unique identifier, possibly the slug?
					if (isset($json->slug) and !empty($json->slug)) $uniqueIdentifier = $json->slug;
					else $uniqueIdentifier = basename($appPath);
					@$apps->$uniqueIdentifier->config = $json;
					
					$apps->$uniqueIdentifier->path = $appPath;
				} else {
					//malformed config. Possibly log an error? @todo
					trigger_error( 'Malformed json in '.$appConfig.' at line ' . __LINE__ . ' in file ' . __FILE__ );	
				} //not proper json
			} //end the foreach loop
			//now regard the parameter in the beginning
			if ( $str != 'all' ) {
				if (isset($apps->$str)) return $apps->$str;
				else return false;
			} else return $apps;
		}
		
	}
		
}