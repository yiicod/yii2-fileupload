<?php

namespace yiicod\fileupload\validators;

use yii\base\Object;
use yiicod\fileupload\components\common\UploadedFile;

/**
 * Class FileTypeValidator
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\validators
 */
class FileTypeValidator extends Object implements ValidatorInterface
{
    /**
     * Error message
     *
     * @var string
     */
    public $message = 'File type is not allowed';

    /**
     * Allowed file types
     *
     * @var array
     */
    public $allowedExtensions = [];

    /**
     * Validate file
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function validate(UploadedFile &$file): bool
    {
        if (!preg_match('/\.(' . implode('|', $this->allowedExtensions) . ')$/i', $file->name)) {
            $file->error = $this->message;

            return false;
        }

        return true;
    }
}
