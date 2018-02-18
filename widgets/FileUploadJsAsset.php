<?php

namespace yiicod\fileupload\widgets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Class FileUploadJsAsset
 * File upload assets bundle
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\notification\widgets\assets
 */
class FileUploadJsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/blueimp/jquery-file-upload/js';
    public $js = [
        'vendor/jquery.ui.widget.js',
        'jquery.fileupload.js',
        'jquery.fileupload-process.js',
        'jquery.fileupload-validate.js',
    ];
    public $depends = [
        YiiAsset::class,
    ];
}
