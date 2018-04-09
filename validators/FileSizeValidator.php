<?php

namespace yiicod\fileupload\validators;

use yii\base\BaseObject;
use yiicod\fileupload\components\common\UploadedFile;
use yiicod\fileupload\components\traits\ServerVariableTrait;

/**
 * Class FileSizeValidator
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\validators
 */
class FileSizeValidator extends BaseObject implements ValidatorInterface
{
    use ServerVariableTrait;

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'File size should be in range {minFileSize} and {maxFileSize} bytes';

    /**
     * Minimum file size
     *
     * @var int
     */
    public $minFileSize = 0;

    /**
     * Maximum file size
     *
     * @var int
     */
    public $maxFileSize = 10000000;

    /**
     * Validate file
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function validate(UploadedFile &$file): bool
    {
        if ($file->filePath && is_uploaded_file($file->filePath)) {
            $fileSize = filesize($file->filePath);
        } else {
            $fileSize = (int)$this->getServerVar('CONTENT_LENGTH');
        }

        if ($this->maxFileSize && ($fileSize > $this->maxFileSize || $file->size > $this->maxFileSize)) {
            $file->error = $this->getErrorMessage();

            return false;
        }
        if ($this->minFileSize && $fileSize < $this->minFileSize) {
            $file->error = $this->getErrorMessage();

            return false;
        }

        return true;
    }

    /**
     * Prepare and return error message
     *
     * @return string
     */
    protected function getErrorMessage(): string
    {
        return str_replace(['{minFileSize}', '{maxFileSize}'], [$this->minFileSize, $this->maxFileSize], $this->message);
    }
}
