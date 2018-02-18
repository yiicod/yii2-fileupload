<?php

namespace yiicod\fileupload\validators;

use yiicod\fileupload\components\common\UploadedFile;

/**
 * Interface ValidatorInterface
 *
 * @package yiicod\fileupload\validators
 */
interface ValidatorInterface
{
    /**
     * Validate file
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function validate(UploadedFile &$file): bool;
}
