<?php

namespace Ferntastic\fQuery;

use Ferntastic\Drivers\Common\DriverImplementation;
use Ferntastic\Drivers\Database\Schema\Driver as DatabaseDriver;
use Ferntastic\Errors\fQueryError;


/**
 * fQuery is the Ferns Query Object used for all DB-based websites. It cycles through the DB
 * like Wordpress cycles through posts. It has become the database interface
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.3
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

class fQuery extends DriverImplementation {

    protected static $instance = NULL;
    protected function __construct() {
        return $this;
    }

    public static function Invoke() {
        if (self::$instance === NULL) self::$instance = new self();
        return self::$instance;
    }

    protected $DefaultDriver = 'Ferntastic\\Drivers\\Database\\MySQL'; //overrides class category
	
	public function Create(/*$context, $selectors = null*/) {
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

	
	function __invoke(/* mixed_params */) {
		return call_user_func_array( 'fQuery', func_get_args() );
	}

}
