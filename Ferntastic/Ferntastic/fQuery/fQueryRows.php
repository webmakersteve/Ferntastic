<?php

namespace Ferntastic\fQuery;

use Ferntastic\Errors\fQueryError;
use Ferntastic\Formatting\Strings as StringFormatter;


class fQueryRows extends fQuery {

    public function setupDefaultExtensions() {
        $or =  function( $params, $col, $q, $old ) {
            $arr = explode( ',', StringFormatter::removeQuotes( $params ) );

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

        };

        $lim = function( $params, $col, $q ) {

            $arr = explode( ',', StringFormatter::removeQuotes( $params ) ); //split the parameters by commas
            $numArgs = count ( $arr ); //number of arguments base on the split
            if ( $numArgs == 1 ) {
                $ret = fQuery::$useDatabase->e( $arr[0] );
            } elseif ( $numArgs == 2 ) $ret = fQuery::$useDatabase->e( $arr[0].",".$arr[1] );
            else $ret = "0,20";
            $ret = sprintf("LIMIT %s", $ret);
            $q->addStatement( $ret, 3, 1);

        };

        $grp = function( $params, $col, $q ) {

            if ($params && in_array( $params, array('ASC', 'DESC') )) {
                $ret = fQuery::$useDatabase->e( $col ) . " " . StringFormatter::removeQuotes( $params );
            } else {
                $ret =  fQuery::$useDatabase->e( $col );
            }
            $ret = "GROUP BY ".$ret;
            $q->addStatement( $ret, 2, 1 );

        };

        $count = function( $params, $col, $q ) {
            return "COUNT(" . fQuery::$useDatabase->e( $col ) . ")";
        };

        $order = function( $params, $col, $q ) {
            $ret = fQuery::$useDatabase->e( $col ) . " " . StringFormatter::removeQuotes( $params ) . "";
            $ret = sprintf( "ORDER BY %s", $ret );
            $q->addStatement( $ret, 2, 2 );
        };

        //add function
        $this->addExtendedFunction ( $or, "fq_or" );
        $this->addExtendedFunction ( $lim, "fq_lim" );
        $this->addExtendedFunction ( $grp, "fq_grp" );
        $this->addExtendedFunction ( $count, "fq_count" );
        $this->addExtendedFunction ( $order, "fq_order" );

    }

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
     * @var mixed $row_data An array with all the row data in the selection.
     * @access private
     */

    protected $Data = array(); //the row data from the database driver

    /*
     * @var array $curr_row The data for the currently loaded post for EACH statements
     * @access private
     */

    private $curr_row;

    /*
     * @var $lastStatement: statement executed last among all the classes
     * @staticvar string $lastStatement;
     */

    static $lastStatement = ''; //static variable used to access the last SQL statement

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

    static $lastCollection = null;

    /*
     * @var mixed $cols Holds the column data that is being loaded by this fQuery object
     * @access private
     */

    private static $columnCache = array();

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
     * @var mixed $oldStmts Holds the old build query statements executed
     * @name $oldStmts
     */

    private $oldStmts = array();

    /**
     * @var mixed $funcs Holds the functions that are being loaded into fQuery.
     * @staticvar $funcs
     */

    static $funcs = array();

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

    static function addExtendedFunction( $key, $val ) {
        self::$funcs[$key] = $val;
    }

    private $columns = array();



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
     * Function AddStatement adds SQL statements to the $stmt array.
     *
     * Adds the statements into the statement array. Takes 3 parameters. Uses both
     * priorities and places. Places correlate with the '%s' in the string format.
     * For example, if you're adding a column you would use addStatement($stmt, 1); Priority
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

    public function addStatement($stmt, $place=null, $prio=0) {

        if ($place !== null) { //if place is set, use it
            $place = (int) $place;

            if ($prio == 0) $this->stmts[$place]['none'][] = $stmt;
            else $this->stmts[$place]['prio'][$prio] = $stmt;

        } else throw new fQueryError('nosupport'); //if place is not set, kill the program

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
        $inParentheses = false;
        $inSingQuote = false;

        $counter = 0;
        $newString = "";

        $rep = ";;fQuerySelectorSep".md5("Johnson_salt"+rand(0,50000)).";;"; //ridiculous SALT to replace commas with. No one would use this in a statement, right?
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
                    if (!$inQuote and !$inSingQuote) $inParentheses = true;
                    $newString .= $temp;
                    break;

                case ')':
                    if (!$inQuote and !$inSingQuote) if ($inParentheses) $inParentheses = false;
                    $newString .= $temp;
                    break;

                case ',':

                    if (!$inQuote and !$inBrace and !$inBracket and !$inParentheses) {
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
                if (!$this->columnIsInCache($colname) and $colname != "*") continue;
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

            if (!empty($curr)) $this->addStatement($curr, 1);

        endforeach;

        $this->addStatement( $sqlCols, 0, 1); //add into the first position the columns
        return $this->stmts; //build the SQL from the statements and return it to the __construct function
    }

    private function getCurrentContext() {
        if ($this->CollectionName) return $this->CollectionName;
        throw new fQueryError('ucontext');
    }

    private function setCurrentContext($context) {
        $this->CollectionName = $context;
    }

    private function addColumnToCache( $colname, $context=null ) {
        if ($context === null) $this->getCurrentContext();
        self::$columnCache[$context][$colname] = true;
    }

    private function columnIsInCache( $colname, $context=null ) {
        if ($context === null) $this->getCurrentContext();
        return array_key_exists($colname, self::$columnCache[$context]);
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

        /**
         * This is totally different unfortunately,
         * it will have to use that awkward meta syntax that WordPress loves so let's just see
         * where we go with it
         * @todo
         */

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

    private $CollectionName = null;

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

            if ($context == null) $context = (self::$lastCollection) ? self::$lastCollection : null;
            if ($context == null) throw new fQueryError("ucontext"); //no context yields error

            $this->setCurrentContext($context); //sets current context for use in other functions

            // Used to be fQuery::$fQuerySafetyDefault. We force safety now but just need to come up with a way to cache it better
            $columns = $this->getDriver()->getColumns($context);
            foreach( $columns as $col ) {
                $this->addColumnToCache($col); //this safely adds a new column to the cache
            }

            $statements = $this->parse( $selectors );
            $found = $this->getDriver()->Find( $statements, $this->getCurrentContext() );

            $this->oldStmts[] = $statements;

            self::$lastCollection = $context; //set the static variable of context as the default of context

            if ( ($count = count($found) ) > 0) {
                $this->count = $count;
                /**
                 * Now we need to get totla rows found on the last query
                 */
                $this->total_count = $this->getDriver()->fetchTotals();
                $this->Data = $found;
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

        if ($tbl == null) $tbl = self::$lastCollection;
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
            $this->count = count( $this->Data );


        } else return false;
        return true;

    }

    public function this() {
        if ($this->count > 0):
            return new fQueryData($this->Data[$this->current + 1]);
        else:
            return false;
        endif;
    }

    public function extract( &$var ) {
        if ($this->count > 0):
            $var = $this->Data[$this->current + 1];
            return true;
        else:
            return false;
        endif;
    }

    public function reload() {

        $sql = isset($this->oldSQL[count($this->oldSQL)-1]) ? $this->oldSQL[count($this->oldSQL)-1] : $this->oldSQL[0];
        $this->query = self::$useDatabase->query( $sql );

        $this->Data = array();

        if ( ($count = self::$useDatabase->num_rows( $this->query ) ) > 0) {

            $this->count = $count;
            $sql = "SELECT FOUND_ROWS();";
            $q = self::$useDatabase->query( $sql );
            $r = self::$useDatabase->arr( $q );
            $this->total_count = $r[0];

            while ( $r = self::$useDatabase->assoc( $this->query ) ) $this->Data[] = $r;

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

                    $newob = new fQueryData( $this->Data[$this->current] );
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
            $tbl = $this->CollectionName;

            /** PARSE THE UPDATE ARRAY **/

            //$sel is expected to be an array of changes that are in the cols called

            $newstring = "";

            if (is_array($sel) and count($sel) > 0) {
                foreach ( $sel as $the_column => $the_new_value ):

                    if ( $this->columnIsInCache($the_column)) continue;
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
     * Check if there is data to be iterated over.
     *
     */

    private function have_data() {

        $data = $this->Data;
        $index = $this->current;

        @$curr_data = $data[$index+1];

        if ( !isset($curr_data) ) return false;
        else return true;

    }

}
