<?php

namespace yiicod\fileupload\themes;

use yii\base\Object;
use yiicod\fileupload\widgets\FileUpload;

/**
 * Class ThemeAbstract
 * Main theme abstract
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\components\base
 */
abstract class ThemeAbstract extends Object implements ThemeInterface
{
    /**
     * File upload widget instance
     *
     * @var FileUpload
     */
    protected $widget;

    /**
     * ThemeAbstract constructor.
     *
     * @param FileUpload $widget
     * @param array $config
     */
    public function __construct(FileUpload $widget, array $config = [])
    {
        $this->widget = $widget;

        parent::__construct($config);
    }
}
