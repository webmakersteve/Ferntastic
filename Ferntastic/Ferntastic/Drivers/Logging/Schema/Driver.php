<?php

namespace Ferntastic\Drivers\Mail\Schema;

interface Driver {
	
	public function Insert( $Values );
	public function Update( $Conditions, $Changes );
	public function Delete( $Conditions );
	public function Find( $Conditions );
	public function Connect( $Parameters );
		
}