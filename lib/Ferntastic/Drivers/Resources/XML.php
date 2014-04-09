<?php

namespace Ferntastic\Drivers\Resources;

use Ferntastic\Drivers\Common\Driver;
use Ferntastic\Drivers\Resources\Schema\Driver as ResourceDriver;
use Ferntastic\Errors\ResourceError;

class XML extends Driver implements ResourceDriver {
	
	protected static $instance = NULL;
	protected function __construct() {
		return $this;
	}
	
	public static function Invoke() {
		if (self::$instance === NULL) self::$instance = new self();
		return self::$instance;
	}	
	
	public function LoadResources( $Directory ) {
		$this->lastDirectory = $Directory;
//		error_reporting(E_ALL);
	//	ini_set('display_errors', '1');
		if (!is_dir( $Directory) ) throw new ResourceError(ERROR_RESOURCE_NO_DIRECTORY, array('data' => $Directory));
		if (is_readable( $Directory ) && $dir = opendir($Directory)) {
			$_resources = array();
			while (($file = readdir($dir)) !== false) {
				if ( !preg_match("#[.]xml$#i", $file) ) {
					//these are for non xml files
				} else {
					
					//this gives us the new dirs
					//now we need to find the widget with the identifying 
					//now we have the data inside the widgets folder
					
					//load the xml sheet
					$xmlfp = $Directory . DS . $file;
					if (file_exists($xmlfp)) {
			
						$contents = file_get_contents($xmlfp);
						if (!$contents) continue;
						$doc = new \SimpleXMLElement($contents);
						
						$catname = preg_replace('#[.]xml$#i', '', basename($xmlfp));
						$t = &$_resources[$catname];
						$atts = &$_resources['attr'][$catname];
						
						foreach ($doc->children() as $k => $child) {
							
							if ($k == "array") {
								
								$name = (string) $child->attributes()->name;
								$attr = (array) $child->attributes();
								
								//we need to find what type it is to get the next tags
								@$type = $attr['type'];
								$type = $attr['@attributes']['type'];
								
								$value = array();
								
								foreach ($child->$type as $subchild):
									$k = (string) $subchild->attributes()->key;
									$value[$k] = (string) $subchild;
								endforeach;
								
								$attributes = (object) $attr['@attributes'];
								$resValue = $value;
								
							} else {
								
								$name = (string) $child->attributes()->name;
								$attr = (array) $child->attributes();
								$value = (string) $child;
								
								$attributes = (object) $attr['@attributes'];
								$resValue = (string) $value;
								
							}
							
							$t->$name = $resValue;
							//supressing warning because I know it can be blank. But the check is then empty instead of isset which is better
							@$atts->$name = (object) $attributes;
							
						}
						
					} else throw new ResourceError(ERROR_RESOURCES_DIR_NO_READ, array('data' => $xmlfp));
						
				}//end checking the folders
				
			} //end reading the directory
			
			if ($dir) closedir($dir);
			return $_resources; //we want to use a token of the key to do this so we can reference the specific file later, too
		} else throw new ResourceError(ERROR_RESOURCES_DIR_NO_READ); //end opening the directory

	}
		
}