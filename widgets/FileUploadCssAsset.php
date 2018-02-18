<?php

namespace yiicod\fileupload\widgets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Class FileUploadCssAsset
 * File upload assets bundle
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\notification\widgets\assets
 */
class FileUploadCssAsset extends AssetBundle
{
    public $sourcePath = '@vendor/blueimp/jquery-file-upload/css';
    public $css = [
        'jquery.fileupload.css',
    ];
    public $depends = [
        YiiAsset::class,
    ];
}
