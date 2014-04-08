<?php

namespace Ferntastic\Drivers\Resources;

use Ferntastic\Drivers\Common\Driver;
use Ferntastic\Drivers\Resources\Schema\Driver as ResourceDriver;

class XML extends Driver implements ResourceDriver {
	
	protected static $instance = NULL;
	protected function __construct() {
		return $this;
	}
	
	public static function Invoke() {
		if (self::$instance === NULL) self::$instance = new self();
		return self::$instance;
	}	
	
	public function Get( $key ) {}
	public function Set() {}
	public function toArray() {
		
	}
	public function LoadResources( $Specification ) {
		if (!is_dir( $the_path) ) throw new ResourceError("Couldn't read directory: ".$the_path);
		if ($dir = opendir($the_path)) {
			
			while (($file = readdir($dir)) !== false) {
				if ( !preg_match("#[.]xml$#i", $file) ) {
					//these are for non xml files
				} else {
					
					//this gives us the new dirs
					//now we need to find the widget with the identifying 
					//now we have the data inside the widgets folder
					
					//load the xml sheet
					$xmlfp = $the_path . DIRECTORY_SEPARATOR . $file;
					
					if (file_exists($xmlfp)) {
			
						$contents = file_get_contents($xmlfp);
						if (!$contents) continue;
						$doc = new SimpleXMLElement($contents);
						
						$catname = preg_replace('#[.]xml$#i', '', basename($xmlfp));
						$t = &$tres->$catname;
						$atts = &$tres->attr->$catname;
						
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
						
					} else throw new ResourceError("Couldn't read ".basename($xmlfp));
						
				}//end checking the folders
				
			} //end reading the directory
			
			if ($dir) closedir($dir);
			$this->pathsTried[] = $the_path;
			$this->resources[md5($the_path)] = $tres; //we want to use a token of the key to do this so we can reference the specific file later, too
			
		} else throw new ResourceError("Couldn't Read Resources"); //end opening the directory

	}
		
	public function LoadedTypes() {
		
	} //returns array of loaded types
		
}

/**
 * Resource is a private class that is created when the Resources class interprets the data.
 * Data is accessed through the resource object when it is constructed. Methods are not used often.
 *
 * <code>
 * Resources()->string->name; //string is the MetaCategory of the specific resource.
 * </code>
 *
 * @package resources
 * @version 0.1
 *
 */

class Resource {
	
	public $value;
	public $attr;
	private $attrArr = array();
	
	function __construct( $data ) {
		
		$attrs = $data->attr;
		$this->value = $data->value;
		
		$this->attr = new stdClass();
		
		foreach( $attrs as $k => $v ):
			$a = &$this->attr;
			$this->attrArr[$k]=$v;
			$a->{$k} = !empty($v) ? $v : null;
		
		endforeach;
		
	}
	
	public function Exchange( $Data ) {
			
	}
	
	public function getProp( $key ) {
		
		if (isset($this->attrArr[$key])) return $this->attrArr[$key];
		else return false;
		
	}
	
	function __toString() {
		if (is_array($this->value)) return serialize($this->value);
		return $this->value;
	}

}

class Resources {
	
	private $type;
	
	function __construct( $data ) {
		
		foreach ( $data as $type => $instances ) {
			
			$this->{$type} = new stdClass();
			$ref = &$this->$type;
			foreach ($instances as $k => $obj) {
			
				$ref->$k = new Resource($obj);
			
			}
			
		} //endforeach
		
	} //end __construct

} //end Resources
