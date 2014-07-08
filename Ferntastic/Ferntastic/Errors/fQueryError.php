<?php

/**
 * fQuery Error logging functions
 * This file will edit how errors will be handled and integrated with database and mail. Any LogError will be automatically handled after it is thrown.
 * @author Stephen Parente <sparente@91ferns.com>
 * @version 0.1
 * @package php_extensions
 */

namespace Ferntastic\Errors;

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
