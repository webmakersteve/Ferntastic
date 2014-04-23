<?php

namespace Ferntastic\Files;

class Includer {
	
	public function safeInclude( $str ) {
		if (!is_readable( $str )) return false;//throw new \Ferntastic\Errors\InclusionError("No file by that name");
		include_once( $str );
		return true;
	}
		
}

