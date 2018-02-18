<?php

namespace yiicod\fileupload\models\behaviors;

interface SourceRepositoryInterface
{
    /**
     * Get relative folder path
     *
     * @return string
     */
    public function getRelativeFolderPath(): string;

    /**
     * Get folder path
     *
     * @param string $field Field name
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @return string Return path to entity img with|out field name
     */
    public function getFolderPath(string $field): string;

    /**
     * Get file path
     *
     * @param string $field Field name
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @return string Return path to file
     */
    public function getFilePath(string $field): string;

    /**
     * Get file type
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @param string $field Field name
     * @param bool $full Full or not full file type
     *
     * @todo If no need then delete in the next version
     *
     * @return string Return file type
     */
    public function getFileType(string $field, $full = true);

    /**
     * Get file src
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @param $field
     * @param null $default
     *
     * @return string File src
     *
     * @throws Exception
     */
    public function getFileSrc(string $field, $default = null): string;

    /**
     * Base upload url
     *
     * @return string
     */
    public function getUploadUrl(): string;

    /**
     * Base upload dir
     *
     * @return string
     */
    public function getUploadDir(): string;
}
