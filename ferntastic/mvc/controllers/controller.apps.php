<?php

class AppController extends Controller {
	
	private function buildGlobalAppData() {
		
		//a lot of interesting stuff here. For example, using the model
		//we can get a bunch of the apps and their configuration files
		//then we load data about them to build navigation blocks.
		//We can then use the blocks given in the folders
		//to create the menus from the given blocks
		//NEAT HUH? ITS MVC!
		
		
			
	}
	
	function index( ) {
		$this->redirect("/", 403);
	}
	
	function _( $arg ) {
		$this->g( $arg );	
	}
	
	function g( $appName ) {
		if (!isset($appName) or empty($appName)) {
			$this->index();
			return;	
		}
		$LastRequest = func_get_args();
		
		if (!$app = $this->App->find( $appName )) {
			return false; //no app found	
		} else {
			//we got the app data
			//from the config we need to somehow get this to run? I guess? Let's get the controller I guess?
			//not dealing with naming conventions so lets just see what it adds to the namespace
			$classes = get_declared_classes();
			if (!safe_include($app->path.DS.$app->config->controller)) return false; //app is broken
			$newClasses = array_diff( get_declared_classes(), $classes);
			//just gonna take the first one I guess?
			$theClass = reset($newClasses);
			$AppObject = new $theClass;	
			
			$AppObject->config( $app->config );
//			$AppObject->loadHelpers($app);
			//let's find out what it uses
			if (isset($AppObject->uses) and !empty($AppObject->uses)) {
				if (!isset(Fn()->helper)) Fn::add('helper', new Helpers());
				foreach ($AppObject->uses as $module) {
					//this is where we need to include them as helpers
					//i know one is mysql so let's just do that
					if (strtolower($module) == 'mysql' and isset($app->config->connections->MySQL)) {
						$AppObject->db = new MySQLEngine(
							$app->config->connections->MySQL->host,
							$app->config->connections->MySQL->user,
							$app->config->connections->MySQL->password,
							$app->config->connections->MySQL->database
						);
					} else {
						$classes = get_declared_classes();	
						if (Fn()->helper->load( $module )) { //this ensures it is loaded
							$newClasses = array_diff( get_declared_classes(), $classes);
							if (class_exists($theClass = ucwords($module))) {
								$Helper = new $theClass;
								$Helper->config = $app->config;
								if ($AppObject instanceof WebApp) $AppObject->setHelper($theClass, $Helper);
								else $AppObject->$theClass = $Helper;
							}
						}
					}
				}
				//now we will load the method named after the class
				unset($LastRequest[0]);
				reset($LastRequest); //get rid of the object name in there
				$params = (is_array($LastRequest) and count($LastRequest) > 0) ? $LastRequest : array();
				if (method_exists( $AppObject, 'index' )) {
					call_user_func_array( array($AppObject,'index'), $params );
					return true;
				} elseif (method_exists( $AppObject, 'default' )) {
					call_user_func_array( array($AppObject,'default'), $params );
					return true;
				} else {
					return false;	
				}
				
			}
			
			//all helpers have been loaded at this point
			//do we want to load the individual method or give it to the app to deal with? i think the first
			
		}
		
	}
	
		
}