<?php

namespace yiicod\fileupload\models\behaviors;

interface TmpRepositoryInterface
{
    /**
     * Generate session key for file
     *
     * @param string $field Field name
     *
     * @return string
     */
    public function generateKey(string $field): string;

    /**
     * Save file to session, after move to "getFilePath"
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @param string $file
     * @param string $field
     *
     * @return bool
     */
    public function setFile(string $file, string $field): bool;

    /**
     * Get file by session key
     *
     * @param string $field
     *
     * @return string Return string
     */
    public function getFile(string $field): string;

    /**
     * Get file name
     *
     * @param string $field
     *
     * @return string Return string
     */
    public function getFileName(string $field): string;

    /**
     * Remove file by session key
     *
     * @param string $field
     *
     * @return bool
     */
    public function removeFile(string $field): bool;
}
