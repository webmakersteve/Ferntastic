<?php

if (!function_exists('Fn')) exit;

class Switcher {

	private $file_array=array();

	public function __construct() {

		$expl = explode("/",$_SERVER["REQUEST_URI"]);
		
		foreach( $expl as $k=>$v ) {
			if ($v=="") unset($expl[$k]);	
		}
		
		$this->file_array=$expl;
		return $this;
		
		
	}
	
	private $routed=false;
	
	public function reset() {
		$this->routed=false;
		return $this;
	}
	
	public function route( $condition, $callback ) {
	
		//$condition can either be a function,string, or array
		//condition may be a boolean. If it is true it must be executed provided the page hasn't been previously routed
		if (!$this->routed && $condition===true) {
			$accepted = true;
		} else $accepted=false;
		
		if (!$accepted):
			if (is_array($condition)) {
				
				//Array()
				if (count($condition) != count($this->file_array)) {} else {
					
					foreach ($condition as $k=>$v) {
						if (isset($this->file_array[$k])) {
							if ($this->file_array[$k]!=$v) return $this;
						} else return $this;
					} //endforeach
					
					$accepted=true;
					
				}
			
			} elseif (is_callable($condition)) {
				
				$accepted=$condition($this->file_array);
			
			} else {
				
				$condition = (string) $condition;
				$accepted=($_SERVER['REQUEST_URI']==$condition)?true:false;
			
			}
		endif;
		//now callback
		
		if ($accepted && is_callable( $callback )) {
			
			$data = (object) array("file_arr"=>$this->file_array);
			
			$callback( $data );
			$this->routed=true;
		}
		
		return $this;
	
	}


}

Fn::add('switcher', new Router());

?>