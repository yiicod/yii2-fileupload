<?php

namespace yiicod\fileupload\themes\base;

use yii\web\AssetBundle;
use yiicod\fileupload\widgets\FileUploadJsAsset;

/**
 * Class BaseThemeAsset
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 */
class BaseThemeAsset extends AssetBundle
{
    public $sourcePath = '@yiicod/yii2-fileupload/themes/base/assets';
    public $js = [
        'jquery.fileupload-trigger.js',
    ];
    public $depends = [
        FileUploadJsAsset::class,
    ];
}
