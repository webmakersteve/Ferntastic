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
 
