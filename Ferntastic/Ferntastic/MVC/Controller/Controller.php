<?php

namespace Ferntastic\MVC\Controller;

/**
 * Controllers deal with the controlling of the models and views.
 *
 * For example, a controller says given these circumstances we will undergo this programming logic
 *
 * The model itself has no logic but is a way to separate DATA from the part of the program that
 * deals with the controlling of that data
 *
 * The controller then passes its interpretation to the view which deals with little to no data manipulation
 * controllers output their data in JSON and a JSON object
 */
 
use Ferntastic\Files\Includer;
use Ferntastic\Formatting\Strings as Formatter;

use Ferntastic\MVC\Model\Model;
 
class Controller {
	public static $contollersLoaded = array();
	protected $Request;
	private $miscVars = array();
	
	function __construct(  ) {
		$this->Request = Fn()->getServiceLocator()->get('Request');
	}
	
	function __set ( $name, $value ) {
		$this->miscVars[$name] = $value;
	}
	
	function __get ( $name ) {
		return $this->miscVars[$name];	
	}
	
	final public static function findController( $name ) {
		$suffix = "controller";
		foreach( array($name, ucwords($name), strtolower($name), strtoupper($name)) as $topLevelRequest ) {
			$continue = true;
			if (Includer::safeInclude( Fn()->getApplication()->getModulePath() . DS . 'Controllers' . DS . $x = $topLevelRequest.'Controller.php')) {
			} elseif (Includer::safeInclude( Fn()->getApplication()->getModulePath() . DS . 'Controllers' . DS . $x = $topLevelRequest.'controller.php')) {
			} elseif (Includer::safeInclude( Fn()->getApplication()->getModulePath() . DS . 'Controllers' . DS . $x = $topLevelRequest.'.php' )) {
			} elseif (Includer::safeInclude( Fn()->getApplication()->getModulePath() . DS . 'Controllers' . DS . $x = Formatter::removeTrailingS($topLevelRequest).'Controller.php' )) {
			} elseif (Includer::safeInclude( Fn()->getApplication()->getModulePath() . DS . 'Controllers' . DS . $x = Formatter::removeTrailingS($topLevelRequest).'controller.php' )) { 
			} else $continue = false;
			
			if ($continue) {
				//get rid of extensions
				$class = str_replace(".php", "", $x);
				$class1 = "\Module\Controllers\\".$class;
				$class2 = "\Module\Controllers\\".ucwords($class);
				if (class_exists( $class1, false )) {
					return new $class1;
				} elseif (class_exists( $class2, false )) {
					return new $class2;
				}
			}
		} //endfirstforeach
		return false;
	}
	
	final protected function getView ( $viewPath ) {
		
		//we are going to include the file in here
		if (file_exists($viewPath)) {
			//let's set the variables we need
			extract( $this->viewSets );
			if (!include( $viewPath )) return false;
			return true;
			
			//yay we're good
				
		}
		
			
	}
	
	final protected function getCompoundView( /* mixed params */ ) {
		if (func_num_args() < 1) return;
		if (!is_array(func_get_arg(0))) {
			$args = func_get_args();	
		} else {
			$args = func_get_arg(0);	
		}
		if (is_array($args) and count($args) > 0) {
			foreach ( $args as $viewPath ) {
				$this->getView ( $viewPath );	
			}
			return true;
		}
		return false;
	}
	
	protected $viewSets = array();
	final protected function set( $variableName, $variableValue ) {
		$this->viewSets[$variableName] = $variableValue;
	}
	
	final public function loadHelpers() {
		if (isset($this->uses) and !empty($this->uses)) {
				if (!isset(Fn()->helper)) Fn::add('helper', new Helpers());
				foreach ($this->uses as $module) {
					$module = strtolower($module);
					//this is where we need to include them as helpers
					//i know one is mysql so let's just do that
					if ($module == 'mysql' and isset($this->config->connections->MySQL)) {
						$this->db = new MySQLEngine(
							$app->config->connections->MySQL->host,
							$app->config->connections->MySQL->user,
							$app->config->connections->MySQL->password,
							$app->config->connections->MySQL->database
						);
					} else {
						$classes = get_declared_classes();	
						if (Fn()->helper->load( $module )) { //this ensures it is loaded
							$newClasses = array_diff( get_declared_classes(), $classes);
							if (_Class::isClass($theClass = ucwords($module))) {
								$Helper = new $theClass;
								if ($this->config) $Helper->config = $this->config;
								if ($this instanceof WebApp) $this->setHelper($theClass, $Helper);
								else $this->$theClass = $Helper;
								
							}
						}
					}
				}	
		}
	}
	
	final private function findClassAndCreate( $name, $suffix = 'Controller' ) {
//		var_dump(get_declared_classes());  //awesome function right here. May potentially be able to just search the array
		if (_Class::isClass( $name ))
			return new $name;
		elseif (_Class::isClass( $new = $name.ucwords($suffix) ))
			return new $new;
		elseif (_Class::isClass( $new = $name.strtolower($suffix) ))
			return new $new;
		elseif (_Class::isClass( $new = ucwords($suffix).$name ))
			return new $new;
		elseif (_Class::isClass( $new = strtolower($suffix).$name )) 
			return new $new;
		else return false;
	}
	
	final public function loadModels() {
		if (isset($this->models)) {
			//this means it has the models it wants to load in here	
			foreach( $this->models as $model ) {
				$theModel = Model::findModel( $model );
				if ($theModel) {
					$modelClass = get_class($theModel);
					$this->$modelClass = $theModel;	
				}
			}
		} else {
			//it is not telling us what models it wants to load
			//we need to now guess
			
			$modelCheck = trim(preg_replace("#controller$#i", "", get_class($this)));
			$model = Model::findModel( $modelCheck );
			if ($model === false) {
				//couldnt find it
				return false;
			} else {
				//woohoo. we should have the model loaded now
				//okay we now have a model
				//just give it to the controller
				$modelclass = get_class($model);
				$t = ucwords($modelclass);
				$this->$t = $model;
				return true;
			} //no trailing else because it is a soft problem
			
		} //end if models is set
		
	}
	
	public function redirect( $newLocation, $errorCode=403 ) {
		SiteResponse::redirect( $newLocation, $errorCode );
	}
	
	function __destruct() {
		if (count($this->viewSets) > 0) SiteRequest::killTransients();	//perhaps it is safe to assume that if there was a view set in a controller a view was loaded. but we will see
	}
	
}

