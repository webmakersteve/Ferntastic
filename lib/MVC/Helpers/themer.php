<?php
/**
 * Dummy text
 *
 * @package 91ferns
 */

namespace MVC\Helpers;

class Themer extends Helper {
	public $config;
	public $basePath;
	
	
	function __construct() {
			
	}
	
	private function findInFS( $regionName ) {
		/**
		 * This file implements inheritence and 'smart' checking
		 * to find the appropriate file to correspond with the theme's 
		 * region
		 * First check in app folder then look for parents to use instead
		 */
		
		if (file_exists( $file = $this->basePath . DS . 'regions' . DS . $regionName )) {
		} elseif (file_exists( $file = $this->basePath . DS . 'regions' . DS . $regionName . '.php' )) {
		} elseif (file_exists( $file = $this->basePath . DS . 'regions' . DS . $regionName . '.region' )) {
		} elseif (file_exists( $file = __MODELS . DS . 'views' . DS . 'regions' . DS . $regionName )) {
		} elseif (file_exists( $file = __MODELS . DS . 'views' . DS . 'regions' . DS . $regionName . '.php' )) {
		} elseif (file_exists( $file = __MODELS . DS . 'views' . DS . 'regions' . DS . $regionName . '.region' )) {
		} elseif (file_exists( $file = __MODELS . DS . 'views' . DS . 'regions' . DS . $regionName . '.php' )) {
		} else $file = false;
		
		return $file;
	}
	
	function build( $pagename = 'default') {
		
		//we need to simply return the appropriate path otherwise the setting and inclusion will not work
		//since we will be in this functions variable scope
		
		//these files should be formatted as views which is to say minimal php with html, 
		//receiving a good old json data string
		if ($this->config->layouts && count($this->config->layouts) > 0) {
		foreach( $this->config->layouts as $k => $data ) {
			if ($k == $pagename) {
				//we found it.
				//load the region
				$paths = $this->config->layouts->$k->regions;
			}
		} //endforeach
		
		if (!$paths) {
			if (isset( $this->config->layouts->default )) {
				$paths = $this->config->layouts->default->regions;
			} else {//otherwise...
				return false;
			}
		}
		}
		
		$return = array();
		if (!$paths) $paths = array($pagename);
		foreach($paths as $path) {
			$tmp = $this->findInFS( $path );
			if ($tmp) $return[] = $tmp;
		}
		
		if (count($return) < 1) return false;
		return $return;
		
	}
	
}
