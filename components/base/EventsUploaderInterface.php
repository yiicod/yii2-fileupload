<?php

namespace yiicod\fileupload\components\base;

/**
 * Interface UploaderInterface
 *
 * @package yiicod\fileupload\components\base
 */
interface EventsUploaderInterface extends UploaderInterface
{
    /**
     * Before uploading action
     *
     * @param array $userData additional data
     *
     * @return bool
     */
    public function beforeUploading(array $userData): bool;

    /**
     * After uploading action
     *
     * @param array $userData Additional data
     * @param array $filesData Files results data
     */
    public function afterUploading(array $userData, array $filesData);
}
