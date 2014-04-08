<?php

/**
 * Error logging functions
 * This file will edit how errors will be handled and integrated with database and mail. Any LogError will be automatically handled after it is thrown.
 * @author Stephen Parente <sparente@91ferns.com>
 * @version 0.1
 * @package php_extensions
 */

if (!function_exists('Fn') or !is_object(Fn())) die();

/**
 * NoLogError is similar to the standard exception class except that it has the handle method.
 *
 * Use the handleMe() method to only set the global var to the error text.
 */

class NoLogError extends Exception {
	
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
			$this->data = array("file" => $this->file, "line" => $this->line);
			$this->previous=$previous;
			
			return $this;
			
		}
		//regular data
		//@$this->data['browser'] = ($g = get_browser(null, true)) ? $g : "error"; //holds all browser data
		
	}
	
}

/**
 * LogError will extend the Exception class
 *
 * LogError will allow for logging functionality through database entry and mail functionality.
 *
 * Example:
 * <code>
 * <?php
 * 
 * $e = new LogError("Database error", 4);
 * //will both send an email and add to the database.
 * 
 * $e = new LogError("Database error", 4, false);
 * //will only add to the database.
 *
 * $e = new LogError("Database error", 4, true, false);
 * //will only email the administrator.
 * 
 * ?>
 * </code>
 */

class LogError extends NoLogError {
	
	/**
	 * @var string Contains the current class name. All extensions of this class will need to declare this independently to receive a new type. This is used in the database for error handling
	 * @access private
	 */
	
	private $type = __CLASS__;
	
	/**
	 * Formulates the body text for an error email
	 *
	 * @param boolean $html Specifies whether to return HTML or not.
	 * @global $errortypes Used to get error types from codes
	 * @global $en_de Used to translate 0 to disabled and 1 to enabled
	 * @return string Returns the email body to be used in error cases.
	 *
	 * Example:
	 * <code>
	 * <?php
	 * $e = new Exception('Error', 0);
	 * $body = codifyErrorEmail($e);
	 * mail("example@example.com", "Error", $body, "From: john@example.com");
	 * ?>
	 * </code>
	 */
	
	private function codifyErrorEmail($html = false) {
		
		/**
		 * @var integer $num Stores the number of the error to be used for error logging purposes.
		 * @var string $errormsg The message of the error to be logged and displayed on the page
		 */
		
		$num = $this->code;
		$errormsg = $this->message;
		
		//Start Body of email
		
		if ($html) {
		
		/**
		 * @var string $bodytext The body of the email. In HTML if the $ html variable is set to true. If not, it is set to Plain text.
		 */
		
		//start the HTML version
		$bodytext = '';
		
		}
		
		//end body of email
		return $bodytext;
		
	}
	
	/**
	 * Emails the server administrator about a problem
	 *
	 * First the emailing function checks for the admin address in the 
	 *
	 * @param string|boolean $subject The subject of the email. Defaults to "Error at " timestamp
	 * @param string|object|boolean $text The body text.
	 * @global $adminemailaddr Used as the sendee of the email
	 * @return boolean Tells whether it worked or not
	 */
	
	private function mailAdmin($subject=false) {
		
		/*
		 * @var string $adminemailaddr The email address of the admin set in the config file.
		 */
		
		$adminemailaddr = ( defined('ADMIN') ) ? ADMIN : "sparente@91ferns.com";
		
		/**
		 * @var string $text The text string that stores the email
		 */
		
		$text = $this->codifyErrorEmail(false);
		$file = $this->getFile();
		$line = $this->getLine();
			
		/**
		 * @var object $mail PHPMailer class for emailing.
		 */
		
		$mail = mail( $adminemailaddr, $subject, wordwrap($text, 50, "\n"), "From: errors@91ferns.com" );
		
		if(!$mail) {
		
			throw new NoLogError("nomail"); //If mailing fails, the script will exit.
			return false;
			
		} else return true;
		
	}
	
	/**
	 * Adds errors caught to the error log.
	 * 
	 * If it fails, it will throw an UNLOGGABLE exception. This exception will not be logged and will just give an error to the user. 
	 *
	 * @global $errortypes The array storing the error types to be identified.
	 * @return boolean Tells whether it worked or not in adding to the database.
	 */
	
	private function addToLog() {
		
		global $errormsg;
		
		/**
		 * @var string $sformat The format to be used to insert the information into the database
		 */
	
		$sformat = "INSERT INTO log (type, variables, timestamp, description) VALUES ('%s', '%s', %d, '%s')";
		
		/**
		 * @var string $sql The Sql statement to enter the error into the database
		 */
		 
		$sql = sprintf($sformat, e($this->type), e(serialize( $this->data )), time(), e($this->message));
		$query = query($sql);
		
		$errormsg = $this->description;
		
		return true;
		
	}
	 
	
	/**
	 * Assigns the global variable "errormsg" to the current message and initiates logging.
	 *
	 * Assigns the Global Variable $ errormsg to the current message of the error and adds the error information to the database log.
	 *
	 * @global string The error message that is obtained from this Exception
	 * @param boolean $db Whether or not to add the information to the Database log.
	 * @param boolean $mail Whether or not to email the information.
	 */
	
	public function handleMe($db = true, $mail = true) {
		
		$conf = Fn()->conf ? Fn()->conf : false;
		
		try {
			
			if ($conf and $conf->get('E_REP_DB') && $db) { //if the conf file says to enter the info into the database
				$this->addToLog(); //if the parameter DB is set to true
			}
			if ($conf and $conf->get('E_REP_MAIL') && $mail) {
				$this->mailAdmin("Error on " . $_SERVER['PHP_SELF']);
			}
			
			$_SESSION['errormsg'] = $this->description;
			return @$this->log_to_file();
			
		} catch (NoLogError $e) {
			//this will be done of EITHER addToLog or MAIL functions throw an exception. Things need to be done depending on which exception was thrown.

			$e->handleMe();
			return false;
		}
		
	}
	
}

class Sessions {
	
	function set_error($val) {
		$_SESSION['errormsg'] = $val;
		$_SESSION['lastpost'] = serialize($_POST);
	}
	
	function last_post() {
		if (isset($_SESSION['lastpost'])):
			return unserialize($_SESSION['lastpost']);
		else:
			return false;
		endif;	
	}
	
	function clear_error() {
		$_SESSION['errormsg'] = "";
		unset($_SESSION['errormsg']);
		unset($_SESSION['lastpost']);
	}
	
	function echo_and_clear() {
		$this->echo_error();
		$this->clear_error();
	}
	
	function is_error() {
		if (isset($_SESSION['errormsg'])) return true; else return false;
	}
	
	function echo_error() {
		echo @$_SESSION['errormsg'];
	}
		
	function __set( $key, $value ) {
		$_SESSIONS[$key]=$value;
		return $value;
	}
	
	function __get( $key ) {
		
		if (isset($_SESSIONS[$key])) return $_SESSIONS[$key];
		else return;
			
	}
		
}

/**
 *
 */

Fn::add('sessions', new Sessions());

//add procedural logging functions
function log_notice( $error, $data=null ,$path=null) {
	if (ini_get('error_reporting') == E_ALL and ini_get('display_errors')) {
		echo $error;	
	}
	if ($path==null) {
			$path=dirname( dirname(__FILE__) ).DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR."errorlog.log";
		}
		if ( !is_dir( dirname($path) ) ) {
			mkdir( $path, 0777, true );
		}
		
		$handle = fopen( $path, "a" );
		if ($handle===false) return false;
		
		$text = sprintf("%s\t\t%s\t%s\t%s\n", "NOTICE", $error,  (json_encode( $data )), date('c'));
		
		fwrite($handle, $text);
		fclose($handle);
}
function log_fatal( $error, $data=null,$path=null) {
	if (ini_get('error_reporting') == E_ALL and ini_get('display_errors')) {
		echo $error;	
	}
	if ($path==null) {
			$path=dirname( dirname(__FILE__) ).DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR."errorlog.log";
		}
		if ( !is_dir( dirname($path) ) ) {
			mkdir( $path, 0777, true );
		}
		
		$handle = fopen( $path, "a" );
		if ($handle===false) return false;
		
		$text = sprintf("%s\t\t%s\t%s\t%s\n", "FATAL", $error,  (json_encode( $data )), date('c'));
		
		fwrite($handle, $text);
		fclose($handle);
}
function log_warning( $error, $data=null ,$path=null) {
	if (ini_get('error_reporting') == E_ALL and ini_get('display_errors')) {
		echo $error;	
	}
	if ($path==null) {
			$path=dirname( dirname(__FILE__) ).DIRECTORY_SEPARATOR."logs".DIRECTORY_SEPARATOR."errorlog.log";
		}
		if ( !is_dir( dirname($path) ) ) {
			mkdir( $path, 0777, true );
		}
		
		$handle = fopen( $path, "a" );
		if ($handle===false) return false;
		
		$text = sprintf("%s\t\t%s\t%s\t%s\n", "WARNING", $error,  (json_encode( $data )), date('c'));
		
		fwrite($handle, $text);
		fclose($handle);
}
