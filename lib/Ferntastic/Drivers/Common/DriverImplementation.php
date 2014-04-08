<?php

namespace Ferntastic\Drivers\Common;

class DriverImplementation {
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

