<?php

class DefaultController extends FernsController {
	
	public $uses = array('Themer');
	public $models = array('UsersTable');
	
	private function headers() {
		fQuery::$useDatabase = $this->db;
	}
	
	private function globalSets ( ) {
		
		/** Is Logged In stuff **/
		$id = FernIdentity::getIdentity();
		
		$strings = R()->get()->strings;
		$res = (object) array(
			'login_title' => $strings->login_title,
			'login_intro' => $strings->login_intro,
			'login_form_title' => $strings->login_form_title,
			'login_remember_field' => $strings->login_remember_field,
			'login_noremember_label' => $strings->login_noremember_label,
			'login_diffuser_label' => $strings->login_diffuser_label,
			'login_email_field' => $strings->login_email_field,
			'login_password_field' => $strings->login_password_field,
		);
		$return = array(
			'res' => $res,
			'scriptError' => SiteRequest::getSessionData('errormsg'),
			'error' => SiteRequest::getSessionData('formerror'),
			'oldpost' => SiteRequest::getSessionData('OLD_POST'),
		);
		
		if ($id) $return['user'] = $id;
		else $return['user'] = false;
		
		return $return;
	}
	
	private function notLoggedIn() {
	
		$t = $this->Themer;
		$t->config->layouts->default->regions = (object) array('header', 'content', 'footer');
		$viewFiles = $t->build();
		$v = new View();
		$v->set( $this->globalSets() );
		$v->getCompoundView( $viewFiles );
		SiteRequest::killTransients();
	}
	
	private function loggedIn() {
		$User = FernIdentity::getIdentity();
		$t = $this->Themer;
		$t->config->layouts->default->regions = (object) array('header', 'sidebar', 'logged-in-default', 'footer');
		$v = new View();
		$v->set( $this->globalSets() );
		$v->getCompoundView( $t->build() );
		SiteRequest::killTransients();
	}
	
	function index() {
		$this->headers();
		if (FernIdentity::getIdentity()) $this->loggedIn();
		else $this->notLoggedIn();	
	}
		
}