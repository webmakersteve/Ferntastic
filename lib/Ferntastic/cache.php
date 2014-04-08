<?php

class CacheError extends NoLogError {
	private $class = __CLASS__;	
}

class CacheEngine {
	
	private function getLastModified( $filename=null ) {
		
		return filemtime ( $filename );
			
	}
	
	private function get( $identifier, $maxAge = null ) {
			
	}
		
}