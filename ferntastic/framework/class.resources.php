<?php
/**
 * Resource loading functionality.
 * Resources are modeled after the Android resource system.
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.1
 *
 */

if (!function_exists('Fn')) die();
if (!defined('RESOURCE_FP')) define('RESOURCE_FP',  dirname(dirname(EXTPATH)) . DIRECTORY_SEPARATOR . 'res');

 
/**
 * ResourceError is the Loggable Error thrown when something goes wrong with resources.
 *
 * @package errors
 * @version 0.1
 *
 */

class ResourceError extends LogError {private $type = __CLASS__;}

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

/**
 * ResourceError is the Loggable Error thrown when something goes wrong with resources.
 *
 * @package errors
 * @version 0.1
 *
 */
 
class ResourceLoader {
	
	protected $resources = array(); //holds all of the resource data lowest priority to highest
	
	public function load( $the_path ) {
	
		/**
		 * Constructs the Resources. Only method in class. 
		 * Goes through the RESOURCE_FP. Loads all the different XML files in the directory provided.
		 * @param string Optional list of params that would be the files loaded. If specific, will only load the specified resources.
		 
		 */
		 $tres = "";
		//first it needs to turn the ID into the appropriate filepath, and then it executes it. If $id is a string, it will go right to the filepath
	
		try {
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
		
		} catch (ResourceError $e) {
			$e->handleMe();
		} //end try catch
		
	}
	
	private $default;
	private $pathsTried = array();
	
	public function get ( $the_path=null ) {
		if ($the_path == null && !empty($this->default)) $the_path = $this->default;
		elseif (isset($this->resources[md5($the_path)])) return $this->resources[md5($the_path)];
		//elseif ($the_path == null && empty($this->default)) throw new ResourceError('no_path_set');
		
		//now we need to iterate through all of them in order and merge them but make sure default is merged last
		$returnResourcesObject = array();
		foreach( $this->pathsTried as $path ) {
			if ($path == $the_path) continue;
			if (isset($this->resources[md5($path)])) $returnResourcesObject = array_merge( (array) $this->resources[md5($path)], $returnResourcesObject );	
		}
		
		//now merge the default
		if (isset($this->resources[md5($the_path)]))
			$returnResourcesObject = array_merge( (array) $this->resources[md5($the_path)], $returnResourcesObject );	
		else {
			$this->load( $the_path ); //try loading it
			if (isset($this->resources[md5($the_path)]))
				$returnResourcesObject = array_merge( (array) $this->resources[md5($the_path)], $returnResourcesObject );
			else return;
		}
		
		return (object) $returnResourcesObject;
	}
	
	public function setDefault( $the_path ) {
		if (array_key_exists(md5($the_path), $this->resources)) $this->default=$the_path;
		else return false;
	}
	
	public function dumpLocations() { print_r($this->pathsTried); }

}

$l = new ResourceLoader(); //initializes resources
$l->load( RESOURCE_FP ); //this wil lload our first resources into the object
$l->setDefault( RESOURCE_FP ); //initial default

Fn::add('resources', $l); //invokable

function R() {
	return Fn()->resources;	
}

?>