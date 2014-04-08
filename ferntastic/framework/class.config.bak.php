<?php

/**
 * Config loads and can dynamically set configuration values.
 * This file will load the configuration from the config directory and interpret it for use
 * @author Stephen Parente <sparente@91ferns.com>
 * @version 0.1
 * @package Error
 */


/**
 * Global Declaration DocBlock
 */

$GLOBALS['conf'] = false;

/**
 * Config is the class loader.
 *
 * 
 */

if (!function_exists('Fn')) die();

/**
 * 
 * <code>
 * 
 * $c = new Config(); //loads the config
 * $c->get('HEADER'); //returns true if exists, false if doesn't
 * 
 * $c->get('account.ini')
 *
 * </code>
 */

class Config {
	
	private $config = array();
	private $files_loaded = array();
	private $by_file = array();
	
	function __construct() {
		
		if (func_num_args() > 0) {
			$sheep = func_get_args();
		}
		
		try {
			$dirpath = EXTPATH . "/config";
			if (!file_exists($dirpath)) return false;
			if ($dir = opendir($dirpath)) {
				
				while (($file = readdir($dir)) !== false) {
					
					if ( !preg_match("#[.]ini$#i", $file) || ( isset( $sheep ) && !in_array( $file, $sheep ) ) ) {
						//these are for non ini files
					} else {
						
						//these are the files that exist
						$parsed = parse_ini_file( $dirpath . "/" . $file , false );
						if ($parsed) {
							$this->files_loaded[] = $file;
							$this->by_file[$file] = $parsed;
							if (count($this->config) > 0) $this->config = array_merge( $this->config, $parsed );
							else $this->config = $parsed;
						} //end if it was parsed
						
					} //end if the file is an INI file
					
				} //end if the file exists (while loop)
				
			} else { //end if the dir exists 
				throw new ConfigError('noconfigdir');
			}
						
		} catch (ConfigError $e) {
			$e->handleMe();
			return alse;
		}
		
	}
	
	public function define( $key, $value ) {
		
		$this->config[$key] = $value;
		
	}
	
	public function get( $key ) {
		return isset($this->config[$key]) ? $this->config[$key] : false;
	}
	
	public function superfile( $filename ) {
		
		if ( isset($this->by_file[ $filename ] ) ) {
			
			$this->config = array_merge( $this->by_file[ $filename ], $this->config );
			printf("%s file has in it config data:", $filename);
			print_r( $this->config );
			return $this->by_file[ $filename ];
			
		}
		
		return false;
			
	}
	
	public function file( $filename ) {
		
		if ( isset($this->by_file[ $filename ] ) ) return $this->by_file[ $filename ];
		
		return false;
		
	}
	
	function __toString() {
		return "Configuration Data";
	}
	
	
}

Fn::add( 'config', new Config() );

?>