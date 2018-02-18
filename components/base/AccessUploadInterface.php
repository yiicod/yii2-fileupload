<?php

namespace yiicod\fileupload\components\base;

/**
 * Interface AccessUploaderInterface
 *
 * @package yiicod\fileupload\components\base
 */
interface AccessUploadInterface
{
    /**
     * Check uploading access
     *
     * @param array $userData additional data
     *
     * @return bool result
     */
    public function canUpload(array $userData): bool;

    /**
     * Denied message
     *
     * @param array $userData
     *
     * @return string
     */
    public function deniedUpload(array $userData): string;
}
