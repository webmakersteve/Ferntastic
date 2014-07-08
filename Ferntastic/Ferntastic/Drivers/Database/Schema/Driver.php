<?php

namespace Ferntastic\Drivers\Database\Schema;

use Ferntastic\Drivers\Common\Schema as DriverTemplate;

interface Driver extends DriverTemplate {
	
	public function Insert( $Values );
	public function Update( $Conditions, $Changes );
	public function Delete( $Conditions );
	public function Find( $Conditions );
	public function Connect( $Parameters );

    public function getColumns( $Collection );
		
}