<?php

namespace Ferntastic\MVC\View;

/**
 * Views deal with the controlling of the models and views.
 *
 * For example, a controller says given these circumstances we will undergo this programming logic
 *
 * The model itself has no logic but is a way to separate DATA from the part of the program that
 * deals with the controlling of that data
 *
 * The controller then passes its interpretation to the view which deals with little to no data manipulation
 * controllers output their data in JSON and a JSON object
 */
 
class View {
	public static $contollersLoaded = array();
	
	private $miscVars = array();
	 
	function __construct(  ) {
		
	}
	
	function __set ( $name, $value ) {
		return;
	}
	
	function __get ( $name ) {
		return $this->miscVars[$name];	
	}
	
	final public function getView ( $viewPath ) {
		
		//we are going to include the file in here
		if (file_exists($viewPath)) {
			//let's set the variables we need
			extract( $this->viewSets );
			if (!include( $viewPath )) return false;
			return true;
			
			//yay we're good
				
		}
		
			
	}
	
	final public function getCompoundView( /* mixed params */ ) {
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
	final public function set( $variableName, $variableValue=null ) {
		if ($variableValue!==null)
		$this->viewSets[$variableName] = $variableValue;
		elseif (is_array($variableName) && count($variableName) > 0) {
			foreach( $variableName as $k=>$v) $this->viewSets[$k]=$v;
		} else return;
	}
}