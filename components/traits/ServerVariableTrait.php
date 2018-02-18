<?php

namespace yiicod\fileupload\components\traits;

/**
 * Trait ServerVariableTrait
 *
 * @package yiicod\fileupload\common
 */
trait ServerVariableTrait
{
    /**
     * Get server variable
     *
     * @param string $name
     */
    protected function getServerVar(string $name)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
    }
}
