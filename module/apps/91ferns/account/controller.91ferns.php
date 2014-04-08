<?php

class Patsys extends WebApp {
	public $uses = array('mysql','themer');	
	public $models = array(); //this is where this apps models are
	private $patsysDB;
	
	function index( $page, $itemid ) { //this method is ALWAYS called.
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

		if (method_exists( $this, $page)) call_user_func( array($this, $page), $itemid);
	}
	
	function items($itemid) {
		$result = $this->db->query('select * from `patsys_items`');
		//okay so we have the page name we can traverse through the php
		
		$viewFiles = $this->helpers()->Themer->build(__FUNCTION__);
		//anything need to be set? no?
		$this->set( 'data', $this->config );
		$this->set( 'items', $result );
		$this->getCompoundView( $viewFiles );
		
	}
	
}

