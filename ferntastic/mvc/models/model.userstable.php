<?php

require_once( 'model.user.php' );

class UsersTable extends Model {
	
	protected $dataSource = 'db';
	
	function useDB( /*DBDriver*/ $db ) {
		$this->dataSource = $db;	
	}
	
	function authenticate( $email, $password, $userDynamicPassword = false ) {
		
		Fn()->load_extension( 'users' );
		
		try {
		//check if password is an array. If it is we have 2 part authenticate
			if ($userDynamicPassword === true) $f = fQuery('accounts', '*,username[x=?],uniquestr[x=?]:limit(1)', $email, $password);
			else $f = fQuery('accounts', '*,username[x=?],password[x=?]:limit(1)', $email, FernIdentity::encryptPassword($password));
		} catch (NoLogError $e) {
			echo "Could not connect to db";
			return;	
		}
		if ($f->count == 1) {
			
			$row = $f->this();
			if ($data_return==null)$data_return=array();
			$data_return=$row->to_array();
			extract($data_return);
			
			if (!$userDynamicPassword) {
				$uniquestring = sha1(FernIdentity::encryptPassword(md5(base64_encode($email).time().rand()*1000000)));
				$f->update(array('uniquestr' => $uniquestring ));
			}
			
			$u = new User();
			$u->exchange( $data_return );
			$u->setUStr( $uniquestring );
			return $u;
		} else {
			return false;
		}
			
	}
	
	function findByID( $ID ) {
		$f = fQuery('accounts', '*,id[x=?]:limit(1)', $ID);
		if ($f->count > 0) return $f->this();
		else return false;
	}
	
	function findAll() {
		$return = array();
		fQuery('accounts', '*')->each( function( $d ) use (&$return) {
			$return[] = $d->to_array();
		});
		return $return;
	}
	function findAllInWorkgroup( $wkgp ) {
		$return = array();
		fQuery('accounts', '*,domain[x=?]', $wkgp)->each( function( $d ) use (&$return) {
			$return[] = $d->to_array();
		});
		return $return;
	}
	
		
}