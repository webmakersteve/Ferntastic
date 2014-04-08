<?php

interface API extends Driver {
	
	public function success( &$saveResponseTo );
	public function request( string $method, array $params=null );
	public function validate( array $data );
	public function getErrors();
}