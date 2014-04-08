<?php

/**
 * MySQL extension lib. This file controls the fetching of information from the database and the road the information takes.
 * 
 * This extension lib includes the MySQL layer abstraction for use within the website in a more streamlined fashion
 * as well as security measures to secure databases and the backup engine to save the data when the query alters the table.
 * This is used as a precaution against attacks, for the DB/Tbl that will be used will have higher priviledges (possibly read-only)
 * It will allow the restoration of all the data very easily, just by extracting it and executing it.
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.3
 *
 */
 
namespace Ferntastic\Drivers\Database;
 
use Ferntastic\Errors\NoLogError;
use Ferntastic\Drivers\Database\Schema\Driver as DatabaseDriver;
use Ferntastic\Errors\DatabaseError;
 
/**
 * MYSQL Exception class.
 */

class MySQL implements DatabaseDriver {
	
	protected $usingTable = NULL;
	protected function checkIfTableSet() {
		if ($this->usingTable === NULL) throw new DatabaseError( ERR_MYSQL_NO_TABLE_SELECTED );	
	}
	
	
	public function Insert( $Values ) {
		$this->checkIfTableSet();
			
		$SQL = "INSERT INTO `%s` (%s) VALUES (%s)";
		
		$k = array();
		$v = array();
		
		foreach( $Values as $Key => $Val ) {
			$Val = $this->Escape($Val);
			if (is_string($Val)) $v[] = "'" . $Val . "'";
			else $v[] = $Val;
			$k[] = $this->Escape($Key);
		}
		
		$SQL = sprintf( $SQL, $this->usingTable, implode(',', $k), implode(',' , $v) );
		$this->last_sql =  $SQL;
		
		$this->Execute( $SQL );
		return $this->InsertID();
		
	}
	public function Escape($x) {return $x;}
	public function Delete( $Conditions ) {
		$this->checkIfTableSet();
	}
	public function Find( $Conditions ) {
		$this->checkIfTableSet();
	}
	public function Update( $Conditions, $Changes ) {
		$this->checkIfTableSet();
	}
	
	public function UseCollection( $Collection ) {
		$this->usingTable = $Collection;
	}
	
	public function Connect( $Parameters ) {
		
		$user = $Parameters['Username'] ? $Parameters['Username'] : null;
		$host = $Parameters['Host'] ? $Parameters['Host'] : null;
		$password = $Parameters['Password'] ? $Parameters['Password'] : '';
		$db = $Parameters['Database'] ? $Parameters['Database'] : null;
		
		if (!$host || !$db || !$user ) {
			throw new DatabaseError( ERR_MYSQL_NO_CONNECTION_STRING );	
		}
		
		$this->MySQLConnect( $host, $user, $password, $db );
		
	}
	/**
	 * Holds the db Object for use in the objective MySQL Abstraction
	 * @name $db_object
	 */
	
	private $db_object = false;
	private $db_extensions = array();
	
	/**
	 * Holds the lastly executed SQL as long as the query object is used.
	 * @name $last_sql
	 * @global string $GLOBALS['last_sql']
	 */
	
	public $last_sql = '';
	
	private function MySQLConnect( $db_host, $db_user, $db_password, $db_name ) {
		$db_object = @mysqli_connect( $db_host, $db_user, $db_password, $db_name );
		if ($db_object && mysqli_error( $db_object ) != "")
			$this->db_object = $db_object;	
		else throw new DatabaseError( ERR_MYSQL_CONN_ERROR, $db_object ? mysqli_error( $db_object ) : array() );
	}
	//end configuration
	
	function override_default_connection( $dbname = null, $dbuser = null, $dbpassword = null, $dbhost = null ) {
		
		if ($dbname == null) $dbname = self::$db_name;
		if ($dbuser == null) $dbuser = self::$db_user;
		if ($dbpassword == null) $dbpassword = self::$db_password;
		if ($dbhost == null) $dbhost = self::$db_host;
		
		mysqli_close( $this->db_object );
		$this->db_object = mysqli_connect( $dbhost, $dbuser, $dbpassword, $dbname );
		return $this->db_object;
		
	}
	
	function add_connection( $reference, $dbname = null, $dbuser = null, $dbpassword = null, $dbhost = null ) {

		if ($dbname == null) $dbname = self::$db_name;
		if ($dbuser == null) $dbuser = self::$db_user;
		if ($dbpassword == null) $dbpassword = self::$db_password;
		if ($dbhost == null) $dbhost = self::$db_host;
		
		if (isset( $this->db_extensions[$reference] )) mysqli_close( $this->db_extensions[$reference] );
		$this->db_extensions[$reference] = mysqli_connect( $dbhost, $dbuser, $dbpassword, $dbname );
		return $this->db_extensions[$reference];
	}
	
	function use_connection( $connID ) {
		if (isset( $this->db_extensions[$connID] )) {
			$this->db_object = $this->db_extensions[$connID];
			return $this->db_object;
		} else return false;
	}
	
	function is_connected( ) {
		if (isset($this->db_object) and !$this->db_object->connect_error) return true;
		else return false;	
	}
		
	/**
	 * Query the MYSQL database. Abstraction for mysqli_query
	 *
	 * @param $sql The SQL statement to be executed
	 * @return Returns false on failure, or query identifier on success
	 *
	 */
	
	public $last_query = '';
	
	function query( $sql ) {
		
		if (!isset($this->db_object) or !$this->db_object) {
			throw new DatabaseError('nodbobj');
		} else {
			
			$q = mysqli_query( $this->db_object, $sql );
			$this->last_sql = $sql;
			if (!$q) throw new DatabaseError( ( $e = mysqli_error( $this->db_object ) ) ? $e : null );
			else {
				$this->last_query = $q;
				return $q;
			}
		}
		
	}
	
	/** 
	 *
	 *
	 */
	 
	function insert_id() {
		
		return mysqli_insert_id( $this->db_object );
		
	}
	
	/** 
	 *
	 *
	 */
	
	function affected_rows() {
		
		return mysqli_affected_rows( $this->db_object );
			
	}
	
	function num_rows( $q = null ) {
		
		if ($q == null) {
			$q = isset($this->last_query) ? $this->last_query : false;
		}
		
		if ($q) return mysqli_num_rows( $q ); else return false;
		
	}
	
	function assoc( $q = null ) {
		
		if ($q == null) {
			$q = isset($this->last_query) ? $this->last_query : false;
		}
		
		if ($q) return mysqli_fetch_assoc( $q ); else return false;
		
	}
	
	function arr( $q = null ) {
		
		if ($q == null) {
			$q = isset($this->last_query) ? $this->last_query : false;
		}
		
		if ($q) return mysqli_fetch_array( $q ); else return false;
		
	}
	
	
	/**
	 * Checks if the given MySQL table exists. Only necessary for some cases.
	 * 
	 * @param $tbl_name The name of the table to be loaded.
	 * @return boolean Returns true if the table exists, false otherwise
	 */
	
	function table_exists( $tbl_name ) {
		
		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '".DB_NAME."' AND table_name = '".$tbl_name."'";
		$q = $this->query($sql);
		
		if ($this->num_rows($q) > 0) return true; else return false;
			
	}
	
	/**
	 * Lookup function
	 *
	 * Lookup function simplifies getting MySQL data from another table
	 *
	 */
	 
	public static function lookup( $table, $id, $stringkey, $lookupkey = "id" ) {
	
		$keys = array();
		$keystring = '';
		$temp = $stringkey;
		
		while (preg_match("#(?P<full>\[(?P<key>[^]]+)\])#i", $temp, $matches)) {
			//print_r($matches);
			$keystring.=$matches['key'].",";
			$keys[] = $matches['key'];
			$temp = str_replace($matches['full'], "", $temp);
			
		}
		
		$keystring = preg_replace("#[,]+$#", "", $keystring);
	
		$sql = "SELECT %s FROM `%s` WHERE %s = '%s'";
		$sql = sprintf($sql, $keystring, $table, $lookupkey, $id);
		query($sql);
		if (num_rows() > 0):
		
			$row = assoc();
			$ret = $stringkey;
			
			foreach ($row as $k=>$v) {
				$ret = str_replace( "[".$k."]", $v, $ret );
			}
		
		else: false; /*throw new DatabaseError(0);*/ endif;
		return $ret;
		
	}
	
	/**
	 * Escape function
	 *
	 * @param $val The value to be escaped
	 * @return Returns the escaped string
	 *
	 */
	
	function e( $val ) {
		
		$val = (string) $val;
		if (!$this->db_object) throw new NoLogError('nodbobj');
		return mysqli_real_escape_string( $this->db_object, $val );
		
	}

}

/**
 * Holds the DB Object for use in the procedural MySQL Abstraction
 * 
 * This Backup class is used to backup SQL to the database. It can also load backup data and revert what has been undone as long as it is
 * of the Backup data class type
 *
 * @abstract Loads backup data from MYSQL fBackup table
 * @staticvar boolean $operable determines whether or not the class should execute its functions. This is determined on the construction of the first class.
 * @staticvar integer Instances of currently open Backup engines.
 */

class MySQLBackup {
	
	static $operable; //is it operable?
	static $instances = 0; //number of open instances
	
	const TBL = 'fBackup';
	
	/**
	 * Constructor class has no Parameters.
	 * 
	 * @return True on successful creation, false on failure
	 */
	
	function __construct() {
		
		self::$instances = $working = table_exists( self::TBL );
		
		try {
			
			if (!$working) {
				throw new DatabaseError("Can't write to `fBackup`. Does it exist?");
			}
			
			return true;
		
		} catch (DatabaseError $e) {
			$e->handleme();
			return false;	
		}
		
	}
	
	/**
	 * This method formats a SQL statement for backup. It parses the statement with REGEX to learn what it is trying to do.
	 *
	 * @param $sql The statement to back up
	 * @return mixed The formatted SQL statement array on successful creation, false on failure or if it can't be backed up
	 */
	
	private function formatSQL( $sql ) {
		
		if (!self::$instances) return false;
		
		$sql = preg_replace( "#(low_priority|ignore|temporary|delayed|into)#i", "", $sql ); //get rid of keywords we don't care about
		
		$return = array();
		$words = split(' ', strtolower($sql)); //the words array now contains every word in the statement.
		
		if ( count($words) < 1 ) return false;
		
		$type = $words[0]; //the first word will be the type of statement it is. We don't want to back this up if it is a select statement.
		if ( $type == "select" or $type == "insert" ) return false;
		
		//as of here, it is not a select statement. If it is an UPDATE statement, we need to back-up the old data for the column that is being updated
		$return['SQL'] = $sql;
		
		if (($temp = array_search("where", $words)) != null) { //if there is a condition
					
			//there is a condition. Extract it. The index it stops at will either be the end, ORDER or LIMIT
			$start = $temp;
			$end = count($words);
			
			//see if we need to change the end.
			if ( ($temp = array_search("order", $words)) != null) {
				$end = $temp;
			} elseif ( ($temp = array_search("limit", $words)) != null) {
				$end = $temp; 
			}
			
			$condition = array();
			for ($i=(int) $start; $i<$end; $i++) {
				$condition[] = $words[$i];
			}
			
			$condition = implode(' ', $condition);
			
			$return['CONDITION'] = $condition;
			
		}
		
		switch ($type) {
			
			case 'update':
				
				$table = $words[1];
				$return['TYPE'] = "UPDATE";
				
				//we need to get the backup SQL for the update. Only get the rows we need.
				if (!isset($condition) or !$condition) $condition = '';

				//what are we selecting?
				//this will start after the SET statement and END with WHERE, ORDER BY, or LIMIT, or END in that order
				$end = count($words);
				if ( ($temp = array_search("where", $words) ) !== null) $end = $temp;
				elseif ( ($temp = array_search("order", $words) ) !== null) $end = $temp;
				elseif ( ($temp = array_search("limit", $words) ) !== null) $end = $temp;
				
				//now we have the end let's put the setting together
				$start = array_search("set", $words) +1;
				$sets = array();
				for ($i=$start; $i<=$end; $i++) $sets[] = $words[$i];
				
				$sets = implode(' ', $sets); //now we need to expload them by commas
				$settables = array();
				$sets = explode(',', $sets);
				
				$select = '';
				
				foreach ($sets as $setting) {
					
					$setting = preg_replace("# *= *#", "=", $setting); //format the = sign with preg_replace
					$temp_words = explode("=", $setting); //now explode it by word
					//first word is the column, second is the table
					$select .= $temp_words[0].',';
					
				}
				
				$select = substr($select, 0, strlen($select)-1);
				$sql = sprintf( "SELECT %s FROM %s %s", $select, $table, $condition );
				
				$return['BACKUP_DATA'] = assoc( query( $sql ) );
			
			break;
			
			case 'delete':
			
				$table = $words[array_search("from", $words)+1];
				$return['TYPE'] = "DELETE";
				
			
			break;
			
			case 'drop':
			
				$table = $words[2];
				$return['TYPE'] = "DROP";
			
			break;
			
			default:
			
				$table = "unknown";
				$return['TYPE'] = $words[0];
				
			
			break;
			
		}
		
		$return['TABLE'] = $table;
		
		return $return;
		
	}
	
	/**
	 * This is the public method of the back up class. Executing this upon a SQL statement will back up its previous contents
	 *
	 * @access public
	 * @param $sql The statement to back up
	 * @return boolean The value of the query, which is true or false, to determine whether it was successful or not.
	 */
	
	public function backupStmt( $sql ) {
		
		$arr = $this->formatSQL( $sql ); //run the SQL parsing
		$serialized = serialize($arr); //serialize the parsed SQL for entry into the database
		
		$sformat = 'INSERT INTO %s (data) VALUES ("%s")';
		$sql = sprintf($sformat, self::TBL, e( $serialized ));
		
		$q = query( $sql ); //enter it in
		
		return $q; //return the result
			
	}
}