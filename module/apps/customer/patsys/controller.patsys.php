<?php

class Patsys extends WebApp {
	public $uses = array('mysql','themer');	
	public $models = array(); //this is where this apps models are
	private $patsysDB;
	
	private function initItemSchema() {
		//only viewable/editable stuff needs to be in the schema. Except keys which also need to be in here.
		foreach( $this->config->schema as $k => $schema ) DatabaseMapper::add( $k, $schema );
	}
	
	function index( $page=null, $itemid=null ) { //this method is ALWAYS called.
		
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
		
		$this->initItemSchema(); //does this in the config
		 
		fQuery::$useDatabase = $this->db;
		$this->helpers()->Themer->basePath=dirname(__FILE__);
		
		if ($page != null && $itemid != null)
			if (method_exists( $this, $page)) call_user_func( array($this, $page), $itemid);
			else {
				//404
			}
		else {
			//actual index controller
			$viewFiles = $this->helpers()->Themer->build(__FUNCTION__);
			$this->set( 'data', $this->config );
			$this->getCompoundView( $viewFiles );
			print_r($viewFiles);
			echo 'hey';
		}
	}
	
	function items($itemid) {
		Fn()->load_extension('fQuery');
		$result = fQuery('items', '*'); //my beautiful... how wonderful you are
		//okay so we have the page name we can traverse through the php
		
		$viewFiles = $this->helpers()->Themer->build(__FUNCTION__);
		//anything need to be set? no?
		$this->set( 'data', $this->config );
		$this->set( 'items', $result );
		$this->getCompoundView( $viewFiles );
		
	}
	
}

class DatabaseMapper extends Model {
	
	public static $schema;
	
	static function validate( $string, $validator ) {
		return Formatter::is( $validator, $string );
	}
	
	static function add( $key, $schema ) {
		//we actually do some work here
		//create reference
		$ref = array();
		foreach( $schema as $key => $row ) {
			$thisRow = array();
			
			$dT = strtolower($row->dataType);
			//split it
			$split = preg_match("#([a-z]+)\(([0-9]+)\)#i", $dT, $matches);
			
			$thisRow['length'] = $matches[2];
			$thisRow['type'] = $matches[1];
			$v = $row->validator;
			$thisRow['validator'] = function($s) use ($v) {
				DatabaseMapper::validate( $s, $v );
			};
			//split mapTo
			if (is_string($row->mapTo)) {
				if (strstr($row->mapTo, ".")) {
					$map = explode(".", $row->mapTo);
					$thisRow['map'] = array('table' => $map[0], 'row' => $map[1]);
				} else {
					$thisRow['map'] = array('row' => $row->mapTo);
				}
			} elseif (is_object($row->mapTo)) {
				//gotta do the foreign one too this will only be the primary
				if (strstr($row->mapTo->key, ".")) {
					$map = explode(".", $row->mapTo->key);
					$thisRow['map']['table'] = $map[0];
					$thisRow['map']['row'] = $map[1];
				} else {
					$thisRow['map']['row'] = $row->mapTo->key;
				}
				if (strstr($row->mapTo->foreignKey, ".")) {
					$map = explode(".", $row->mapTo->foreignKey);
					$thisRow['map']['foreignTable'] = $map[0];
					$thisRow['map']['foreignRow'] = $map[1];
				} else {
					$thisRow['map']['foreignRow'] = $row->mapTo->key;
				}	
			}
			if ($row->key) $thisRow['isKey'] = true;
			$ref[$key] = (object) $thisRow;
		}
		
		self::$schema = $ref;
		
	}	
		
}