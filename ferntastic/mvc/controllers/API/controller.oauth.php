<?php
require_once( __MODELS . DS . 'models' . DS . 'model.userstable.php' );

class OAuthController extends FernsAPIController {
	protected function headers() {
		$table = new UsersTable();
		fQuery::$useDatabase = $this->db;
		return $table;
	}
	public function index() {
		$table = $this->headers();
		$p = $this->Request->Post();
		if (!$p->isFormat('Email', 'email', $msg)) $this->throwError("Please enter a valid Email: ");
		if (!$p->isFormat('Password', 'password', $msg)) $this->throwError("Please enter a valid password");
		else {
			if ( $x = $table->authenticate($p->Email,$p->Password)) {
				//logged in
				//$this->setIdentity($x, $p->Remember);
				//we do not set identity here we create an api code
				$this->respond( "API CODE: ".md5(rand(0,100000000000)));
			} else {
				$this->throwError("Invalid Credentials");
			}
		} //end else
	}
}