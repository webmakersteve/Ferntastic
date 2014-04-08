<?php

class DefaultDriver extends Singleton implements Driver {
	
	
	
}

class DriverImplementation extends Singleton {
	protected $DefaultDriver = NULL;
	protected static $Driver;
	protected function HasSetDriver() {
		if (self::$Driver instanceof Driver) return true;
		if ($this->DefaultDriver === NULL) {
			return false;
		}
		$c = $this->DefaultDriver;
		$DefaultDriver = new $c();
		self::Uses($c);
		return true;
	}
	
	public static function Uses( Driver $x ) {
		self::$Driver = $x;	
	}
}

class Singleton {
	protected static $instance = NULL;
	protected function __construct() {
		return $this;
	}
	
	public static function Invoke() {
		if (self::$instance === NULL) self::$instance = new self();
		return self::$instance;
	}	
}