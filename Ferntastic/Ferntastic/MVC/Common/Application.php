<?php

namespace Ferntastic\MVC\Common;

class Application {
	
	private $path = NULL;
	
	public function getModulePath() {
		return 'module' . DS . 'src';
		return $this->path;
	}
	
	public function setModulePath( $path ) {
		$this->path = $path;
	}
	
}