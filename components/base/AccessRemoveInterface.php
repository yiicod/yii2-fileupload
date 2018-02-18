<?php

namespace yiicod\fileupload\components\base;

/**
 * Interface AccessRemoveInterface
 *
 * @package yiicod\fileupload\components\base
 */
interface AccessRemoveInterface
{
    /**
     * Check remove access
     *
     * @param array $userData additional data
     *
     * @return bool result
     */
    public function canRemove(array $userData): bool;

    /**
     * Denied message
     *
     * @param array $userData
     *
     * @return string
     */
    public function deniedRemove(array $userData): string;
}
