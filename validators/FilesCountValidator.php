<?php

namespace yiicod\fileupload\validators;

use yii\base\Object;
use yiicod\fileupload\components\common\UploadedFile;
use yiicod\fileupload\components\traits\ServerVariableTrait;

/**
 * Class FilesCountValidator
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\validators
 */
class FilesCountValidator extends Object implements ValidatorInterface
{
    use ServerVariableTrait;

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'Maximum number of files exceeded';

    /**
     * Minimum file size
     *
     * @var int
     */
    public $maxCountOfFiles;

    /**
     * File upload path
     *
     * @var string
     */
    public $uploadDir;

    /**
     * Validate file
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function validate(UploadedFile &$file): bool
    {
        if (is_int($this->maxCountOfFiles) &&
            ($this->countFileObjects() >= $this->maxCountOfFiles) &&
            !is_file($this->getUploadDir($file->name))) {
            $file->error = $this->message;

            return false;
        }

        return true;
    }

    /**
     * Count file objects
     *
     * @return int
     */
    protected function countFileObjects(): int
    {
        return count($this->getFileObjects('is_valid_file_object'));
    }

    /**
     * Get file objects
     *
     * @param string $iterationMethod
     *
     * @return array
     */
    protected function getFileObjects(string $iterationMethod = 'get_file_object'): array
    {
        $uploadDir = $this->getUploadDir();

        if (!is_dir($uploadDir)) {
            return [];
        }

        return array_values(array_filter(array_map(
            [$this, $iterationMethod],
            scandir($uploadDir)
        )));
    }

    /**
     * Get upload path
     *
     * @param null|string $fileName
     * @param null|string $version
     *
     * @return string
     */
    protected function getUploadDir(?string $fileName = null, ?string $version = null)
    {
        $fileName = $fileName ? $fileName : '';
        if (empty($version)) {
            $versionPath = '';
        } else {
            $versionPath = $version . '/';
        }

        return $this->uploadDir . $versionPath . $fileName;
    }
}
