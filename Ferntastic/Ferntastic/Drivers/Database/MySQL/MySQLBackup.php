<?php

namespace Ferntastic\Drivers\Database\MySQL;

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