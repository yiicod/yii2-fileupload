<?php

namespace yiicod\fileupload\models\traits;

use Yii;

/**
 * Coco trait uploader
 *
 * @author Orlov Alexey <aaorlov88@gmail.com>
 */
trait FileDownloadTrait
{
    /**
     * Download
     *
     * @param $fileName
     * @param $filePath
     */
    public static function download(string $fileName, string $filePath)
    {
        if (file_exists($filePath)) {
            Yii::$app->response->sendFile($filePath, $fileName);
        }
    }
}
