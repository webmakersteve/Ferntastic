<?php

$GLOBALS['nonce'] = '';

class Nonce {
	
	const nonce_prevalue = 'FERNTASY_NONCE_SALTVAL_1F3AS9FC9';
	const NONCE_EXPIRATION = 86400;
	
	private $value = '';
	
	function __construct() {
		$this->generate();
	}
	
	private function generate() {
	
		if ($this->value!='') {
			return $this->value;
		} else {
			
			//first check if there is an unexpired nonce for this given IP, and user if its logged in
			$noncepre = Nonce::nonce_prevalue;
			$noncerand = rand()*600000+1;
			
			if (is_logged_in()) {
				$nonceuser = 0;//user_id();
			} else $nonceuser = 0;
			
			$noncetime = time();
			
			$presql = "SELECT nonce,time FROM `nonce` WHERE ip = '%s' AND acc = %d AND active = 1 LIMIT 1"; //this cannot be as queries must be filtered through fquery object
			$sql = sprintf($presql, e($_SERVER['REMOTE_ADDR']), $nonceuser); //make the SQL statement
			
			try {
				
				$q = query($sql);
				$num = num_rows($q); //get the number of rows
				
				if ($num == 1) { //if there's already a nonce
					
					$row = assoc($q);
					$this->value = $row['nonce'];
					return $row['nonce'];
					
				} else { //if there isn't a nonce in the database, generate it
					
					$prenonce = $noncepre.$noncerand.$nonceuser.$noncetime;
					$nonce = md5($prenonce);
					
					//insert the new nonce
					$presql = "INSERT INTO `nonce` (ip, time, acc, nonce, active) VALUES ('%s', %d, %d, '%s', 1)";
					$sql = sprintf($presql, e($_SERVER['REMOTE_ADDR']), time(), $nonceuser, $nonce);
					
					try {
						
						$q = query($sql);
						$this->value = $nonce;
						if (insert_id())
						return $nonce; else return false;
						
					} catch (LogError $e) {
						throw $e;
					}
					
				}
				
			} catch (LogError $e) {
			
				return false;
				
			}
			
		}
			
		
	}
	
	public function value() {
		return $this->value;	
	}
	
	public function destroy() {
		
		$nonce = $this->value;
		$presql = "SELECT id FROM `nonce` WHERE nonce = '%s' AND active = 1 LIMIT 1";
		$sql = sprintf($presql, e($nonce));
		
		try {
			
			$q = query($sql);
			$num = num_rows($q);
			
			if ($num > 0) {
				
				$row = assoc($q);
				$id = $row['id'];
				
				$sql = sprintf("UPDATE `nonce` SET active = 0, used = %d WHERE id = %d",time(), $id);
				$q = query($sql);
				
				$aff = affected_rows();
				if ($aff = 1) return true; else return false;
			
			} else {
				return false;
			}
			
		} catch (LogError $e) {
			return false;
		}
		
	}
	
	function is_current( $nonce=null ) {

		$nonce = ($nonce==null) ? $this->value : $nonce;
		$presql = "SELECT time FROM `nonce` WHERE nonce = '%s' AND active = 1 LIMIT 1";
		$sql = sprintf($presql, e($nonce));
		
		try {
			
			$q = query($sql);
			$num = num_rows($q);
			
			if ($num > 0) {
				
				$row = assoc($q);
				
				if ($row['time'] > time() - Nonce::NONCE_EXPIRATION) { //if the nonce is NOT expired
					return true;
				} else {
					$this->destroy();
					return false;
				}
				
			} else {
				return false; //this generates a new nonce
			}
			
		} catch (LogError $e) {
			return false;
		}
		
	}
	
}

Fn::add( 'nonce', new Nonce() ); //this is the noncing