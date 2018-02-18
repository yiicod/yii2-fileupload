<?php

namespace yiicod\fileupload\actions;

use Exception;
use Yii;
use yii\base\Action;
use yii\web\HttpException;
use yii\web\Response;
use yiicod\base\helpers\LoggerMessage;
use yiicod\fileupload\components\RemoveHandler;
use yiicod\fileupload\widgets\FileUpload;

/**
 * Class FileRemoveAction
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\actions
 */
class FileRemoveAction extends Action
{
    /**
     * Upload file
     *
     * @param string $data
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function run(string $data)
    {
        try {
            $payload = FileUpload::decodeServerOptions($data);
            $fileHandler = new RemoveHandler();

            return $this->controller->asJson($fileHandler->remove($payload['uploader'], $payload['userData']));
        } catch (Exception $e) {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_RAW;
            $response->setStatusCode($e->getCode(), $e->getMessage());

            Yii::error(LoggerMessage::log($e), __METHOD__);

            return $response;
        }
    }
}
