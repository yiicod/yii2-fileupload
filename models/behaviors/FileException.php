<?php

namespace yiicod\fileupload\models\behaviors;

use Throwable;

class FileException extends \Exception
{
    private $_error;

    public function __construct($error, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->_error = $error;

        parent::__construct($message, $code, $previous);
    }

    public function getError()
    {
        return $this->_error;
    }
}
