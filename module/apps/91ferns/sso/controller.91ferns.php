<?php

//require_once( __MODELS . DS . 'models' . DS . 'model.userstable.php' );

class SSO extends WebApp {
	public $uses = array('mysql','themer');	
	public $models = array('userstable'); //this is where this apps models are
	private $fernDB;
	
	function index( $page = null ) { //this method is ALWAYS called.
		/** 
		 * Controllers have a lot of power over their own execution. Once the other
		 * AppController finishes up what it was doing, it then
		 * opens up this index or default method.. Now it is up to this method to determine
		 * how the controller works after
		 *
		 * The alternative is to automagically use the $page, or next argument, and call
		 * the method if it exists. But then we don't have the DB established. Other option is
		 * to call both automatically. This can be considered in time but I think I'd prefer to do 
		 * it this way even though it looks pretty messy
		 *
		 */
		fQuery::$useDatabase = $this->db;
		$this->helpers()->Themer->basePath=dirname(__FILE__);
		
		if ($page !== null && method_exists( $this, $page)) call_user_func( array($this, $page), $itemid);
		else $this->login();
	}
	
	function login() {
		if ($x = FernIdentity::getIdentity( )) {
			$this->callback($x);
			return;
		}
		
		$p = $this->Request->Post();
		$email = $p->Email;
		
		if (!empty($email)) {
			$table = new UsersTable();
			if (!$p->isFormat('Email', 'email', $msg)) SiteRequest::setSessionData('formerror', array('msg' => "<span class=\"formerror\">".$msg."</span>", "field" => "email"), true);
			if (!$p->isFormat('Password', 'password', $msg)) SiteRequest::setSessionData('formerror', array('msg' => "<span class=\"formerror\">".$msg."</span>", "field" => "password"), true);
			else {
				//structure should be [domain].[extension], but [extension] can have
				if ( $x = $table->authenticate($p->Email,$p->Password)) {
					//logged in
					
					FernIdentity::setIdentity( $x );
					//this is where we will do the special stuff
					$this->callback( $x );
				} else {
					$this->set('error', 'Invalid Password');
				}
				
			} //end else
		}
		$viewFiles = $this->helpers()->Themer->build(__FUNCTION__);
		$this->getCompoundView( $viewFiles );
	}
	
	function create() {
		$viewFiles = $this->helpers()->Themer->build(__FUNCTION__);
		$this->getCompoundView( $viewFiles );
	}
	
	private function makeURL( $callbackURL ) {
		if ($callbackURL == NULL || empty($callbackURL)) throw new SSOError('nocallback');
		$data = parse_url( $callbackURL );
		if ($data == false) {
			throw new SSOError('badurl');	
		}
		
		$url = '';
		if (!$data['scheme']) $url .= "http://";
						else  $url .= $data['scheme']."://";
		
		if (!$data['host']) {
			//get the hostname from specified settings
				
		} else $url .= $data['host'];
		if ($data['path']) $url .= $data['path'];
		
		$url .= "?";
		//$url .= "short_token=".$token."&";
		#die(urlencode("http://91ferns.com?callback=hey&shortcode=asdasd"));
		
		if ($data['query']) $url .= $data['query'];
		
		return $url;
		
	}
	
	private function callback( $x ) {
		//we are now logged in
		//create the get parameter
		//we need to have the App ID first each app has different parameters.
		$token = base64_encode((string)($x));
		$short_token = dechex(mt_rand());
		
		$ch = curl_init();
		curl_setopt_array( $ch, array(
			CURLOPT_POST => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS => json_encode(array(
				"token" => $token,
				'ip_client' => $_SERVER['REMOTE_ADDR'],
				'agent' => $_SERVER['HTTP_USER_AGENT'],
				'short_token' => $_REQUEST['uid'], 
			)),
			CURLOPT_URL => "http://91ferns.com/sso",
			CURLOPT_HEADER => 0,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			)
		));
		
		$ret = curl_exec( $ch );
		header("Location: " . $this->makeURL($_REQUEST['callback']) );
		exit;
	}
	
}

class SSOError extends Exception {
	
	
		
}