<?php

namespace yiicod\fileupload\components\common;

use yii\base\NotSupportedException;
use yii\helpers\Inflector;

/**
 * Class FileName
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\components\base
 */
class FileName
{
    /**
     * @var int|null
     */
    protected $length;

    /**
     * @var string
     */
    protected $uploadDir;

    /**
     * FileName constructor.
     *
     * @param string $uploadDir
     * @param int|null $length
     */
    public function __construct(string $uploadDir, ?int $length)
    {
        $this->uploadDir = $uploadDir;
        $this->length = $length;
    }

    /**
     * Get file name
     *
     * @param UploadedFile $uploadedFile
     *
     * @return mixed
     */
    public function getFileName(UploadedFile $uploadedFile)
    {
        $uploadedFile->name = $this->trimFileName($uploadedFile);

        return $this->getUniqueFileName($uploadedFile);
    }

    /**
     * Trim file name
     *
     * @param UploadedFile $uploadedFile
     *
     * @return bool|mixed|string
     *
     * @throws NotSupportedException
     */
    protected function trimFileName(UploadedFile $uploadedFile)
    {
        if (!preg_match('/([^.]*)(\.[^.]+)$/', $uploadedFile->name, $matches)) {
            throw new NotSupportedException('Unsupported file name');
        }
        $name = Inflector::slug($matches[1]) . $matches[2];

        if ($this->length) {
            $name = substr(strrev($name), 0, $this->length);
            $name = strrev($name);
        }

        $name = trim($this->getBasename(stripslashes($name)), ".\x00..\x20");
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }

        return $name;
    }

    /**
     * Get unique file name
     *
     * @param UploadedFile $uploadedFile
     *
     * @return string
     */
    protected function getUniqueFileName(UploadedFile $uploadedFile)
    {
        $name = $uploadedFile->name;

        while (is_dir($this->getUploadDir($name))) {
            $name = $this->upCountName($name);
        }

        $uploadedBytes = (int)$uploadedFile->contentRange[1];
        while (is_file($this->getUploadDir($name))) {
            if ($uploadedBytes === $this->getFileSize($this->getUploadDir($name))) {
                break;
            }
            $name = $this->upCountName($name);
        }

        return $name;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    protected function upCountName($name)
    {
        return preg_replace_callback('/(?:(?:_([\d]+))?(\.[^.]+))?$/', function ($matches) {
            $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
            $ext = isset($matches[2]) ? $matches[2] : '';

            return '_' . $index . $ext;
        }, $name, 1);
    }

    /**
     * Get file base name
     *
     * @param string $filePath
     * @param null|string $suffix
     *
     * @return bool|string
     */
    protected function getBasename(string $filePath, ?string $suffix = null)
    {
        $splited = preg_split('/\//', rtrim($filePath, '/ '));

        return substr(basename('X' . $splited[count($splited) - 1], $suffix), 1);
    }

    /**
     * Get file size
     *
     * @param string $filePath
     * @param bool $clearStatCache
     *
     * @return float
     */
    protected function getFileSize(string $filePath, bool $clearStatCache = false)
    {
        if ($clearStatCache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $filePath);
            } else {
                clearstatcache();
            }
        }

        return filesize($filePath);
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
