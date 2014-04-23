<?php

namespace Ferntastic\Formatting;

class Strings {
	
	public function removeTrailingS( $string ) {
		return preg_replace("#s$#i", "", $string );	
	}
		
}

