<?php

namespace Ferntastic\Drivers\Resources\Schema;

use Ferntastic\Drivers\Common\Schema as DefaultDriver;

interface Driver extends DefaultDriver {
	public function Get( $type ); //returns ResourceCategory
	public function LoadedTypes(); //returns array of loaded types
	
	public function toArray(); //returns Array
	public function LoadResources( $Specification ); //void return
}

interface ResourceCategory extends Driver {
	public function toObject();
}