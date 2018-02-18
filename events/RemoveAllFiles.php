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
class RemoveAllFiles extends Event
{
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
     * @param string $uploadDir
     * @param string $folderPath
     * @param array $config
     */
    public function __construct(string $uploadDir, string $folderPath, array $config = [])
    {
        parent::__construct($config);

        $this->uploadDir = $uploadDir;
        $this->folderPath = $folderPath;
    }
}
