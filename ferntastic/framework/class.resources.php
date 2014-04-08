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

 
class ResourceLoader extends DriverImplementation {
	
	protected $DefaultDriver = 'ResourceXMLDriver'; //overrides class category
	
	public static function Uses(ResourceDriver $x ) {
		self::$Driver = $x;
	}
	
	protected $loadedTypes = array();
	
	protected function LoadAll( $Specification ) {
		
		try {
			if (!$this->HasSetDriver()) throw new DriverError( ERROR_NO_DRIVER_SET );
			self::$Driver->LoadResources( $Specification );
			$this->loadedTypes = self::$Driver->LoadedTypes();
			
		} catch (ResourceError $e) {
			$e->handleMe();
		} 
		
	}
	
	public function Get( $type ) {
		//this returns an object so it is accessed in this manner
		//R()->strings->awesome
		//R() returns ResourceLoader->Get();
		if (!in_array( $type, $this->loadedTypes )) return NULL;
		$category = self::$Driver->Get( $type ); //this will give us 
		if ($type instanceof ResourceCategory) {
			return $type->toObject();	
		} else throw new ResourceError( ERROR_RESOURCE_INVALID_CATEGORY );
		
	}

}

Fn::AddInvokable('resources', 'ResourceLoader'); //invokable
//versus Fn::AddCreatable(); which takes tag name and class name or closure

function R( $Type ) {
	return Fn()->resources->Get( $Type );
}

?>