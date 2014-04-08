<?php

class DefaultDriver implements Driver {
	
	protected static $instance = NULL;
	protected function __construct() {
		return $this;
	}
	
	public static function Invoke() {
		if (self::$instance === NULL) self::$instance = new self();
		return self::$instance;
	}
	
}