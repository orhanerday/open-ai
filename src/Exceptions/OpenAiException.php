<?php

namespace Ze\OpenAi\Exceptions;

use Exception;
use Throwable;

class OpenAiException extends Exception
{
    public function __construct($message = "Http error occurred !", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
