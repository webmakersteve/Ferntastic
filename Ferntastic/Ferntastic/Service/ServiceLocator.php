<?php

namespace Ferntastic\Service;

use Ferntastic\Errors\ServiceError as ServiceError;

class ServiceLocator {
	
	private $services = array();
	
	public function registerService( $key, $function ) {
		//we have two options. One is a class, the other is not
		$key = strtolower($key);
		
		if (is_string( $function ))
			if (class_exists( $function, true )) {
				//this means it is a class! yay!
				$this->services[$key] = $obj = new $function;
				
				//we need to check what class this thing extends - is it invokable or is it something else?
			} else {
				throw new ServiceError('Class does not exist: '.$function);
			}
		
		if (is_callable( $function )) {
			//this means it is a function that may in turn create something
			$this->services[$key] = $function;
		}
		
	}
	
	public function get( $key ) {
		$key = strtolower($key);
		if (!array_key_exists($key, $this->services)) throw new ServiceError('Service not registered: '.$key);
		
		$d = $this->services[$key];
		
		if (is_callable($d)) {
			return call_user_func($d);
		}
		
		if (is_object($d)) {
			return $d;	
		}
		
		
		
	}
	
}