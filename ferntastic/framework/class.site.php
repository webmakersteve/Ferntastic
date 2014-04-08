<?php

if (!function_exists('Fn')) exit;

/**
  *
  *
  *
  */
  
class Site {

	public $id, $name, $superadmin, $logolink, $data, $domain, $settings;

	public function __construct( $siteID ) {
		
		//we need to get the public site data
		
		//we don't want to use fQuery because it necessitates it.
		//we need to figure out how they are accessing the data
		if (is_array($siteID)) $siteID=$siteID[0];
		
		if ((int) $siteID != 0) {
			//this means siteID is an integer and thus an id
			$siteID = (int) $siteID;
			
			$sql = "SELECT * FROM `sites` WHERE id = %d";
			$sql = sprintf($sql, e($siteID));
			
		} else {
			//we assume this is a string and treat it as the identifier	
			$siteID = (string) $siteID;
			
			$sql = "SELECT * FROM `sites` WHERE idstring = '%s' LIMIT 1";
			$sql = sprintf($sql,e($siteID));
			
		}
		
		if ($siteID!=null) query($sql);
		
		if ($siteID!=null and num_rows() > 0) {
			//success loading this site	
			
			$row = assoc(); //holds the data
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->superadmin = $row['superadmin'];
			$this->logolink = $row['logolink'];
			$this->domain = $row['domain'];
			$this->data=json_decode($row['data']);
			
			//now get the metadata
			
			$sql = "SELECT datakey,datavalue FROM `sites_metadata` WHERE reference = %d";
			$sql = sprintf($sql,e($this->id));
			
			query($sql);
			
			if (num_rows() > 0) {
				//this means there is metadata
				$arr=array();
				while ($row = assoc()) $arr[$row['datakey']]=$row['datavalue'];
				
				$this->settings = new SiteSettings($arr);
				
			} else {
				//this means there is not metadata
				$this->settings = new SiteSettings(null);
				
			}
			
			return $this;
			
		} else {
			list($this->id, $this->name, $this->superadmin, $this->logolink,$this->domain, $this->data) = array(0, '91ferns', 1, 'https://secure.91ferns.com/img/91ferns.png', '91ferns.com', array());
			return $this;
		}
		
		
	}
	
	public function is_admin() {
		
		if (is_logged_in())
			if ($this->superadmin==Fn()->account->id or Fn()->account->id==1) return true; else return false;
		else return false;
			
	}

}

class SiteSettings {
	
	private $data;
	
	function __construct( $data=null ) {
		
		if ($data==null) $this->data=array();
		else {
			
			if (is_array($data) and count($data) > 1) {
				
				foreach ($data as $k=>$v) {
					$this->data[$k]=$v;
				}
				
			} else $this->data=array();
			
		}
		return $this;
		
	}
	
	function __get( $key ) {
		if (isset($this->data[$key])) return $this->data[$key];
		else return false;
	}
		
	function __set($key,$val) {return false;}
		
}

if (isset($_COOKIE['currsite'])) {
	
	$sql = "SELECT id,idstring FROM `sites` WHERE MD5(idstring) = '%s' LIMIT 1";
	$x = $_COOKIE['currsite2'];
	$sql = sprintf($sql, $x);
	
	query($sql);
	
	if (num_rows()>0) {
		
		$t = assoc();
		$id=$t['id'];
		
		Fn::add('this_site', new Site($id));
				
	} else {
		Fn::add('this_site', new Site(null));
	}	
	
} else {
	
	//let's try to get it without havng to do the ID business (for sites that we are on that domain
	$sn = $_SERVER['SERVER_NAME'];
	//get rid of www if it's there
	$sn = (string) str_replace( "www.", "", $sn );
	//now we have something to work with
	
	$sql = sprintf('SELECT id FROM `sites` WHERE `domain` LIKE "%s" LIMIT 1', $sn);
	query($sql);
	
	if (num_rows() > 0) {
		$x = assoc();
		$id = $x['id'];
		Fn::add('this_site', new Site($id));
	} else {
		Fn::add('this_site', new Site(null));
	}
	
}

Fn::add('site', function($p) {
	return new Site( $p );
});

?>