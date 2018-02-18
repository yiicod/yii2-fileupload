<?php

namespace yiicod\fileupload\components;

use Exception;
use UploadHandler as BaseUploadHandler;
use yii\helpers\ArrayHelper;
use yiicod\fileupload\components\base\AccessUploadInterface;
use yiicod\fileupload\components\base\EventsUploaderInterface;
use yiicod\fileupload\components\base\UploaderInterface;

/**
 * Class UploadHandler
 *
 * @package yiicod\fileupload\libs
 */
class UploadHandlerOld extends BaseUploadHandler
{
    /**
     * @var array
     */
    protected $clientOptions;

    /**
     * UploadHandler constructor.
     *
     * @param callable $userPostFunc
     * @param array $options
     * @param bool $initialize
     * @param array $error_messages
     */
    public function __construct($clientOptions, $options = null, $initialize = true, $error_messages = null)
    {
        $this->clientOptions = $clientOptions;

        parent::__construct($options, $initialize, $error_messages);
    }

    /**
     * Post trigger.
     *
     * @param bool $print_response
     * @param bool $print_response
     *
     * @throws Exception
     */
    public function post($print_response = true)
    {
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $this->fileTypeExecutable($upload['tmp_name'][$index]);
            }
        } else {
            $this->fileTypeExecutable($upload['tmp_name']);
        }

        $result = parent::post($print_response);

        if ($this->canDo()) {
            $can = true;
            list($userData, $uploader) = array_values($this->clientOptions);
            /** @var UploaderInterface $inst */
            $uploader = new $uploader();
            if (false === is_a($uploader, UploaderInterface::class)) {
                throw new Exception('Uploader class must be instanceof UploaderInterface', 500);
            }
            if (true === $can && true === is_a($uploader, AccessUploadInterface::class)) {
                $can = $uploader->can($userData);
            }
            if (true === $can && true === is_a($uploader, EventsUploaderInterface::class)) {
                $can = $uploader->beforeUploading($userData);
            }
            if (true === $can) {
                foreach ($result[$this->options['param_name']] as $i => $fileData) {
                    $result[$this->options['param_name']][$i] = $this->onFileUpload($uploader, $fileData, $userData);
                }
            }
            if (true === $can && true === is_a($uploader, EventsUploaderInterface::class)) {
                $uploader->afterUploading($userData, $result[$this->options['param_name']]);
            }
        }

        return $this->generate_response($result, $print_response);
    }

    /**
     * Callback on post end method.
     *
     * @param $fileData
     *
     * @return array
     *
     * @throws Exception
     */
    public function onFileUpload($uploader, $fileData, $userData)
    {
        $fileData = array_diff_key((array)$fileData, array_flip([
            'deleteUrl', 'deleteType', 'url',
        ]));
        $path = $this->get_upload_path();
        $filePath = (rtrim($path, '/') . '/' . $fileData['name']);

        if (false === isset($fileData['error'])) {
            $fileData['isSuccess'] = true;

            $result = $uploader->uploading($filePath, $userData, $fileData);

            if (is_array($result)) {
                $fileData = ArrayHelper::merge($fileData, $result);
            }
        } else {
            $fileData['isSuccess'] = false;
            $fileData = array_diff_key($fileData, array_flip([
                'url',
            ]));
        }

        return $fileData;
    }

    protected function canDo()
    {
        //Callback
        $content_range = $this->get_server_var('HTTP_CONTENT_RANGE') ?
            preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;

        if (($this->get_server_var('HTTP_CONTENT_RANGE') && $content_range[3] - 1 == $content_range[2]) ||
            !$this->get_server_var('HTTP_CONTENT_RANGE')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $file
     *
     * @throws Exception
     */
    protected function fileTypeExecutable($file)
    {
        if ($file && 'text/x-php' == mime_content_type($file)) {
            throw new Exception('File type was blocked', 403);
        }
    }

    /**
     * @param $file_path
     * @param $name
     * @param $size
     * @param $type
     * @param $error
     * @param $index
     * @param $content_range
     *
     * @return mixed|string
     */
    protected function trim_file_name($file_path, $name, $size, $type, $error, $index, $content_range)
    {
        $name = preg_replace('/[^A-Za-z0-9\-\.]/', '_', $name);

        return parent::trim_file_name($file_path, $name, $size, $type, $error, $index, $content_range);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    protected function upcount_name($name)
    {
        return preg_replace_callback(
            '/(?:(?:_([\d]+))?(\.[^.]+))?$/', [$this, 'upcount_name_callback'], $name, 1
        );
    }

    /**
     * @param $matches
     *
     * @return string
     */
    protected function upcount_name_callback($matches)
    {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';

        return '_' . $index . $ext;
    }
}
