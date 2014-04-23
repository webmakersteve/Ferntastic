<?php

namespace Ferntastic\MVC\Controller;

class WebApp extends Controller {
	protected $config = array();
	public $helpers;
	public function setHelper( $helperName, $data ) {
		$this->helpers[ $helperName ] = $data;	
	}
	public function helpers() {
		return (object) $this->helpers;	
	}
	
	public final function config( $data ) {
		//setter for config
		$this->config = $data;	
	}
}

