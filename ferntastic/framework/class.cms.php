<?php

/**
  * This is the file for the 91ferns CMS
  * 
  * The 91ferns Content Management System is built to be a light-weight, basic functionality CMS. It deals mostly with changing text on a page; not creating entirely new pages, though this functionality will be built in after the 91ferns site builder is made. 
  *
  * The basic usage is called in the 91ferns HTML files after this file is included. This file is an external file and must be called after the functions.php file.
  * Basic class is CMS. 
  * @version 0.1
  * @author Stephen Parente <sparente@91ferns.com>
  */

if (!function_exists('Fn') or !is_object(Fn())) die();

//Are we using a filebased module or a DB based module
if (!defined('CMS_TYPE')) define('CMS_TYPE', 'DB');

interface CMS {
	const FILEXT = "fns";
	public function get( $tag );
	public function set( $tag, $value );
	public function getTag( $tag );	
}

if (CMS_TYPE == "FILE") require('module.fscms.php');
else require('module.dbcms.php');

//Get DATA for the current site
//REQUIRES SITES

class CMSNode {
	
	public $type;
	private $id,$value,$editable;
	
	function __construct( $dataConstruct ) {
		
		//OK.
		$this->value = $dataConstruct->value;
		$this->id = $dataConstruct->id;
		$this->type = $dataConstruct->type;
		$this->editable = ($dataContruct->editable==1) ? true : false;
		
	}
	
	public function __toString() {
		return $this->value;	
	}
	
}

/* Apply the CMS to the FN Class */
if (!defined('NO_CMS_INSTANTIATION')) {
	if (CMS_TYPE=="FILE") {
		Fn::add('CMS', new FSCMS());
	} else {
		Fn::add('CMS', new DBCMS());
	}
} else {	
}

Fn::add('CMS', function($x) {
	if (CMS_TYPE=="FILE") {
		return new FSCMS($x);
	} else {
		return new DBCMS($x);
	}
});