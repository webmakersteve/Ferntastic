<?php

class Helpers {
	private static $HELPERS_DIR;
	private $helpers = array(
		'gui' => 'gui.php',
		'widgets' => 'widget.php',
		'themer' => 'themer.php'
	);
	public $config;
	function __construct() {
		self::$HELPERS_DIR  = __INCLUDES . DS . 'helpers' . DS;
	}
	
	function load( $helper ) {
		if (array_key_exists( $helper, $this->helpers)) {
			if (!include( self::$HELPERS_DIR . $this->helpers[$helper] )) return false;
			return true;
		}
		return false;
	}
}

class Helper {
	
}

Fn::add('helper', new Helpers() );