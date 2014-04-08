<?php

class Ingenuity extends WebApp {
	public $uses = array('themer');	
	
	function index( $arg='default' ) { //this method is ALWAYS called.
		/** 
		 * Controllers have a lot of power over their own execution. Once the other
		 * AppController finishes up what it was doing, it then
		 * opens up this index or default method.. Now it is up to this method to determine
		 * how the controller works after
		 *
		 * The alternative is to automagically use the $page, or next argument, and call
		 * the method if it exists. But then we don't have the DB established. Other option is
		 * to call both automatically. This can be considered in time but I think I'd prefer to do 
		 * it this way even though it looks pretty messy
		 *
		 */
		$this->helpers()->Themer->basePath=dirname(__FILE__);
		
		if ($arg == 'default' || method_exists( $this, $arg ))
			$this->load();
		else {
			$this->fourohfour();
		}
	}
	
	private function fourohfour() {
		header("HTTP/1.0 404 Not Found");
		die("404 - we couldnt find the page");	
	}
	
	private function load() {
		$viewFiles = $this->helpers()->Themer->build(__FUNCTION__);
		//anything need to be set? no?
		$this->set( 'data', $this->config );
		$this->getCompoundView( $viewFiles );
		
	}
	
}

