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
	
	private static $Driver = NULL;
	
	public static function uses(ResourceDriver $x ) {
		self::$Driver = $x;
	}
	
	protected function LoadAll( $Specification ) {
	
		try {
			
			self::$Driver->LoadResources( $Specification );
			
		} catch (ResourceError $e) {
			$e->handleMe();
		} 
		
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