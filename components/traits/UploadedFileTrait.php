<?php

namespace yiicod\fileupload\components\traits;

/**
 * Trait UploadedFileTrait
 *
 * @package yiicod\fileupload\common
 */
trait UploadedFileTrait
{
    /**
     * Get uploaded file data
     *
     * @param $name
     *
     * @return mixed|null
     */
    protected function getUploadData(string $name)
    {
        return isset($_FILES[$name]) ? $_FILES[$name] : null;
    }
}
