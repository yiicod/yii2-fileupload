<?php

namespace yiicod\fileupload\validators;

use yii\base\BaseObject;
use yiicod\fileupload\components\common\UploadedFile;

/**
 * Class FileUploadValidator
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\validators
 */
class FileUploadValidator extends BaseObject implements ValidatorInterface
{
    /**
     * Error message
     *
     * @var string
     */
    public $messages = [
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
    ];

    /**
     * Validate file
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function validate(UploadedFile &$file): bool
    {
        if ($file->error) {
            $file->error = $this->getErrorMessage($file->error);

            return false;
        }

        return true;
    }

    /**
     * Get error message
     *
     * @param $error
     *
     * @return mixed
     */
    protected function getErrorMessage($error)
    {
        return isset($this->messages[$error]) ? $this->messages[$error] : $error;
    }
}
