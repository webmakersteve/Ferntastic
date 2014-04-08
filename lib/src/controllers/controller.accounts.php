<?php

class AccountController extends FernsController {
	
	public $uses = array('Themer');
	
	private function buildGlobalAppData() {
		
		//a lot of interesting stuff here. For example, using the model
		//we can get a bunch of the apps and their configuration files
		//then we load data about them to build navigation blocks.
		//We can then use the blocks given in the folders
		//to create the menus from the given blocks
		//NEAT HUH? ITS MVC!
		
		
			
	}
	
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
	
	function index( ) {
		$this->redirect("/", 403);
	}
	
	function create(  ) {
		
		$this->headers();
		//if (FernIdentity::getIdentity()) $this->redirect("/");
		
		$t = $this->Themer;
		$t->config->layouts->default->regions = (object) array('header', 'default', 'footer');
		$viewFiles = $t->build();
		$v = new View();
		$v->set( $this->globalSets() );
		$v->getCompoundView( $viewFiles );
		SiteRequest::killTransients();
		
		
	}
	
		
}