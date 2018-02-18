<?php

namespace yiicod\fileupload\components\common;

/**
 * Class FileParams
 * File params for upload handler
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\components\base
 */
class UploadedFile
{
    /**
     * @var string
     */
    public $filePath;

    /**
     * @var string
     */
    public $name;

    /**
     * File size
     *
     * @var int|float
     */
    public $size;

    /**
     * File type
     *
     * @var string
     */
    public $type;

    /**
     * File error
     *
     * @var null|string
     */
    public $error;

    /**
     * File index
     *
     * @var int|null
     */
    public $index;

    /**
     * @var array|null
     */
    public $contentRange;

    /**
     * FileParams constructor.
     *
     * @param string $filePath
     * @param string $name
     * @param $size
     * @param string $type
     * @param null|string $error
     * @param int|null $index
     * @param array|null $contentRange
     */
    public function __construct(string $filePath, string $name, $size, string $type, ?string $error = null, ?int $index = null, ?array $contentRange = null)
    {
        $this->filePath = $filePath;
        $this->name = $name;
        $this->size = $size;
        $this->type = $type;
        $this->error = $error;
        $this->index = $index;
        $this->contentRange = $contentRange;
    }
}
