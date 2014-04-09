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
namespace Ferntastic;
 
use Ferntastic\Drivers\Common\DriverImplementation;
use Ferntastic\Drivers\Resources\Schema\Driver as ResourceDriver;
use Ferntastic\Errors\ResourceError;
 
class Resources extends DriverImplementation {
	
	protected static $instance = NULL;
	protected function __construct() {
		return $this;
	}
	
	public static function Invoke() {
		if (self::$instance === NULL) self::$instance = new self();
		return self::$instance;
	}	
	
	protected $DefaultDriver = 'Ferntastic\\Drivers\\Resources\\XML'; //overrides class category
	
	public static function Uses( ResourceDriver $x ) {
		self::$Driver = $x;
	}
	
	protected $loadedTypes = array();
	
	protected function LoadAll( $Specification ) {
		
		try {
			if (!$this->HasSetDriver()) throw new DriverError( ERROR_NO_DRIVER_SET );
			$res = self::$Driver->LoadResources( $Specification );
			if (!is_array($res)) throw new ResourceError( ERROR_RESOURCES_DRIVER_EXPECTED_ARRAY );
			
			$this->Resources[md5($Specification)] = $res;
			$this->loadedSpecifications[] = $Specification;
			return true;
			
		} catch (ResourceError $e) {
			$e->handleMe();
			return false;
		} 
		
	}
	
	protected $Specification = '.';
	protected $loadedSpecifications = array();
	
	public function setDirectory( $Directory ) {
		$this->Specification = $Directory;	
	}
	
	protected $Resources;
	protected $Map = array();
	protected function Type( $type ) {
		$spec = md5($this->Specification);
		$cwr = $this->Resources[$spec];
		$keys = array_keys( $cwr );
		//check map
		if (!array_key_exists($spec, $this->Map)) $this->Map[$spec] = array();
		if (array_key_exists( $type, $this->Map[$spec] )) $k = $this->Map[$spec][$type];
		else $k = preg_grep( sprintf('#%ss?#i', $type), $keys );
		if ($k) {
			$k = array_values($k);
			$k = $k[0];
			$this->Map[$spec][$type] = (string) $k;
			return $cwr[(string) $k];
		}
		return NULL;
	}
	
	public function Get( $type ) {
		//this returns an object so it is accessed in this manner
		//R()->strings->awesome
		//R() returns ResourceLoader->Get();
		if (!$this->specHasBeenLoaded())
			if (!$this->LoadAll( $this->Specification )) throw new ResourceError( ERROR_RESOURCES_NO_LOAD ); //resources were not loaded
		
		$category = $this->Type( $type ); //this will give us
		if ($category === NULL) return NULL;
		else return $category; 
		
	}
	
	protected function specHasBeenLoaded( $Spec = NULL ) {
		if ($Spec === NULL) $Spec = $this->Specification;
		if (!in_array( $Spec, $this->loadedSpecifications )) return false;
		return true;
	}

}

function R( $Type ) {
	return Fn()->resources->Get( $Type );
}

?>