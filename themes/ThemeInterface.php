<?php

namespace yiicod\fileupload\themes;

use yiicod\fileupload\widgets\FileUpload;

/**
 * Interface ThemeInterface
 * Main theme interface
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\components\base
 */
interface ThemeInterface
{
    /**
     * ThemeInterface constructor.
     *
     * @param FileUpload $widget
     */
    public function __construct(FileUpload $widget);

    /**
     * Get theme view
     *
     * @return string
     */
    public function getViewAlias(): string;

    /**
     * Get theme view data
     *
     * @return array
     */
    public function getViewData(): array;

    /**
     * Register client scripts
     *
     * @return mixed
     */
    public function registerClientScripts();
}
