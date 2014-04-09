<?php

namespace Ferntastic\Drivers\Resources\Schema;

use Ferntastic\Drivers\Common\Schema as DefaultDriver;

interface Driver extends DefaultDriver {
	public function LoadResources( $Specification ); //void return
}