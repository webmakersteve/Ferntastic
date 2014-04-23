<?php

namespace Ferntastic\Routing;

use Ferntastic\HTTP\Request;
use Ferntastic\MVC\Controller\Controller;
use Ferntastic\MVC\Model\Model;


class Dispatcher {
	
	protected function findClassAndCreate( $name, $suffix = 'Controller' ) {
//		var_dump(get_declared_classes());  //awesome function right here. May potentially be able to just search the array
		if (class_exists( $name ))
			return new $name;
		elseif (class_exists( $new = $name.ucwords($suffix) ))
			return new $new;
		elseif (class_exists( $new = $name.strtolower($suffix) ))
			return new $new;
		elseif (class_exists( $new = ucwords($suffix).$name ))
			return new $new;
		elseif (class_exists( $new = strtolower($suffix).$name )) 
			return new $new;
		else return false;
	}
	
	protected function findMethodAndCall( $method, &$obj ) {
		if (method_exists( $obj, $method )) return call_user_func_array( array($obj, $method), $this->methodArgs );
		elseif (method_exists($obj, 'index')) {
			//if the index method exists this may be being used as a catch all
			
			return call_user_func_array( array($obj, 'index'), $this->methodArgs );
		} else return false; //other naming conventions here?	
	}
	
	protected $methodArgs = array();
	
	function dispatch ( Request $request=null, $response=null ) {
		
		/*
		 * This is where naming conventions become important
		 * Essentially, we have the root, which is going to be defined here
		 *
		 */
		
		Router::addHostnames( array(
			'parentepainting.com' => 'ingenuity',
			'admin.patsys.com' => 'patsys'
		));
		Router::addRoutes( array(
			'ingenuity' => 'apps/g/ingenuity',
			'patsys' => 'apps/g/patsys',
			'apps/%1' => 'apps/g/%1',
			'this/%2/shows/%1/backwards' => '%1/%2'
		));
		
		//first check the routes to see if there is a rule to where this goes
		$routes = Router::getRoutes(new Request());
		$this->methodArgs = Router::getMethodArgs();
		
		$topLevelRequest = $routes[0];
		$secondLevelRequest = $routes[1];
		
		//now check the MVC controllers folder for properly named files
		$obj = Controller::findController( $topLevelRequest );
		if ($obj === false) {
			//CONTROLLER NOT FOUND
			$obj = Controller::findController( 'default' );
			$obj -> loadModels();
			$obj -> loadHelpers();
		} else {
			//check to see if there is a class that exists
			/** okay if we have gotten this far it means we need to now load the models */
			$obj->loadModels();
			//now the method
			//we need to make sure the method exists and all that good stuff
			
			//helpers time
			$obj->loadHelpers();
			
			if ($return = $this->findMethodAndCall($secondLevelRequest, $obj)) {
				//SiteRequest::killTransients();
				return true;
			} else return false;
		
		}
			
	}
		
}