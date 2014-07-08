<?php

namespace Ferntastic\Formatting;

class Strings {
	
	public static function removeTrailingS( $string ) {
		return preg_replace("#s$#i", "", $string );	
	}

    public static function removeQuotes( $string ) {
        return $string;
    }

}

