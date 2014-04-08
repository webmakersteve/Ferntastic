<?php

require_once( __MODELS . DS . 'models' . DS . 'model.userstable.php' );

class AuthController extends FernsController {
	
	function index( ) {
		
		$this->login();
	}
	
	protected function headers() {
		SiteRequest::setSessionData('OLD_POST', $_POST, true);
		
		$table = new UsersTable();
		fQuery::$useDatabase = $this->db;
		return $table;
	}
	
	function logout() {
		SiteResponse::removeCookie(FernFigure::LOGIN_COOKIE_NAME);
		SiteResponse::removeCookie(FernFigure::LOGIN_COOKIE_REMEMBER);
		$this->redirect('/');
	}
	
	private function setIdentity( User $User , $Persistent=false) {
		//now set authentication cookies
		
		FernIdentity::setIdentity( $User );
	}
	
	function login() {
		$table = $this->headers(); //we need to do this so the other pages will have the old form data
		$error = false;
		$msg = ''; //initialize the message variable. I cant find a way where PHP lets me do it with an uninitialized var
		
		$p = $this->Request->Post();
		if (!$p->isFormat('Email', 'email', $msg)) SiteRequest::setSessionData('formerror', array('msg' => "<span class=\"formerror\">".$msg."</span>", "field" => "email"), true);
		if (!$p->isFormat('Password', 'password', $msg)) SiteRequest::setSessionData('formerror', array('msg' => "<span class=\"formerror\">".$msg."</span>", "field" => "password"), true);
		else {
			//structure should be [domain].[extension], but [extension] can have
			if ( $x = $table->authenticate($p->Email,$p->Password)) {
				//logged in
				$this->setIdentity($x, $p->Remember);
			} else {
				SiteRequest::setSessionData('formerror', 	array('msg' => "<span class=\"formerror\">Invalid Credentials.</span>",) );
			}
			
			
		} //end else
		//should have redirected already if we didnt something is up
		$this->redirect( "/" ); //back to home	
	}
	
	function dump() {
		print_r($_POST);	
	}
	
		
}