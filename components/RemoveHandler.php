<?php

namespace yiicod\fileupload\components;

use Exception;
use yiicod\fileupload\components\base\AccessRemoveInterface;
use yiicod\fileupload\components\base\UploaderInterface;

/**
 * Class UploadHandler
 *
 * @package yiicod\fileupload\libs
 */
class RemoveHandler
{
    /**
     * Remove file
     *
     * @param string $uploader
     * @param array $userData
     *
     * @return array
     *
     * @throws Exception
     */
    public function remove(string $uploader, array $userData)
    {
        /** @var UploaderInterface $uploader */
        $uploader = new $uploader();
        if (false === is_a($uploader, UploaderInterface::class)) {
            throw new Exception('Uploader class must be instanceof UploaderInterface', 500);
        }
        if (is_a($uploader, AccessRemoveInterface::class)) {
            /** @var UploaderInterface|AccessRemoveInterface $uploader */
            if ($uploader->canRemove($userData)) {
                return $uploader->remove($userData);
            } else {
                return $uploader->deniedRemove($userData);
            }
        } else {
            return $uploader->remove($userData);
        }
    }
}
