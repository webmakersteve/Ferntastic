<?php

/**
 * Error logging functions
 * This file will edit how errors will be handled and integrated with database and mail. Any LogError will be automatically handled after it is thrown.
 * @author Stephen Parente <sparente@91ferns.com>
 * @version 0.1
 * @package php_extensions
 */

namespace Ferntastic\Errors;

/**
 * NoLogError is similar to the standard exception class except that it has the handle method.
 *
 * Use the handleMe() method to only set the global var to the error text.
 */

class NoLogError extends \Exception {
	
	private $type = __CLASS__;
	protected $data, $class;
	public $description;
	
	/**
	 * Only sets the global variable to the message text.
	 * @global $errormsg Used to set the error text to for use in other pages.
	 */
	
	public function handleMe() {
		$l = $_SERVER['DOCUMENT_ROOT']."/errorlog.log";;
		$_SESSION['errormsg'] = $this->description;
		return @$this->log_to_file($l);
		
	}
	
	public function log_to_file( $path=null ) {
		
		if ($path==null) {
			$path=dirname( dirname(__FILE__) ).DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR."errorlog.log";
		}
		if ( !is_dir( dirname($path) ) ) {
			mkdir( $path, 0777, true );
		}
		
		$handle = fopen( $path, "a" );
		if ($handle===false) return false;
		
		$text = sprintf("%s\t\t%s\t%s\t%s\n", ($this->type), $this->message,  (json_encode( $this->data )), date('c'));
		
		fwrite($handle, $text);
		fclose($handle);
		
		return true;
		
			
	}
	
	/**
	 * Error messages are turned off for the browscap section. This is done to reduce the number of '@'s necessary, and because
	 * this information is not necessary.
	 * 
	 * @global string Used to save the error message for use in all pages.
	 * @param string $message Message, like in the regular exception construct, that will be used
	 * @param integer $code The message code. It will be identified using the array above.
	 * @see LogError
	 */
	
	function __construct($res_id, $data = null, Exception $previous = null) {
		global $errormsg, $resources, $fns;
		$fns = Fn();
		if (is_object($fns) and method_exists($fns, 'load_extension')) {
			
			$fns->load_extension('resources');
			if (!isset($fns->resources->errors->$res_id)) {
				
				$this->message=$res_id." (".$this->file.":".$this->line.")";
				$this->description="Sorry. Something went wrong.";
				$this->code = 0;
				
			} else {
				
				$ed = $fns->resources->errors->$res_id;
				$this->message = ($t = $ed->attr->message) ? $t : null;
				$this->description = ($t = $ed->value) ? $t : null;
				$this->code = ($t = $ed->attr->code) ? $t : null;
			
			}
			
			$this->data = array("file" => $this->file, "line" => $this->line);
			if ($data != null) $this->data = array_merge($this->data, array("data" => $data) );
			$this->previous = $previous;
		
			return $this;
		
		} else {
			
			$this->message=$res_id." (".$this->file.":".$this->line.")";
			$this->description="Sorry. Something went wrong.";
			$this->code = 0;
			if ($data != null) $this->data = array_merge($this->data, array("data" => $data) );
			$this->data = array("file" => $this->file, "line" => $this->line);
			$this->previous=$previous;
			
			return $this;
			
		}
		//regular data
		//@$this->data['browser'] = ($g = get_browser(null, true)) ? $g : "error"; //holds all browser data
		
	}
	
}

