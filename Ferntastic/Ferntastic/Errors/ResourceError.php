<?php

namespace Ferntastic\Errors;

/**
 * ResourceError is the Loggable Error thrown when something goes wrong with resources.
 *
 * @package errors
 * @version 0.1
 *
 */

class ResourceError extends LogError {private $type = __CLASS__;}