<?php

interface DatabaseDriver extends Driver {
	
	protected $savedConnections = array();
	
	public function connect( array $params=null, string $saveAs ); //params for different drivers can be different. Some may require additional credentials
	public function isConnected(); //@return bool
	public function query( array $queryData ); //@return Query; needs to be an array. database can then build the sql from the array
	public function e( string $str ); //escapes and formats properly for database queries
	
}

interface Query extends Driver {
	public function num(); //needs to return num return rows or whatever they're called in the others
	public function iterate(); //needs to iterate through values
	public function first(); //needs to return the first row
	public function data(); //needs to return the data as an array
}