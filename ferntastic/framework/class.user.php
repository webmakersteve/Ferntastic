<?php

if (!function_exists('Fn')) exit;

class FernIdentity {
	
	const PASS_SALT = "shaaaaaa";
	const MD5_SALT = "preMD5";
	private static $currentUser;
	
	public static function encryptPassword( $PASSWORD ) {
		return sha1( self::PASS_SALT . md5( self::MD5_SALT . $PASSWORD ) );
	}
	
	private static $identityLoaded = false;
	
	public static function getIdentity( $db=NULL ) {
		if (!self::$identityLoaded) {
			$hasIdentity = self::loadIdentity( $db );
			self::$identityLoaded = true;
		} else {$hasIdentity = (self::$currentUser) ? self::$currentUser : false;}
		if ($hasIdentity) return self::$currentUser;
		else return false;
		
	}
	
	protected static function loadIdentity( ) {
		$UserTable = new UsersTable();
		$c = new SiteRequest();
		$c = $c->Cookies();
		$ustr = $c->get(FernFigure::LOGIN_COOKIE_NAME);
		$user = $c->get(FernFigure::LOGIN_COOKIE_SAVE);
		$user = base64_decode($user);
		if ($User = $UserTable->authenticate( $user, $ustr, true )) {
			/*if ($time = $c->get(FernFigure::LOGIN_COOKIE_REMEMBER) > 0) {
				SiteResponse::setCookie(FernFigure::LOGIN_COOKIE_NAME, $User->getUStr());
				SiteResponse::setCookie(FernFigure::LOGIN_COOKIE_SAVE, base64_encode($email));
			}*/
			self::$currentUser = $User;
			return true;
		} else {
			SiteResponse::removeCookie(FernFigure::LOGIN_COOKIE_NAME);
			SiteResponse::removeCookie(FernFigure::LOGIN_COOKIE_REMEMBER);
			return false;		
		}
	}
	
	private static function saveSession() {
	#	echo 'saving session';
	}
	
	public static function setIdentity( User $User, $Persistent = false ) {
		if ( $Persistent ) {
			$time = time()+(60*60*24*31*12);
			SiteResponse::setCookie(FernFigure::LOGIN_COOKIE_REMEMBER, $time, $time);
		} else $time = 0;

		SiteResponse::setCookie(FernFigure::LOGIN_COOKIE_NAME, $User->getUStr(), time()+8640000);
		SiteResponse::setCookie(FernFigure::LOGIN_COOKIE_SAVE, base64_encode($User->getEmail()), time()+8640000); //100 days saved
		self::$currentUser = $User;
	}
		
}
Fn::add( 'Identity', new FernIdentity() );
