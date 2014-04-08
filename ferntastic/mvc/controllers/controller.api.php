<?php

class FernsAPIController extends FernsController {
	protected function throwError($errorMsg) {
		$r = $this->formResponse(array(
				'status' => "nok",
				'response' => $errorMsg,
		));
		die($r);	
	}
	
	protected function respond( $responseData ) {
		$r = $this->formResponse(array(
				'status' => 'ok',
				'response' => $responseData
		));
		die($r);	
	}
	protected function formResponse( $dataResponse ) {
		return json_encode($dataResponse);
	}
}

class APIController extends FernsAPIController {
	
	function index() {
		header("HTTP/10.4.4 403 Forbidden");
		die("Directory listing is unauthorized");
	}
	
	private function getAndMakeSubController( $name ) {
		$filename = trim(strtolower($name));
		if ($name == 'oauth') $subController = 'OAuth';
		else $name = trim(ucwords(strtolower($name)));
		
		$name .= 'Controller';
		
		require_once('API/controller.'.trim((strtolower($filename))).'.php');
		if (!class_exists( $name )) $this->throwError( 'Controller not found' );
		
		else return new $name;
		
	}
	
	function auth( ) {
		//we want no index page. In fact, we want it to throw a 404 but not implement the 404 page
		//this is a file and doing that would potentially cause significant problems
		//with lost file loading
		if (func_num_args() < 1) $this->throwError( 'No action specified' );
		$args = func_get_args();
		$subController = $this->getAndMakeSubController( $args[0] );
		unset($args[0]);
		reset($args);
		
		call_user_func_array(array($subController, 'index'), $args);
		
	}
	
	function payments(  ) {
		if (func_num_args() < 1) $this->throwError( 'No action specified' );
		$this->formResponse("Hey there");
		
			
	}
	
	function apps( ) {
		die(json_encode(array(
			
			array('name' => '91ferns', 'url' => '91ferns.com'),
			array('name' => 'Patsys', 'url' => "patsys.com"),
			array('name' => 'Dennis Taylor Trucking', 'url' => 'dennistaylortrucking.com')
		
		)));	
	}
	
		
}