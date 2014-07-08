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
use Ferntastic\Drivers\Common\Driver;
use Ferntastic\Errors\DatabaseError;
 
/**
 * MYSQL Exception class.
 */

class MySQL extends Driver implements DatabaseDriver  {

    protected static $instance = NULL;
    protected function __construct() {

        return $this;
    }

    public static function Invoke() {
        if (self::$instance === NULL) self::$instance = new self();
        return self::$instance;
    }

    protected $usingTable = NULL;
	protected function checkIfTableSet() {
		if ($this->usingTable === NULL) throw new DatabaseError( ERR_MYSQL_NO_TABLE_SELECTED );
	}
	
	public function getColumns( $Collection ) {

        //just for now we are going to connect
        $conn = mysqli_connect( 'localhost', 'test', 'test', 'test');
        $this->Connect(array(
            'Username' => 'test',
            'Password' => 'test',
            'Host' => 'localhost',
            'Database' => 'test'
        ));


        $sql = sprintf("SHOW COLUMNS FROM %s", $this->Escape($Collection));//this may save time

        $this->query( $sql );

        $columns = array();

        while ($r = $this->assoc()) {
            $columns[] = $r['Field'];
        }

        return $columns;
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

    /**
     * Escape function
     *
     * @param $val The value to be escaped
     * @return Returns the escaped string
     *
     */

	public function Escape($val) {

        $val = (string) $val;
        if (!$this->db_object) throw new NoLogError('nodbobj');
        return mysqli_real_escape_string( $this->db_object, $val );
    }

	public function Delete( $Conditions ) {
		$this->checkIfTableSet();
	}

    /**
     * @var string $sqlBefore This is a pre-formatted SQL statement used in a sprintf function to add the parameters in.
     * @constantvar
     */

    const sqlBefore = "SELECT SQL_CALC_FOUND_ROWS %s FROM `%%s` WHERE %s %s %s";

    /**
     * Function Find uses the $stmt array to make a real SQL statement.
     *
     * This function uses the loaded statements to create a SQL statement to be executed after being filled in
     * with the table name. Uses priority first, then non priority statements.
     *
     * @return string The SQL statement with one variable %s to be used with sprintf($return, $tablename);
     *
     * @access public
     */

    public function Find( $stmts, $table=null ) {

        if ($table == null) {
            $this->checkIfTableSet();
            $table = $this->usingTable;
        }

        $sql_array = array(0=>'', 1=>'', 2=>'', 3=>''); //the first array. 4 keys correlate with 4 '%s'
        foreach ( $stmts as $pl => $v ) { //start cycling through the statements.

            $curr = &$sql_array[$pl]; //simplify
            $curr = ""; //initialize
            if ( array_key_exists("prio", $v) ) { //if there are priority keys, execute them first
                foreach ( $v['prio'] as $new ) {
                    if ($pl == 1) $curr .= $new." AND ";
                    elseif ($pl == 0) $curr .= $new.",";
                    else $curr .= $new." ";
                }
            }

            if ( array_key_exists("none", $v) ) { //If there are non-priority indexes, do them.
                foreach ( $v['none'] as $new ) {
                    if ($pl == 1) $curr .= $new." AND ";

                    elseif ($pl == 0) $curr .= $new.",";
                    else $curr .= $new." ";
                }
            }

            if ($pl == 1) $curr = preg_replace('# *AND *$#i', '', $curr); //for placement 1 we need to remove the last AND
            else $curr = preg_replace('# *[,]+ *$#i', '', $curr); //for all other placements there will be a trailing comma.
        }

        if ( empty($sql_array[1]) ) $sql_array[1] = "1 = 1"; //if there is nothing in the first SQL array, populate it with filler text
        $ret = sprintf( self::sqlBefore, $sql_array[0], $sql_array[1], $sql_array[2], $sql_array[3] ); //build the STATEMENT from the format
        $ret = sprintf($ret, $table);
        $this->query($ret);

        return $this->queryToArray();

    }

    protected function queryToArray() {
        $array = array();
        while ($r = $this->assoc()) {
            $array[] = $r;
        }
        return $array;
    }

    public function fetchTotals() {
        $sql = "SELECT FOUND_ROWS();";
        $this->query( $sql );
        $r = $this->queryToArray( );
        echo 'hey';
        print_r($r);
        exit;
        $this->total_count = $r[0];
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
		$password = $Parameters['Password'] ? $Parameters['Password'] : null;
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
		$db_object = mysqli_connect( $db_host, $db_user, $db_password, $db_name );
		if ($db_object && mysqli_error( $db_object ) == "")
			$this->db_object = $db_object;	
		else throw new DatabaseError( ERR_MYSQL_CONN_ERROR, $db_object ? mysqli_error( $db_object ) : array() );
	}
	//end configuration

    /*
     * Old Functions below
     * ====================================================================================
     */
	
	protected function override_default_connection( $dbname = null, $dbuser = null, $dbpassword = null, $dbhost = null ) {
		
		if ($dbname == null) $dbname = self::$db_name;
		if ($dbuser == null) $dbuser = self::$db_user;
		if ($dbpassword == null) $dbpassword = self::$db_password;
		if ($dbhost == null) $dbhost = self::$db_host;
		
		mysqli_close( $this->db_object );
		$this->db_object = mysqli_connect( $dbhost, $dbuser, $dbpassword, $dbname );
		return $this->db_object;
		
	}

    protected function add_connection( $reference, $dbname = null, $dbuser = null, $dbpassword = null, $dbhost = null ) {

		if ($dbname == null) $dbname = self::$db_name;
		if ($dbuser == null) $dbuser = self::$db_user;
		if ($dbpassword == null) $dbpassword = self::$db_password;
		if ($dbhost == null) $dbhost = self::$db_host;
		
		if (isset( $this->db_extensions[$reference] )) mysqli_close( $this->db_extensions[$reference] );
		$this->db_extensions[$reference] = mysqli_connect( $dbhost, $dbuser, $dbpassword, $dbname );
		return $this->db_extensions[$reference];
	}

    protected function use_connection( $connID ) {
		if (isset( $this->db_extensions[$connID] )) {
			$this->db_object = $this->db_extensions[$connID];
			return $this->db_object;
		} else return false;
	}

    protected function is_connected( ) {
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

    protected $last_query = '';

    protected function query( $sql ) {
		
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

    protected function insert_id() {
		
		return mysqli_insert_id( $this->db_object );
		
	}
	
	/** 
	 *
	 *
	 */

    protected function affected_rows() {
		
		return mysqli_affected_rows( $this->db_object );
			
	}

    protected function num_rows( $q = null ) {
		
		if ($q == null) {
			$q = isset($this->last_query) ? $this->last_query : false;
		}
		
		if ($q) return mysqli_num_rows( $q ); else return false;
		
	}

    protected function assoc( $q = null ) {
		
		if ($q == null) {
			$q = isset($this->last_query) ? $this->last_query : false;
		}
		
		if ($q) return mysqli_fetch_assoc( $q ); else return false;
		
	}

    protected function arr( $q = null ) {
		
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

    protected function table_exists( $tbl_name ) {
		
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

    protected static function lookup( $table, $id, $stringkey, $lookupkey = "id" ) {
	
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
	


}