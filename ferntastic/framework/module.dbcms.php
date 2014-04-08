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

if (!function_exists('Fn') or !is_object(Fn()) or !interface_exists( 'CMS' )) die();


class DBCMS implements CMS {
	
	const EDITABLE = false;
	private $CMSNodeList = array();
	
	/**
	 * The constructor function does not take any arguments.
	 *
	 * Use the handleMe() method to only set the global var to the error text.
	 */
	
	function __construct($siteID=null) {
		
		//HOW ARE WE GONNA CONSTRUCT THIS; VIA THE SITE
		$siteID = ($siteID==null) ?  Fn()->this_site->id : $siteID;
		if (is_array($siteID)) $siteID = $siteID[0];
		Fn()->load_extension('fquery');
		
		$x = fQuery('CMS_taxonomy', "*,site[x=?],sub_of:order('asc')", $siteID);
		$categories_array = array();
		if ($x->count >1) {
			$x->each(function($d) use (&$categories_array) {
				if ($d->sub_of == 0) $categories_array[$d->id] = array( "Name" => $d->name, "children" => array());
				else {
					$categories_array[$d->sub_of][$d->id] = array("Name" => $d->name);
				}
			});
		}
		
		$f = fQuery("CMS", '*,site[x='.$siteID.'],orderable:order("desc")');
		$x = &$this->CMSNodeList;
		$f->each(function($data) use (&$x) {
			//we need to relate this to the category it is in
			$x[$data->tag][] = new CMSNode($data);
		});
		
		//now clean up single tags
		foreach ($x as $k=>$v) {
				if (is_array($x[$k]) and count($x[$k]) == 1) $x[$k] = $x[$k][0];
		}
		
	}
	
	/**
	 * Main function. Used to get DATA Stored in the CMS
	 * 
	 * 
	 */
	
	function get( $tag ) {
		
		if (isset($this->CMSNodeList[$tag])) {
			if (count($this->CMSNodeList[$tag]) > 1) return $this->CMSNodeList[$tag];
			else return $this->CMSNodeList[$tag][0];	
		} else return false;
		
	}
	
	/**
	 * This function is only loaded if the EDITABLE tag is given. Used to set DATA Stored in the CMS
	 * 
	 * 
	 */
	 
	function set( $tag, $value ) {
		
		if (!EDITABLE) return true;
		 
		 
	}
	
	/**
	 * This function is used to get the tag data for special operations.
	 * 
	 * 
	 */
	 
	function getTag( $tag ) {
		
		return new CMSNode( $tag );	
		
	}
	
	private function toFile( $filename, $cmsData ) {
		
		if ($cmsData instanceof CMSNode) {//we're good
		} else {
			try {
				$cmsData = new CMSNode($cmsData);
			} catch (CMSError $e) {
				$e->handleMe();	
				return false;
			}
		}
		
		//now we have the CMSDAta and can do the operation and place it in the file complete with encryption
			
	}
	
	public function getNodes() {
			return $this->CMSNodeList;
	}
	
}
