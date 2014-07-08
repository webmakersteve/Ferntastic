<?php

/**
 * JSON Resource Driver
 *
 * I prefer XML because that's the way Android does it, but this is a valid way too. Just uses
 * a combination of key-value pairs
 *
 * @author Stephen Parente (stephen@91ferns.com)
 * @package Drivers
 * @version 0.3
 *
 */

namespace Ferntastic\Drivers\Resources;

use Ferntastic\Drivers\Common\Driver;
use Ferntastic\Drivers\Resources\Schema\Driver as ResourceDriver;
use Ferntastic\Errors\ResourceError;

class JSON extends Driver implements ResourceDriver {

    private $lastDirectory = false;

    protected static $instance = NULL;
    protected function __construct() {
        return $this;
    }

    public static function Invoke() {
        if (self::$instance === NULL) self::$instance = new self();
        return self::$instance;
    }

    public function LoadResources( $Directory ) {
        // The directory is generally going to be the same as it would for XML
        $this->lastDirectory = $Directory;

        if (!is_dir( $Directory) ) throw new ResourceError(ERROR_RESOURCE_NO_DIRECTORY, array('data' => $Directory));
        if (is_readable( $Directory ) && $dir = opendir($Directory)) {
            $_resources = array();
            while (($file = readdir($dir)) !== false) {

                // we only want JSON files in here so let's use the extension
                if ( !preg_match("#[.]json#i", $file) ) continue;
                $json_file = $Directory . DS . $file;

                if (!file_exists($json_file)) throw new ResourceError(ERROR_RESOURCES_DIR_NO_READ, array('file' => $json_file));

                //we now know it has a json at the end, but JSON like XML is finicky. Let's make sure it parses

                $contents = file_get_contents($json_file);

                @$currentJson = json_decode($contents);
                if (!$currentJson || count($currentJson) < 1) continue; //just skip it here

                $catname = preg_replace('#[.]json$#i', '', basename($json_file));
                $currentResourceCategory = &$_resources[$catname];
                $currentResourceAttributes = &$_resources['attr'][$catname];

                //ok so we have the object. It should be
                foreach( $currentJson as $resourceKey => $resourceValue) {
                    /*
                     * We need to support attributes so we need to first check the type of resourceValue
                     */
                    if (is_string($resourceValue)) {
                        $currentResourceCategory->$resourceKey = $resourceValue;
                    } elseif (is_array($resourceValue) && count($resourceValue) > 0) {
                        //this, too is easy-ish.
                        $array = array();
                        foreach( $resourceValue as $resourceValueKey => $resourceValueValue) {
                            //check if the key is a string or integer
                            if (is_numeric($resourceValueKey)) {
                                $array[] = $resourceValueValue;
                            } else $array[$resourceValueKey] = $resourceValueValue;
                        }
                        $currentResourceCategory->$resourceKey = $array;

                    } elseif (is_object($resourceValue)) {
                        //this means it is complex. We will then have value: "" so we need to check again
                        $resourceValueValue = isset($resourceValue->value) ? $resourceValue->value : false;
                        if (!$resourceValueValue) continue;
                        /*
                         * @todo
                         *
                         */
                        $currentResourceCategory->$resourceKey = $resourceValueValue;

                    }
                }


            }
        } else throw new ResourceError(ERROR_RESOURCES_DIR_NO_READ); //end opening the directory

        if ($dir) closedir($dir);
        return $_resources; //we want to use a token of the key to do this so we can reference the specific file later, too

    }

}