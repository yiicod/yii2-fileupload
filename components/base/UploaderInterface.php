<?php

namespace yiicod\fileupload\components\base;

/**
 * Interface UploaderInterface
 *
 * @package yiicod\fileupload\components\base
 */
interface UploaderInterface
{
    /**
     * On image uploaded action
     *
     * @param string $filePath full file name
     * @param array $userData additional data
     * @param array $fileData upload results data
     *
     * @return array result
     */
    public function upload(string $filePath, array $userData, array $fileData): array;

    /**
     * Remove file
     *
     * @param array $userData
     *
     * @return array
     */
    public function remove(array $userData): array;
}
