<?php

namespace yiicod\fileupload\events;

use yii\base\Event;

/**
 * Class RemoveFile
 * Remove file event
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\events
 */
class RemoveFile extends Event
{
    /**
     * Removed field file name
     *
     * @var array
     */
    public $field;

    /**
     * Upload directory path
     *
     * @var array
     */
    public $uploadDir;

    /**
     * Folder path
     *
     * @var
     */
    public $folderPath;

    /**
     * RemoveFile constructor.
     *
     * @param string $field
     * @param string $uploadDir
     * @param string $folderPath
     * @param array $config
     */
    public function __construct(string $field, string $uploadDir, string $folderPath, array $config = [])
    {
        parent::__construct($config);

        $this->field = $field;
        $this->uploadDir = $uploadDir;
        $this->folderPath = $folderPath;
    }
}
