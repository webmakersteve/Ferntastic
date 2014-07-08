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
		$DefaultDriver = $c::Invoke();
		self::Uses($DefaultDriver);
		return true;
	}
	
	public static function Uses( Driver $x ) {
		self::$Driver = $x;	
	}
    protected function getDriver() {
        if ($this->HasSetDriver()) return self::$Driver;
        else throw new Exception(); //@todo
    }
}

