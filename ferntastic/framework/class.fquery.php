<?php

/**
 * fQuery file. Used for DB manipulation.
 * fQuery allows selecting from the database and manipulation of selectors.
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.5
 * @todo Add relational support
 * Possible ways
 * <code>
 * <?php
 * $f = new fQuery(array('u' => 'users', 'i' => 'items'));
 * $f->query('u.id_user,u.name[x=?],i.id_user[x=u.id_user]', 'Stephen'); //this should find Stephen and get his items.
 * //problem is, how can it tell the difference between u.id_user and 'u.id_user'? Easy! Quotes! No quotes = variable
 * //Essentially, this needs to add variable support to fQuery
 *
 * //Perhaps this will allow for more support like this
 * $f->query('time[x=CURRENT_TIMESTAMP()]');
 * //it isn't in quotes so we know it to be a variable, in this case a variable that is a result of a function return
 * Well, that's enough for today 
 *
 * //Solution 2
 * class fQueryRelation {} //this is the class, created by __construct($table_name);
 * //it has a method called ->row(); this throws the data of an fQuery relation that has a table name and row name
 * //it can then read the object checking if it is an instanceof fQueryRelation
 * //I prefer the first method. It seems more in the spirit of fQuery
 * ?>
 * </code>
 * @todo Aliasing support - this seems to be a far cry aways from what I want to do right now
 * But never the less it will provide useful. Essentially, the aliasing will probably work like this
 * <code>
 * <?php
 * $f = new fQuery('table');
 * $f->query('*a[x={id/10}]'); //* defines alias. Tells program to know not to look for it as a col
 * //though we can check if it is one so we can drop the alias if it is going to cause a code problem
 * //this will define in mysql: alias a as id / 10;
 * //we can just do this verbatim. MySQL errors thrown are the result of faulty code. nothing we can do
 * //though we can make sure to format the types correctly. So strings need to be in quotes, etc.
 * ?>
 * </code>
 */

if (!function_exists('Fn')) die();

/**
 * fQueryError extends LogError
 *
 * fQueryError is the exception object thrown every time fQuery crashes for any reason.
 * The errors return strings and database entries are defined in the errors.xml page
 * loaded using the resource module of Ferntastic library.
 *
 * @see LogError
 *
 */

class fQueryError extends LogError {
	private $type = __CLASS__;	
}

/**
 * fQuery is the Ferns Query Object used for all DB-based websites. It cycles through the DB
 * like Wordpress cycles through posts. This fQuery only supports MYSQL. Use the mongodb.fquery.php
 * for mongo support
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.2
 *
 * <code>
 * <?php
 * $f = fQuery('table1', '*:limit(0,4)'); //creates new fQuery object
 * $g = fQuery('table1', 'post_author[value=?],*:limit(0,4)', $variable); //this creates the fQuery using binded parameters.
 * $f->add('*:lim(4,10)'); //adds the ones past the limits to the fQuery object
 * 
 * //data has been loaded. cycle through now
 * $f->each( function( $data ) {
 * print_r ( $data ); //prints $data (fQueryData object) 
 * });
 *
 * ?>
 * </code>
 *
 */

function fQuery(/*$context, $selectors = null*/ ) { //construct the query object. type is the type of post, like deals, etc. Procedural
		try {
			$arguments = func_get_args(); //array of the arguments saved
			
			if (func_num_args() > 0) {
				
				if (is_array($arguments[0]) and func_num_args() < 2) $arguments=$arguments[0];
				//if there are more arguments than 0, it can be either TABLE or ROWS
				//we can check to see if it is null. That means it's rows
				
				if (count($arguments) > 1) {
					$executionType="ROWS";
					$context = $arguments[0] == null ? fQueryRows::$last_table : $arguments[0];
					$selectors = $arguments[1];
					unset( $arguments[1] );
					unset( $arguments[0] );
					if (count($arguments) > 0) {
						foreach ($arguments as $rg) {
							$arguments[] = $rg;	
						}
					} //add the arguments to the arguments list. Save it for later.
				} else {
					$executionType = "TABLE";
					$context = $table_name =  $arguments[0];
				}
				
			} else $executionType = 'DATABASE';
			
			/**
			  * This will determine the type of fQuery this is. If there is no context, the Database is selected. 
			  * DB Selection is used to check number of tables and the INFORMATION_SCHEMA. For security, this is READONLY.
			  * If there is only one argument it is set to Table selection. This allows the addition and removal of rows. Selector can be added at any time using the ->query() method, which is the equivalent of making a separate fQuery. You can also get the details of the table with ->details().
			  * Lastly, there is ROW mode. This is the standard fQuery purpose It is used to cycle through rows.
			  */
			if ( $executionType == "ROWS") {
			
				$selector = (string) $selectors;
				if ($context == null) $context = fQueryRows::$last_table;
				if ($context == null) throw new fQueryError("ucontext");
				
				$x = new fQueryRows( $context, $selectors, $arguments );
				return $x;
				
			} elseif ($executionType == "TABLE") {
				
				//$context is set to the Table which is being selected.
				//we need to select the table in the information schema. 
				if (fQuery::$fQuerySafetyDefault and !self::$useDatabase->table_exists( $context )) throw new fQueryError('notbl');
				
				return new fQueryTable( $context );
				
			} else throw new fQueryError( 'ukextype' );
			
			return;
			
		} catch (fQueryError $e) {
			$e->handleMe();
			return false;	
		}
		
}

class fQuery {
	public static $useDatabase;	
	public static $fQueryChainingDefault = false;
	public static $fQuerySafetyDefault = false;
	
	/**
	 * Lookup function
	 *
	 * Lookup function simplifies getting MySQL data from another table
	 *
	 */
	
	public static function removeQuotes( $str ) {
	
		return preg_replace( "#['\"]#", "", $str );
			
	}
	
	function __construct() {
		if (!isset(self::$useDatabase) or !empty(self::$useDatabase)) return false;	
	}
	
	function __invoke(/* mixed_params */) {
		return call_user_func_array( 'fQuery', func_get_args() );
	}

}

class fQueryRows extends fQuery {

	/*
	 * @var $selector The currently loaded selector.
	 * @access private
	 */
	 
	private $selector = "";
	
	/*
	 * @var integer $current Current post. Starts at -1 which means it is unloaded
	 * @access private
	 */
	 
	private $current = -1;
	
	/*
	 * @var mysqlresource $query Holds the query link for use in mysqli functions
	 * @access private
	 */
	
	private $query; //the query data (a mysql_result_resource
	
	/* 
	 * @var mixed $row_data An array with all the row data in the selection.
	 * @access private
	 */
	
	protected $row_data = array(); //the row data from assoc
	
	/*
	 * @var array $curr_row The data for the currently loaded post for EACH statements
	 * @access private
	 */
	
	private $curr_row;
	
	/*
	 * @var $lastSQL SQL statement executed last among all the classes
	 * @staticvar string $lastSQL;
	 */
	
	static $lastSQL = ''; //static variable used to access the last SQL statement
	
	/*
	 * @var integer $count holds the number of rows in the current selection
	 * @var integer $total_count holds the number of rows unlimited in the selection
	 * @access public
	 */
	 
	public $count = 0, $total_count = 0;
	
	/*
	 * @name string $last_table The last table used for selection.
	 * @staticvar $last_table
	 */
	
	static $last_table = null;
	
	/*
	 * @var mixed $cols Holds the column data that is being loaded by this fQuery object
	 * @access private
	 */
	
	private $cols = array();
	
	/*
	 * @var array $arguments This is the array used to load the extra arguments in the __construct function
	 * @see fQuery::__construct()
	 * @access private
	 */
	
	private $arguments = array();
	
	/*
	 * @var int $arg_counter This is the pointer used to go through the arguments.
	 * @access private
	 */
	
	private $arg_counter = 0;
	
	/*
	 * @var string PARAM_REG This is the Regular Expression used to interpret parameters of the selector string
	 * @constant
	 */
	
	const PARAM_REG = "(?P<fullparams>(?P<name>[^=!<^>=~%&]+) *(?P<equal>(?:[!]?[<^>=~%&]?[<>=])) *(?P<val>(?:(?P<quote>['\"]).*(?P=quote)|(?:(?:[0-9?])+ *)+)) *[,]? *)";
	
	/**
	 * @var mixed $oldSQL Holds the old SQL statements executed
	 * @name $oldSQL
	 */
	
	private $oldSQL = array();
	
	/**
	 * @var mixed $funcs Holds the functions that are being loaded into fQuery.
	 * @staticvar $funcs
	 */
	
	static $funcs = array('or' => 'run_func_or');
	
	/**
	 * Adds new functions to the fQuery selector text.
	 * 
	 * Function add_function is static among all fQuery objects. This is called only to add new associations
	 * between fQuery selector text and user called functions.
	 *
	 * @access static
	 * @param string $key The function name inside the fQuery selector text
	 * @param string $val The function name in PHP
	 */
	
	static function add_func( $key, $val ) {
		self::$funcs[$key] = $val;	
	}
	
	private $columns = array();
	
	/**
	 * @var string $sqlBefore This is a pre-formatted SQL statement used in a sprintf function to add the parameters in.
	 * @constantvar
	 */
	
	const sqlBefore = "SELECT SQL_CALC_FOUND_ROWS %s FROM `%%s` WHERE %s %s %s";
	
	/**
	 * @var mixed $stmts Holds statements as they are added by the __construct() function, which calls the parse method.
	 * @access private
	 */
	
	private $stmts = array();
	
	/**
	 * @var string $executionType After construction, the fQuery can have varying execution types. The executionType helps influence what other functions can be run. Can equal ROWS, TABLE, or DATABASE
	 * @access private
	 */
	 
	private $executionType;
	
	/**
	 * Function add_sql adds SQL statements to the $stmt array.
	 *
	 * Adds the statements into the statement array. Takes 3 parameters. Uses both
	 * priorities and places. Places correlate with the '%s' in the string format. 
	 * For example, if you're adding a column you would use add_sql($stmt, 1); Priority
	 * determines which one will load first. There can only be one priority in the array, so if
	 * prio 101 is called twice, it will be overwritten. Use this when there can only be one of a given statement.
	 * There may only be one limit clause so this would come in useful for such things.
	 *
	 * @param string $stmt the SQL statement at that given line. 
	 * @param int $place The placement of that given statement in the greater SQL statement.
	 * @param int $prio Defaults to 0. Priority of the statement. Determines order and makes sure there
	 * are not too many similar statements
	 *
	 * @access public
	 */
	
	public function add_sql($stmt, $place=null, $prio=0) {
		
		if ($place !== null) { //if place is set, use it
			$place = (int) $place;
			
			if ($prio == 0) $this->stmts[$place]['none'][] = $stmt;
			else $this->stmts[$place]['prio'][$prio] = $stmt; 
			
		} else throw new fQueryError('nosupport'); //if place is not set, kill the program
		
	}
	
	/**
	 * Function buildSQLFromStmts uses the $stmt array to make a real SQL statement.
	 *
	 * This function uses the loaded statements to create a SQL statement to be executed after being filled in
	 * with the table name. Uses priority first, then non priority statements.
	 * 
	 * @return string The SQL statement with one variable %s to be used with sprintf($return, $tablename);
	 *
	 * @access public
	 */
	
	private function buildSQLFromStmts() {
		
		$sql_array = array(0=>'', 1=>'', 2=>'', 3=>''); //the first array. 4 keys correlate with 4 '%s'
		foreach ( $this->stmts as $pl => $v ) { //start cycling through the statements.
			
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
		
		return $ret; //return the new statement
	}
	
	/**
	 *  method parses the selector string that is given and interprets it as SQL
	 * 
	 * On the initiation of the class, a selector is given as a parameter for certain fQuery requests.
	 * That selector needs to be "translated" into a SQL statement to be utilized.
	 *
	 * @param string $selectors The fQuery selector string
	 * @return string The SQL statement that is going to be used. Returns an "sformat" string that requires table parameter.
	 *
	 * <code>
	 * $this->parse("id,email,password[value={$password}]");
	 * </code>
	 *
	 */
	
	private function parse( $selectors ) {
		//start the parsing by replacing all usable commas with the replacement text
		$inQuote = false;
		$inBracket = false;
		$inBrace = false;
		$inParenthesis = false;
		$inSingQuote = false;
		
		$counter = 0;
		$newString = "";
		
		$rep = ";;fQuerySelectorSep".md5(time()+"Johnson_salt"+rand(0,50000)).";;"; //ridiculous SALT to replace commas with. No one would use this in a statement, right?
		while ($counter < strlen( $selectors ) ) {
			
			$temp = substr( $selectors, $counter, 1);
			
			switch ($temp) {
				
				case '"':
					if (!$inSingQuote) {if ($inQuote) $inQuote = false; else $inQuote = true;}
					$newString .= $temp;
				break;
				
				case "'":
					if (!$inQuote) {if ($inSingQuote) $inSingQuote = false; else $inSingQuote = true;}
					$newString .= $temp;
				break;
				
				case '[':
					if (!$inQuote and !$inSingQuote) $inBracket = true;
					$newString .= $temp;
				break;
				
				case ']';
					if (!$inQuote and !$inSingQuote) if ($inBracket) $inBracket = false;
					$newString .= $temp;
				break;
				
				case '(':
					if (!$inQuote and !$inSingQuote) $inParenthesis = true;
					$newString .= $temp;
				break;
				
				case ')':
					if (!$inQuote and !$inSingQuote) if ($inParenthesis) $inParenthesis = false;
					$newString .= $temp;
				break;
				
				case ',':
				
					if (!$inQuote and !$inBrace and !$inBracket and !$inParenthesis) {
						$newString .= $rep;
					} else $newString .= $temp;
				
				break;
				
				default:
				
					$newString .= $temp;
				
				break;
					
			}
			
			$counter++;	
		} //this will format those appropriate commas
		
		$split = explode( $rep, $newString ); //now split it using the $replacement string
		$selectorData = array();   //this will hold the selector data
		$i = 0; //counter. Could use $selectorData[], but would require a reference variable because it may be called more than once.
		
		foreach ($split as $col) { //cycle through the new split cariables
			
			$col = trim($col); //trim it if necessary
			//3 repeatable regex vals: functions conditions and col
			//col is easy to get, just the first chars between either a , [ or end
			$conds = $funcs = "";
			
			$colname = '';
			//first get the column name
			if (!preg_match("#^(?P<colname>[^[:;]+)(?:\[(?P<params>[^]]+)\])?(?P<functions>(?:[:].+)+)?$#i", $col, $matches)) {
				throw new fQueryError('invselector'); //if this does not match that means the selector data has not been formatted correctly
			} else {
				
				$colname = $matches['colname']; //this one has to be set for the REGEX to go true
				if (fQuery::$fQuerySafetyDefault) if (!in_array($colname, $this->columns) and $colname != "*") continue;
				$are_params = !empty($matches['params']) ? $matches['params'] : false; //does not need to be set
				$are_functions = !empty($matches['functions']) ? $matches['functions'] : false; //does not need to be set
				
			}
			
			$selectorData[$i]['colname'] = $colname; //set the column name in the selectorData[] at this specific index
			
			if ($are_params) { //if there are parameters
				
				//get the parameters
				
				$paramREGEX = self::PARAM_REG; //This is the regex to interpret individual statements.
				$params = array(); //holds the parameters
				while (preg_match("#".$paramREGEX."#i", $are_params, $matches)) { //cycle through them
					$are_params = str_replace($matches['fullparams'], '', $are_params); //get rid of the string we just used so we can make the while loop eventually turn false
					$params[] = array("name" => $matches['name'], "comparison" => $matches['equal'], "val" => $matches['val']); //add this array to the parameters. Thes 
				}
				
				$selectorData[$i]['params'] = $params; //append parameters
				
			}
			
			if ($are_functions) { //if there are functions
				
				//get the functions
				$funcsREGEX = "(?P<full>(?P<colon>\:)(?P<funcname>[^(]+)(?:\((?P<params>[^)]+)?\))?)"; //REGEX for interpreting functions
				
				$functionsArr = array(); //array to hold function data
				while (preg_match("#".$funcsREGEX."#i", $are_functions, $matches)) { //just like before, cycle through function text
					$are_functions = str_replace($matches['full'], '', $are_functions); //replace it so the while loop eventually turns false
					$p = !empty($matches['params']) ? $matches['params'] : false; //if the params are empty, make them false
					
					$functionsArr[] = array("name" => $matches['funcname'], "params" => $p); //add it to the array
				}
				
				$selectorData[$i]['functions'] = $functionsArr; //add it to the selectorData
				
			}
			
			$i++; //increment the counter
		
		}
		
		/**
		 * @internal At this point in the parse method, we have a possibility of three arrays for each param, one of which is required.
		 * $selectorData[]['colname'] is the column name that is being requested.
		 * $selectorData[]['params'][] holds all of the parameter data, ['name'], ['comparison'], and ['val'] respectively.
		 * $selectorData[]['functions'][] holds all of the function data, ['name'] and ['params']
		 */
		
		//now we need to build the SQL. This has to be done in three parts: $sqlConditions, $sqlCols, and $endClauses
		$sqlConditions = $sqlCols = $endClauses = ''; //initialize the variables.
		
		foreach ( $selectorData as $columnData ): //cycle through the selector data
		
			$curr = '';
			$this->cols[] = $columnData['colname'];
			if ( $sqlCols != "*") {
				//If, at any point, there is a wildcard, the entire string needs to be replaced with a wildcard.
				if ($columnData['colname'] == "*") $sqlCols = "*"; else $sqlCols .= $columnData['colname'] . ",";
			} //end if it is not a wildcard
			
			$params = isset($columnData['params']) ? $columnData['params'] : false;
			$funcs = isset($columnData['functions']) ? $columnData['functions'] : false;
			
			if ($params) {
				
				foreach ( $params as $p ) {
					
					//interpret parameters
					try {$curr = $this->interpretParam($columnData['colname'], $p);} catch (fQueryError $e) {
						$e->handleMe();
					}
					
				}
				
			}
			//check the functions for sorting, etc.
			//Most important functions are ORDER and LIMIT
			
			if ( $funcs ) {
				
				foreach( $funcs as $function ) {
					
					$params = isset( $function['params'] ) ? $function['params'] : null;
					$name = $function['name'];
					$temp = $this->interpretFunction( $name, $params, $columnData['colname'], $curr );
					if (strlen($temp) > 1) $curr = $temp;
					
				}
				
				
			}
		
			if (!empty($curr)) $this->add_sql($curr, 1);
		
		endforeach;
		
		$this->add_sql( $sqlCols, 0, 1); //add into the first position the columns
		
		return $this->buildSQLFromStmts(); //build the SQL from the statements and return it to the __construct function
	}
	
	/**
	 * Function interpretParam interprets the parameter given, turning it into SQL
	 *
	 * This function takes 3 parameters, last being a reference varialble to update, and returns
	 * a SQL statement
	 *
	 * @param string $colname The name of the column being interpreted
	 * @param array $p The parameter data used in the being interpretation
	 *
	 * @access public
	 * @return string The SQL statement from the interpretation
	 */
	
	public function interpretParam( $colname, $p ) { 
		
		$return = ''; //prepare return data
		if ( $p['name'] == "value" || $p['name'] == "v" || $p['name'] == "x" ) { //if the param name is "value"
			$return .= $colname . " ";
			//now interpet the comparison operator
			switch ($comp = $p['comparison']) {
				
				case '~=': $comp = " LIKE "; break;
				case '!~=': $comp = " NOT LIKE "; break;
				
				case '!&=': $comp = " NOT IN "; break;
				case '&=': $comp = " IN "; break;
				
				case '*=': $comp = " REGEXP "; break;
				case '!*=': $comp = " NOT REGEXP "; break;
				
				default: $comp = " ${comp} "; break;
				
			} //end switch-case
			
			$return .= $comp;
			if ( preg_match( "#(?P<quote>['\"])(?P<act_val>.*)(?P=quote)#", $p['val'], $matches )) {
				
				$val = "'" . self::$useDatabase->e($matches['act_val']) . "'";
			} else $val = self::$useDatabase->e($p['val']);
			
			if (($x = fQuery::removeQuotes($val)) == "?") {
				if (!isset($this->arguments[$this->arg_counter])) throw new fQueryError("ubparam");
				else $temp = $this->arguments[$this->arg_counter++];	
				
				switch (strtolower(trim($comp))) {

					case 'not in':
					case 'in':
						if ( count((array) $temp) == 0 ) throw new fQueryError("paramerror");
						else {
						
						if ( !is_array( $temp ) ) $temp = (array) $temp;
						for ($i=0; $i<count($temp); $i++) $temp[$i] = "'".fQuery::removeQuotes($temp[$i])."'";
							 
						$temp = implode(',', $temp);
						$val = "(".$temp.")";	
						}
					
					break;
					
					default:
						$val = "'". (string) $temp ."'";
					break;		
					
				}
				
			}
			$return .= $val;
			
		} else { //this is where extensions would go for parameters.
			throw new fQueryError("ubparam");
			return false;
		}
		
		return $return; //returns true on success
		
	}
	
	
	/**
	 * Executes selector functions.
	 *
	 * Selector functions are the most expandable part of the fQuery object! They provide much more influence over
	 * the database and can call for infinite possibilities! Does your website want to allow access to certain entries
	 * if that given entry is ACTIVE only? Well, make a pseudo-function :active and it will only select those entries that are active!
	 * Does your function needs to be able to display it to only people who are friends with others? Use a pseudo class! They are a 
	 * powerful tool to manipulate the database and are easily extensible
	 *
	 * <code>
	 * 
	 * 
	 * </code>
	 *
	 * @param string $name the Name of the function to be executed. No default for this
	 * @param string $params A string of parameters separated by commas. This is a string.
	 * @param string $col A column to manipulate if you want to use the function for a conditional
	 *
	 */
	
	protected function interpretFunction( $name, $params=null, $col=null, $currStmt='') {
//		printf("%s being passed to function %s<br>", $currStmt, $name);
		
		if (array_key_exists( $name, self::$funcs )) {
			
			$fname = self::$funcs[ $name ];
			if (function_exists($fname)) $ret = call_user_func( $fname, $params, $col, $this, $currStmt );
			else throw new fQueryError('nofunc', array('func_name' => $fname));
			return $ret;
		} else return false; //returns false if the function exist. Or should it throw an exception
		
	}
	
	/**
	 * If the object is sent in when it is supposed to be a string, it just returns the selector.
	 * Useful for using selectors in a type of cloning.
	 *
	 * <code>
	 * $j = fQuery( 'table', 'selectors' );
	 * $x = fQuery( null, $j );
	 * </code>
	 */
	
	function __toString() {
		
		return $this->selector;	
		
	}
	
	private $table_name = null;
	
	/**
	 * fQuery is the Ferns Query Object used for all DB-based websites. It cycles through the DB
	 * like Wordpress cycles through posts but does it using selectors similar to jQuery.
	 * to construct this object you use the following syntax
	 *
	 * @param $selectors The selector string. this string builds the conditions of the selection
	 *
	 * <code>
	 * <?php
	 *
	 * $j = fQuery('[cats,dogs,fish]', 'animals'); //args in between [] are the selected rows.
	 * $j = fQuery('[cats="dogs",fish="john"]', 'animals'); //this looks for the given values
	 * $j = fQuery('[cats,dogs];lim(0,4)', 'animals'); //this limits the selection from 0,4 bounds.
	 * $j = fQuery('[cats,dogs]'); //this defaults to the last used table. Will throw an exception if it hasn't been called
	 * 
	 * ?>
	 * </code>
	 *
	 */
	
	public function __construct( $context, $selectors, $arguments=null ) { //construct the query object. type is the type of post, like deals, etc.
		try {
			
			if ($arguments!=null) {
				
					if (is_array($arguments)) {
					
					foreach( $arguments as $rg ) {
						$this->arguments[] = $rg;
					}
				
				} else {/*throw notice*/}	
			
			}
			$this->selector = (string) $selectors;
			if ($context == null) $context = self::$last_table;
			if ($context == null) throw new fQueryError("ucontext");
			
			if (fQuery::$fQuerySafetyDefault) {
				
				self::$useDatabase->query(sprintf("SHOW COLUMNS FROM %s", self::$useDatabase->e($context)));//this may save time
				
				$columns = array();
				
				while ($r = self::$useDatabase->assoc()) {
					$columns[] = $r['Field'];	
				}
				$this->columns = $columns; //this adds around 5 milliseconds to the load time of this function per instantiation. it my be too costly.
			
			}
			
			$parsed = $this->parse( (string) $selectors );
			$sql = sprintf( $parsed, $context );
			
			$this->oldSQL[] = $sql;
			$this->query = self::$useDatabase->query( $sql );
			
			self::$last_table = $context; //set the static variable of context as the default of context
			$this->table_name = $context; //set local variable too		
			
			if ( ($count = self::$useDatabase->num_rows( $this->query ) ) > 0) {
				
				$this->count = $count;
				$sql = "SELECT FOUND_ROWS();";
				$q = self::$useDatabase->query( $sql );
				$r = self::$useDatabase->arr( $q );
				$this->total_count = $r[0];
				
				while ( $r = self::$useDatabase->assoc( $this->query ) ) $this->row_data[] = $r;
				return $this;
					
			} else return $this;
			
			
		} catch (fQueryError $e) {
			$e->handleMe();
			return false;	
		}
		
	}
	
	/**
	 * Filters the Selection object.
	 *
	 * @param $selectors The selector string. this string builds the conditions of the selection
	 *
	 * <code>
	 * <?php
	 *
	 * $j = fQuery('[cats,dogs,fish]', 'animals'); //args in between [] are the selected rows.
	 * $j->not('cats[value=kitty]')
	 * 
	 * ?>
	 * </code>
	 *
	 */
	
	public function not( $selectors ) {
		
		//nature of filter does not require fetching information from the database, we just need to weed out certain entries
		$this->each( function( $data ) {
			
			//DO ME @todo
			
		} );
		
	}
	
	/**
	 * Context dependent. Add adds new Rows to the selection object, adds a new column, or does nothing.
	 *
	 * @param $selectors The selector string. this string builds the conditions of the selection, if selector string is necessary
	 * @param mixed $array Otherwise, it uses an array of data. Data should be col Data to add the new field to.
	 *
	 * <code>
	 * <?php
	 *
	 * $j = fQuery('[cats,dogs,fish]', 'animals'); //args in between [] are the selected rows.
	 * $j->not('cats[value=kitty]')
	 * 
	 * ?>
	 * </code>
	 *
	 */
	
	public function add( /**$selectors, $tbl = null**/ ) {
			
		if (func_num_args() < 1) throw new fQueryError('ucontext');
		
		$selectors = func_get_arg(0);
		$tbl = func_num_args() > 1 ? func_get_arg(1) : null;
		
		if ($tbl == null) $tbl = self::$last_table;
		if ($tbl == null) throw new fQueryError('tblparams');
		
		//nature of filter does not require fetching information from the database, we just need to weed out certain entries
		//selector must have only the same columns as the parent but we will do this later. For now, we will make sure there is a *
		if (!strstr( $selectors, "*" )) $selectors .= ",*";
		
		$temp = new fAlt( $tbl, $selectors );
		$temp_ret = $temp->dump_data();
		unset($temp);
		
		$data = $temp_ret['data'];
		$tot = $temp_ret['tot']; //5 (lim 4)
		$num = $temp_ret['num'];
		
		$this_tot = $this->total_count;
		$this_num = $this->count;
		
		if ($ret = $this->merge( $data )) {
			
			//find out how many were deleted upon merge
			
			$olddata = count($data) + $this_num; //this is the number of the new one before the merge (3)
			$newdata = $this->num = count($ret); //returns the number after the merge (4 - 3+1)
			
			$this->total_count = ( ( $this->total_count + $tot ) - ( $olddata - $newdata ) );
			$this->count = count( $this->row_data );
			
 
		} else return false;
		return true;
		
	}
	
	public function this() { 
		if ($this->count > 0):
			return new fQueryData($this->row_data[$this->current + 1]);
		else:
			return false;
		endif;
	}
	
	public function extract( &$var ) {
		if ($this->count > 0):
			$var = $this->row_data[$this->current + 1];
			return true;
		else:
			return false;
		endif;
	}
	
	public function reload() {
		
		$sql = isset($this->oldSQL[count($this->oldSQL)-1]) ? $this->oldSQL[count($this->oldSQL)-1] : $this->oldSQL[0];
		$this->query = self::$useDatabase->query( $sql );	
		
		$this->row_data = array();
		
		if ( ($count = self::$useDatabase->num_rows( $this->query ) ) > 0) {
			
			$this->count = $count;
			$sql = "SELECT FOUND_ROWS();";
			$q = self::$useDatabase->query( $sql );
			$r = self::$useDatabase->arr( $q );
			$this->total_count = $r[0];
			
			while ( $r = self::$useDatabase->assoc( $this->query ) ) $this->row_data[] = $r;
			
			return $this;
				
		} else return null;
		
	}
	
	/*
	 * Cycles through the loaded rows.
	 * 
	 * @param string|function $func The function to execute. Passes a parameter with the current row data.
	 * @param string|function $callback Callback function to execute at the end. Currently no callbacks
	 */
	
	public function each( $func, $callback = null ) {
		if ($this->count < 1): return false; else: //if there are no posts this can't be executed
		
			while ($this->have_data()) {
				
				$this->current++;
				if ( is_callable( $func ) )
				{
					
					$newob = new fQueryData( $this->row_data[$this->current] );
					call_user_func( $func,  $newob );
					unset( $newob );
				}
				else throw new fQueryError('nofunc');
					
			}
			
			if ($callback != null && is_callable( $func ) ) call_user_func( $callback, $this );
			
			$this->current = -1;
			
			return (fQuery::$fQueryChainingDefault) ? $this : true;
		
		endif;
		
	}
	
	/**
	 *
	 *
	 */
	
	public function update( $sel, $runeach = false ) {
		
		if (!$runeach):
			
			$sql_array = array(0=>'', 1=>'', 2=>'', 3=>''); //the first array. 4 keys correlate with 4 '%s'
			foreach ( $this->stmts as $pl => $v ) { //start cycling through the statements.
				
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
			
			$conditions = $sql_array[1];
			
			$sformat = "UPDATE `%s` SET %s WHERE %s %s %s";
			$tbl = $this->table_name;
			
			/** PARSE THE UPDATE ARRAY **/
			
			//$sel is expected to be an array of changes that are in the cols called
			
			$columns = $this->columns;
			$newstring = "";
			
			if (is_array($sel) and count($sel) > 0) {
				foreach ( $sel as $the_column => $the_new_value ):
				
					if (fQuery::$fQuerySafetyDefault and !in_array( $the_column, $columns) ) continue;
					//continue only if it is in the columns which exist
					
					$temp = "%s = ";
					//we need to check if it is an int or a string so it can be put in correctly
					if (is_int($the_new_value)) {
						//is an int	
						$temp .= "%d";
					} elseif (is_float($the_new_value)) {
						$temp .= "%f";
					} else {
						$temp .= "'%s'";
					}
					
					$newstring .= sprintf($temp, $the_column, $the_new_value).",";
				
				endforeach;
				
				$newstring = substr( $newstring, 0, strlen( $newstring ) - 1);
				
			} else throw new fQueryError('invsel');
			if (empty($newstring)) return;
			/** END PARSING **/
			
			$t = preg_replace("#LIMIT [0-9]+ *(, *([0-9]+))?#i", "LIMIT $2", $sql_array[3]);
			if (preg_match("#LIMIT *[0-9]+$#i", trim($t))) $sql_array[3] = $t;
			
			$sql = sprintf($sformat, $tbl, $newstring, $conditions, $sql_array[2], $sql_array[3]); 
			self::$useDatabase->query( $sql );
			$this->reload();
			
			return (self::$useDatabase->affected_rows() > 0) ? true : false;
		
		else:
		
		//@todo
		
		
		endif;
		
		
	}
	
	/**
	 *
	 *
	 */
	
	private function have_data() {
		
		$data = $this->row_data;
		$index = $this->current;
		
		@$curr_data = $data[$index+1];
		
		if ( !isset($curr_data) ) return false;
		else return true;
		
	}

}

class fQueryTable extends fQuery {
	
	/*
	 * @var mysqlresource $query Holds the query link for use in mysqli functions
	 * @access private
	 */
	
	private $query; //the query data (a mysql_result_resource
	
	/*
	 * @var integer $count holds the number of rows in the current selection
	 * @var integer $total_count holds the number of rows unlimited in the selection
	 * @access public
	 */
	 
	public $count = 0, $total_count = 0;
	
	/*
	 * @name string $last_table The last table used for selection.
	 * @staticvar $last_table
	 */
	
	static $last_table = null;
	
	/*
	 * @var mixed $cols Holds the column data that is being loaded by this fQuery object
	 * @access private
	 */
	
	private $colData = array();
	
	/**
	 * @var mixed $funcs Holds the functions that are being loaded into fQuery.
	 * @staticvar $funcs
	 */
	
	public static $funcs = array('or' => 'run_func_or');
	
	/**
	 * Adds new functions to the fQuery selector text.
	 * 
	 * Function add_function is static among all fQuery objects. This is called only to add new associations
	 * between fQuery selector text and user called functions.
	 *
	 * @access static
	 * @param string $key The function name inside the fQuery selector text
	 * @param string $val The function name in PHP
	 */
	
	static function add_func( $key, $val ) {
		self::$funcs[$key] = $val;	
	}
	
	private $columns = array();
	
	/**
	 * @var string $executionType After construction, the fQuery can have varying execution types. The executionType helps influence what other functions can be run. Can equal ROWS, TABLE, or DATABASE
	 * @access private
	 */
	 
	private $executionType;
	
	
	/**
	 * Executes selector functions.
	 *
	 * Selector functions are the most expandable part of the fQuery object! They provide much more influence over
	 * the database and can call for infinite possibilities! Does your website want to allow access to certain entries
	 * if that given entry is ACTIVE only? Well, make a pseudo-function :active and it will only select those entries that are active!
	 * Does your function needs to be able to display it to only people who are friends with others? Use a pseudo class! They are a 
	 * powerful tool to manipulate the database and are easily extensible
	 *
	 * <code>
	 * 
	 * 
	 * </code>
	 *
	 * @param string $name the Name of the function to be executed. No default for this
	 * @param string $params A string of parameters separated by commas. This is a string.
	 * @param string $col A column to manipulate if you want to use the function for a conditional
	 *
	 */
	
	protected function interpretFunction( $name, $params=null, $col=null, $currStmt='') {
//		printf("%s being passed to function %s<br>", $currStmt, $name);
		
		if (array_key_exists( $name, self::$funcs )) {
			
			$fname = self::$funcs[ $name ];
			if (function_exists($fname)) $ret = call_user_func( $fname, $params, $col, $this, $currStmt );
			else throw new fQueryError('nofunc', array('func_name' => $fname));
			return $ret;
		} else return false; //returns false if the function exist. Or should it throw an exception
		
	}
	
	/**
	 * If the object is sent in when it is supposed to be a string, it just returns the selector.
	 * Useful for using selectors in a type of cloning.
	 *
	 * <code>
	 * $j = fQuery( 'table', 'selectors' );
	 * $x = fQuery( null, $j );
	 * </code>
	 */
	
	function __toString() {
		
		return $this->table_name;	
		
	}
	
	private $table_name = null;
	
	function reload() {
		
		$sql = isset($this->oldSQL[count($this->oldSQL)-1]) ? $this->oldSQL[count($this->oldSQL)-1] : $this->oldSQL[0];
		$this->query = self::$useDatabase->query( $sql );	
		
		if ( ($count = num_rows( $this->query ) ) > 0) {
			
			$this->count = $count;
			$sql = "SELECT FOUND_ROWS();";
			$q = self::$useDatabase->query( $sql );
			$r = self::$useDatabase->arr( $q );
			$this->total_count = $r[0];
			
			while ( $r = self::$useDatabase->assoc( $this->query ) ) $this->row_data[] = $r;
			
			return $this;
				
		} else return null;
		
	}
	
	/**
	 * fQuery is the Ferns Query Object used for all DB-based websites. It cycles through the DB
	 * like Wordpress cycles through posts but does it using selectors similar to jQuery.
	 * to construct this object you use the following syntax
	 *
	 * @param $selectors The selector string. this string builds the conditions of the selection
	 *
	 * <code>
	 * <?php
	 *
	 * $j = fQuery('[cats,dogs,fish]', 'animals'); //args in between [] are the selected rows.
	 * $j = fQuery('[cats="dogs",fish="john"]', 'animals'); //this looks for the given values
	 * $j = fQuery('[cats,dogs];lim(0,4)', 'animals'); //this limits the selection from 0,4 bounds.
	 * $j = fQuery('[cats,dogs]'); //this defaults to the last used table. Will throw an exception if it hasn't been called
	 * 
	 * ?>
	 * </code>
	 *
	 */
	
	public function __construct( $table_name=null ) { //construct the query object. type is the type of post, like deals, etc.
		try {
			
			/**
			  * This will determine the type of fQuery this is. If there is no context, the Database is selected. 
			  * DB Selection is used to check number of tables and the INFORMATION_SCHEMA. For security, this is READONLY.
			  * If there is only one argument it is set to Table selection. This allows the addition and removal of rows. Selector can be added at any time using the ->query() method, which is the equivalent of making a separate fQuery. You can also get the details of the table with ->details().
			  * Lastly, there is ROW mode. This is the standard fQuery purpose It is used to cycle through rows.
			  */
				
			$table_name = $table_name==null?self::$last_table:$table_name;
				
			//$context is set to the Table which is being selected.
			//we need to select the table in the information schema. 
			$this->table_name = $table_name;
			
			$sql = sprintf( "SHOW columns FROM %s", self::$useDatabase->e($table_name) );
			
			self::$useDatabase->query ( $sql );
			
			while ( $row = self::$useDatabase->assoc() ) {
				
				$this->columns[] = $row['Field'];
				$this->cols[ $row['Field'] ] = array('null' => $row['Null'], 'key' => $row['Key'], 'Extra' => $row['Extra'], 'default' => $row['Default'], 'type' => $row['Type']); //stores col data
				//this gets the columns. We also want the defaults
				
				//that's about all we need to do here.
				
					
			}
			
			
		} catch (fQueryError $e) {
			$e->handleMe();
			return false;	
		}
		
	}
	
	public function query( ) {
		
		$args = func_get_args();

		$selectors = isset($args[0]) ? $args[0] : "*";
		unset($args[0]);
		$args = count($args) > 0 ? $args : null;
		
		$f = new fQueryRows( $this->table_name, $selectors, $args );
		
		if ($f->count < 1) return false; else return $f;
		
	}
	
	/**
	 * Context dependent. Add adds new Rows to the selection object, adds a new column, or does nothing.
	 *
	 * @param $selectors The selector string. this string builds the conditions of the selection, if selector string is necessary
	 * @param mixed $array Otherwise, it uses an array of data. Data should be col Data to add the new field to.
	 *
	 * <code>
	 * <?php
	 *
	 * $j = fQuery('[cats,dogs,fish]', 'animals'); //args in between [] are the selected rows.
	 * $j->not('cats[value=kitty]')
	 * 
	 * ?>
	 * </code>
	 *
	 */
	
	public function add( /**$selectors, $tbl = null**/ ) {
	
		//This adds Rows to the Scheme of things. We do it using array values
		
		$sql = "INSERT INTO %s (%s) VALUES (%s)";
		$sqlCols = array();
		$sqlVals = array();
		
		$pieces = $this->columns;
		
		foreach ( func_get_arg(0) as $key => $value ) {
			
			if (in_array($key, $pieces)) {
			$pieces = array_flip($pieces);
			unset($pieces[$key]);
			$pieces = array_flip($pieces);
			
			//they need to be in the same order... unfortunately. we can do it the easy way though
			$sqlCols[] = $key;
			
			//value is a little tricker. If it is an int, it needs to not be nested in quotations. If it isn't it can. We need to get this from
			if (preg_match("#int#i", $this->cols[$key]['type'])) {
				//int
				$modValue = (int) $value;
			} elseif (preg_match("#float#i", $this->cols[$key]['type'])) {
				$modValue = (float) $value;
			} elseif (preg_match("#bool#i", $this->cols[$key]['type'])) {
				$modValue = (bool) $value;
			} else {
				//string
				$modValue = "'".self::$useDatabase->e($value)."'";
			}
			
			$sqlVals[] = $modValue;
			
			}
		}
		
		//now we need to go through the unset pieces.
		foreach ($pieces as $unsetCol) {
			
			//unset Cols need to load the data
			$colData = $this->cols[$unsetCol];
			//first let's determine if the value is null. If it is we can skip it
			if ( $colData['null'] == "NO" ) { //we can just skip it if it is null
				
				//now let's check if its a primary key and auto increment. If it is we can skip it
				if ($colData['Extra'] == "auto_increment" and $colData['key'] == "PRI") {} else {
					
					//this means it isn't an auto incrementing primary key
					
					//now we need to put the default value I guess
					$sqlCols[] = $unsetCol;
					
					//we need to do the same thing we did efore
					//value is a little tricker. If it is an int, it needs to not be nested in quotations. If it isn't it can. We need to get this from
					$value = $colData['default'];
					if (preg_match("#int#i", $colData['type'])) {
						//int
						$modValue = (int) $value;
					} elseif (preg_match("#float#i", $colData['type'])) {
						$modValue = (float) $value;
					} elseif (preg_match("#bool#i", $colData['type'])) {
						$modValue = (bool) $value;
					} else {
						//string
						$modValue = "'".self::$useDatabase->e($value)."'";
					}
					
					$sqlVals[] = $modValue;;
					
				}
				
			}
			
				
		}
		
		$sql = sprintf( $sql, $this->table_name, implode(",", $sqlCols ), implode(",", $sqlVals) );
		
		query( $sql );
		if (affected_rows() > 0) return true; else return false;
		
	}

}


class fQueryData {
	
	private $data = array();
	
	function __construct( $data ) {
		
		$this->data = $data;
		
	}
	
	function row( $id ) {
		
		if ( isset( $this->data[$id] ) ) return stripslashes($this->data[$id]);
		else return false;
			
	}
	
	public function to_array() {
		
		return $this->data;
			
	}
	
	function __get( $key ) {
		return $this->row( $key );	
	}
	
	
}

/**
 * Simple abstraction
 * Below it uses the absraction to add functions to fQueryRows. That's the only place functions are currently accepted.
 */

function fQueryAddFunction( $name, $func ) {
	
	fQueryRows::add_func( $name, $func );
	
}

function fq_or( $params, $col, $q, $old ) {
	$arr = explode( ',', fQuery::removeQuotes( $params ) );
	
	$numArgs = count( $arr );
	$ret = sprintf('(%s OR ', $old);
	
	if ( $numArgs >= 1 ) {
		
		foreach ($arr as $param) {
		
			$params = array();
			while (preg_match( "#(?P<col>[0-9a-z_-]+)?\[".fQueryRows::PARAM_REG."\]#i", $param, $matches) ) { //expecting v[] at least
				
				$param = str_replace($matches['fullparams'], '', $param);
				$temp = array("name" => $matches['name'], "comparison" => $matches['equal'], "val" => $matches['val']);
				
				$c = (!empty($matches['col'])) ? $matches['col'] : $col;
				$ret .= $q->interpretParam($c, $temp) . " OR ";
				
			} 
				
		}
		$ret .= ")";
		$ret = preg_replace("#(and|or) \)$#i", ")", $ret);
			
	} else throw new fQueryError("orerror");
	
	return $ret;
	
}

function fq_lim( $params, $col, $q ) {
	
	$arr = explode( ',', fQuery::removeQuotes( $params ) ); //split the parameters by commas
	$numArgs = count ( $arr ); //number of arguments base on the split
	if ( $numArgs == 1 ) {
		$ret = fQuery::$useDatabase->e( $arr[0] );	
	} elseif ( $numArgs == 2 ) $ret = fQuery::$useDatabase->e( $arr[0].",".$arr[1] );
	else $ret = "0,20";
	$ret = sprintf("LIMIT %s", $ret);
	$q->add_sql( $ret, 3, 1);
	
}

function fq_grp( $params, $col, $q ) {
	
	if ($params && in_array( $params, array('ASC', 'DESC') )) {
		$ret = fQuery::$useDatabase->e( $col ) . " " . fQuery::removeQuotes( $params );
	} else {
		$ret =  fQuery::$useDatabase->e( $col );
	}
	$ret = "GROUP BY ".$ret;
	$q->add_sql( $ret, 2, 1 );
	
}

function fq_count( $params, $col, $q ) {
	return "COUNT(" . fQuery::$useDatabase->e( $col ) . ")";	
}

function fq_order( $params, $col, $q ) {
	$ret = fQuery::$useDatabase->e( $col ) . " " . fQuery::removeQuotes( $params ) . ""; 
	$ret = sprintf( "ORDER BY %s", $ret );
	$q->add_sql( $ret, 2, 2 );
}

//add function
fQueryAddFunction ( "or", "fq_or" );
fQueryAddFunction ( "limit", "fq_lim" );
fQueryAddFunction ( "group", "fq_grp" );
fQueryAddFunction ( "count", "fq_count" );
fQueryAddFunction ( "order", "fq_order" );

Fn::add( 'fQuery', function() {
	call_user_func_array( 'fQuery', func_get_args() );
}); //needs to be separate or it will mess up. This isn't even used often

?>