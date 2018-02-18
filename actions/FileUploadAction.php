<?php

namespace yiicod\fileupload\actions;

use Exception;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\Response;
use yiicod\base\helpers\LoggerMessage;
use yiicod\fileupload\components\UploadHandler;
use yiicod\fileupload\models\behaviors\FileException;
use yiicod\fileupload\validators\FilesCountValidator;
use yiicod\fileupload\validators\FileSizeValidator;
use yiicod\fileupload\validators\FileTypeValidator;
use yiicod\fileupload\validators\FileUploadValidator;
use yiicod\fileupload\validators\PostSizeValidator;
use yiicod\fileupload\widgets\FileUpload;

/**
 * FileUploadAction
 *
 * @author Orlov Alexey <Orlov.Alexey@zfort.net>
 */
class FileUploadAction extends Action
{
    /**
     * Min file size.
     *
     * @var int
     */
    public $minFileSize = 0;

    /**
     * Max file size, 10 MB.
     *
     * @var int
     */
    public $maxFileSize = 10000000;

    /**
     * Max uploads for one time. null is unlimited.
     *
     * @var int
     */
    public $maxCountOfFiles;

    /**
     * Upload dir path ( For temp ).
     *
     * @var string
     */
    public $uploadDir;

    /**
     * File url ( For temp ).
     *
     * @var string
     */
    public $uploadUrl;

    /**
     * File name length
     *
     * @var int
     */
    public $fileNameLength = 40;

    /**
     * Allowed extensions, this validate at first on client side,
     * then on server side.
     *
     * @var array
     */
    public $allowedExtensions = [];

    /**
     * List of validators for
     *
     * @var array
     */
    public $validators = [];

    /**
     * on action init
     *
     * @throws HttpException
     */
    public function init()
    {
        if (false === Yii::$app->request->isPost) {
            throw new HttpException('Incorrect request type', 400);
        }

        if (empty($this->uploadUrl)) {
            $this->uploadUrl = str_replace(Yii::getAlias('@webroot'), trim(Url::base(true), '/'), $this->uploadDir);
        }
    }

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
            $options = [
                'upload_dir' => rtrim($this->uploadDir, '/') . '/',
                'upload_url' => rtrim($this->uploadUrl, '/') . '/',
                'file_name_length' => $this->fileNameLength,
            ];
            $validators = $this->prepareValidators();
            $fileHandler = new UploadHandler($options, $validators);

            return $this->controller->asJson($fileHandler->upload($payload['uploader'], $payload['userData']));
        } catch (FileException $e) {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_RAW;
            $response->setStatusCode($e->getCode(), $e->getError());
        } catch (Exception $e) {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_RAW;
            $response->setStatusCode($e->getCode(), $e->getMessage());
        }

        Yii::error(LoggerMessage::log($e), __METHOD__);

        return $response;
    }

    /**
     * Prepare validators
     *
     * @return array
     */
    protected function prepareValidators(): array
    {
        return ArrayHelper::merge([
            'FileUploadValidator' => [
                'class' => FileUploadValidator::class,
                'messages' => [
                    1 => Yii::t('fileupload', 'The uploaded file exceeds the upload_max_filesize directive in php.ini'),
                    2 => Yii::t('fileupload', 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
                    3 => Yii::t('fileupload', 'The uploaded file was only partially uploaded'),
                    4 => Yii::t('fileupload', 'No file was uploaded'),
                    6 => Yii::t('fileupload', 'Missing a temporary folder'),
                    7 => Yii::t('fileupload', 'Failed to write file to disk'),
                    8 => Yii::t('fileupload', 'A PHP extension stopped the file upload'),
                ],
            ],
            'PostSizeValidator' => [
                'class' => PostSizeValidator::class,
                'message' => Yii::t('fileupload', 'The uploaded file exceeds the post_max_size directive in php.ini'),
            ],
            'FileTypeValidator' => [
                'class' => FileTypeValidator::class,
                'allowedExtensions' => $this->allowedExtensions,
                'message' => Yii::t('fileupload', 'File type is not allowed'),
            ],
            'FileSizeValidator' => [
                'class' => FileSizeValidator::class,
                'minFileSize' => $this->minFileSize,
                'maxFileSize' => $this->maxFileSize,
                'message' => Yii::t('fileupload', 'File size should be in range {minFileSize} and {maxFileSize} bytes'),
            ],
            'FilesCountValidator' => [
                'class' => FilesCountValidator::class,
                'maxCountOfFiles' => $this->maxCountOfFiles,
                'uploadDir' => $this->uploadDir,
                'message' => Yii::t('fileupload', 'Maximum number of files exceeded'),
            ],
        ], $this->validators);
    }
}
