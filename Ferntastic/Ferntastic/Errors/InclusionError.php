<?php

/**
 * Error logging functions
 * This file will edit how errors will be handled and integrated with database and mail. Any LogError will be automatically handled after it is thrown.
 * @author Stephen Parente <sparente@91ferns.com>
 * @version 0.1
 * @package php_extensions
 */

namespace Ferntastic\Errors;

/**
 * Inclusion Exception class.
 */
 
class InclusionError extends LogError {
}