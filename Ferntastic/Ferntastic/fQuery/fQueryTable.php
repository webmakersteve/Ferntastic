<?php

namespace Ferntastic\fQuery;

use Ferntastic\Errors\fQueryError;

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