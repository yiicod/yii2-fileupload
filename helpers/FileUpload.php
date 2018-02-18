<?php

namespace yiicod\fileupload\helpers;

use Yii;

/**
 * File upload helper.
 *
 * @author Orlov Alexey <aaorlov88@gmail.com>
 */
class FileUpload
{
    /**
     * Recursive delete the directory and all its files.
     *
     * @param string $dir File or folder that should be deleted
     */
    public static function rrmdir(string $dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ('.' != $object && '..' != $object) {
                    if ('dir' == filetype($dir . '/' . $object)) {
                        self::rrmdir($dir . '/' . $object);
                    } else {
                        if (false === @unlink($dir . '/' . $object)) {
                            Yii::error(sprintf('Can not "unlink" %s', $dir . '/' . $object), __METHOD__);
                        }
                    }
                }
            }
            reset($objects);
            if (false === @rmdir($dir)) {
                Yii::error(sprintf('Can not "rmdir" %s', $dir . '/' . $object), __METHOD__);
            }
        }
    }

    /**
     * Remove file by path
     *
     * @param string $file
     */
    public static function rrmfile(string $file)
    {
        if (false === @unlink($file)) {
            Yii::error(sprintf('Can not "unlink" %s', $file), __METHOD__);
        }
    }
}
