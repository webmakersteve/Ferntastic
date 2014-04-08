<?php

class User extends Model {
	
	protected $dataSource = 'db';
	
	protected $id, $email, $uniquestr, $data; //computer-y stuff?
	protected $firstName, $lastName, $domain;
	
	function exchange( $data ) {
		foreach( array( 'id', 'username', 'uniquestr', 'data' ) as $d ) if (!array_key_exists( $d, $data )) return;
		$this->id = $data['id'];
		$this->email = $data['username'];
		$this->uniquestr = $data['uniquestr'];
		$this->data = $data['data'];
		
		$this->firstName = $data['firstname'];
		$this->lastName = $data['lastName'];
		$this->domain = $data['domain'];
	}
	
	function getID() {return $this->id;}
	function getUStr() {return $this->uniquestr;}
	function getEmail() {return $this->email;}
	function setUStr( $value ) { $this->uniquestr = $value; }
	
	function __toString() {
		
		return json_encode( array(
			'first_name' => $this->firstName,
			'id' => $this->id,
			'last_name' => $this->firstName,
			'data' => $this->data,
			'email' => $this->email,
			'ustr' => $this->uniquestr
		));
			
	}
		
}