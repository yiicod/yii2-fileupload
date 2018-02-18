<?php

namespace yiicod\fileupload\components;

use Exception;
use stdClass;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yiicod\fileupload\components\base\AccessUploadInterface;
use yiicod\fileupload\components\base\EventsUploaderInterface;
use yiicod\fileupload\components\base\UploaderInterface;
use yiicod\fileupload\components\common\FileName;
use yiicod\fileupload\components\common\UploadedFile;
use yiicod\fileupload\components\traits\ServerVariableTrait;
use yiicod\fileupload\components\traits\UploadedFileTrait;
use yiicod\fileupload\validators\FilesCountValidator;
use yiicod\fileupload\validators\FileSizeValidator;
use yiicod\fileupload\validators\FileTypeValidator;
use yiicod\fileupload\validators\FileUploadValidator;
use yiicod\fileupload\validators\PostSizeValidator;
use yiicod\fileupload\validators\ValidatorInterface;

/**
 * Class UploadHandler
 *
 * @package yiicod\fileupload\libs
 */
class UploadHandler
{
    use ServerVariableTrait,
        UploadedFileTrait;

    /**
     * List of file validators
     *
     * @var array
     */
    public $validators = [
        'FileUploadValidator' => [
            'class' => FileUploadValidator::class,
        ],
        'PostSizeValidator' => [
            'class' => PostSizeValidator::class,
        ],
        'FileTypeValidator' => [
            'class' => FileTypeValidator::class,
        ],
        'FileSizeValidator' => [
            'class' => FileSizeValidator::class,
        ],
        'FilesCountValidator' => [
            'class' => FilesCountValidator::class,
        ],
    ];

    /**
     * @var array
     */
    protected $options;

    /**
     * UploadHandler constructor.
     *
     * @param array $options
     * @param array $validators
     */
    public function __construct(array $options, array $validators = [])
    {
        $this->options = ArrayHelper::merge([
            'upload_dir' => dirname($this->getServerVar('SCRIPT_FILENAME')) . '/files/',
            'mkdir_mode' => 0755,
            'param_name' => 'files',
        ], $options);

        $this->validators = ArrayHelper::merge($this->validators, $validators);
    }

    /**
     * Post trigger.
     *
     * @param string $uploader
     * @param array $userData
     *
     * @return array
     *
     * @throws Exception
     */
    public function upload(string $uploader, array $userData)
    {
        $result = $this->handleUpload();

        $can = $this->canDo();
        if ($can) {
            /** @var UploaderInterface $inst */
            $uploader = new $uploader();
            if (false === is_a($uploader, UploaderInterface::class)) {
                throw new Exception('Uploader class must be instanceof UploaderInterface', 500);
            }
            if ($can && is_a($uploader, AccessUploadInterface::class)) {
                /** @var AccessUploadInterface $uploader */
                $can = $uploader->canUpload($userData);
            }
            if ($can && is_a($uploader, EventsUploaderInterface::class)) {
                /** @var EventsUploaderInterface $uploader */
                $can = $uploader->beforeUploading($userData);
            }
            if ($can) {
                foreach ($result[$this->options['param_name']] as $i => $fileData) {
                    $result[$this->options['param_name']][$i] = $this->onFileUpload($uploader, (array)$fileData, $userData);
                }
            } else {
                foreach ($result[$this->options['param_name']] as $i => $fileData) {
                    $result[$this->options['param_name']][$i]['isSuccess'] = false;
                    if (false === $can && is_a($uploader, AccessUploadInterface::class)) {
                        $result[$this->options['param_name']][$i]['error'] = $uploader->deniedUpload($userData);
                    }
                }
            }
            if ($can && is_a($uploader, EventsUploaderInterface::class)) {
                /* @var EventsUploaderInterface $uploader */
                $uploader->afterUploading($userData, $result[$this->options['param_name']]);
            }
        }

        return $result;
    }

    /**
     * Callback on post end method.
     *
     * @param UploaderInterface $uploader
     * @param $fileData
     * @param $userData
     *
     * @return array
     */
    public function onFileUpload(UploaderInterface $uploader, array $fileData, array $userData)
    {
        $path = $this->getUploadPath();
        $filePath = (rtrim($path, '/') . '/' . $fileData['name']);

        if (false === isset($fileData['error'])) {
            $fileData['isSuccess'] = true;

            $result = $uploader->upload($filePath, $userData, $fileData);

            if (is_array($result)) {
                $fileData = ArrayHelper::merge($fileData, $result);
            }
        } else {
            $fileData['isSuccess'] = false;
        }

        return $fileData;
    }

    /**
     * Handle post upload
     *
     * @return array
     */
    protected function handleUpload(): array
    {
        $upload = $this->getUploadData($this->options['param_name']);
        if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $this->fileTypeExecutable($upload['tmp_name'][$index]);
            }
        } else {
            $this->fileTypeExecutable($upload['tmp_name']);
        }

        // Parse the Content-Disposition header, if available:
        $contentDispositionHeader = $this->getServerVar('HTTP_CONTENT_DISPOSITION');
        $fileName = $contentDispositionHeader ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $contentDispositionHeader
            )) : null;

        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $contentRangeHeader = $this->getServerVar('HTTP_CONTENT_RANGE');
        $contentRange = $contentRangeHeader ?
            preg_split('/[^0-9]+/', $contentRangeHeader) : null;
        $size = $contentRange ? $contentRange[3] : null;

        $files = [];
        if ($upload) {
            if (is_array($upload['tmp_name'])) {
                // param_name is an array identifier like "files[]",
                // $upload is a multi-dimensional array:
                foreach ($upload['tmp_name'] as $index => $value) {
                    $files[] = $this->handleFileUpload(new UploadedFile($upload['tmp_name'][$index],
                        $fileName ? $fileName : $upload['name'][$index],
                        $size ? $size : $upload['size'][$index],
                        $upload['type'][$index],
                        ($upload['error'][$index]) ? $upload['error'][$index] : null,
                        $index,
                        $contentRange));
                }
            } else {
                // param_name is a single object identifier like "file",
                // $upload is a one-dimensional array:
                $files[] = $this->handleFileUpload(new UploadedFile(
                    isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                    $fileName ? $fileName : (isset($upload['name']) ?
                        $upload['name'] : null),
                    $size ? $size : (isset($upload['size']) ?
                        $upload['size'] : $this->getServerVar('CONTENT_LENGTH')),
                    isset($upload['type']) ?
                        $upload['type'] : $this->getServerVar('CONTENT_TYPE'),
                    (isset($upload['error']) && $upload['error']) ? $upload['error'] : null,
                    null,
                    $contentRange));
            }
        }

        return [$this->options['param_name'] => $files];
    }

    /**
     * Can do upload
     *
     * @return bool
     */
    protected function canDo()
    {
        $contentRange = $this->getServerVar('HTTP_CONTENT_RANGE') ?
            preg_split('/[^0-9]+/', $this->getServerVar('HTTP_CONTENT_RANGE')) : null;

        if (($contentRange && $contentRange[3] - 1 == $contentRange[2]) || empty($contentRange)) {
            return true;
        }

        return false;
    }

    /**
     * Handle file upload
     *
     * @param UploadedFile $uploadedFile
     *
     * @return stdClass
     *
     * @throws Exception
     */
    protected function handleFileUpload(UploadedFile $uploadedFile)
    {
        $file = $uploadedFile;
        $file->name = (new FileName($this->options['upload_dir'], $this->options['file_name_length'] ?? null))->getFileName($file);
        $file->size = (int)$file->size;
        if ($this->validate($file)) {
            $uploadDir = $this->getUploadPath();
            if (!is_dir($uploadDir)) {
                FileHelper::createDirectory($uploadDir, $this->options['mkdir_mode'], true);
            }
            $filePath = $this->getUploadPath($file->name);
            $appendFile = $file->contentRange && is_file($filePath) &&
                $file->size > $this->getFileSize($filePath);
            if ($file->filePath && is_uploaded_file($file->filePath)) {
                // multipart/formdata uploads (POST method uploads)
                if ($appendFile) {
                    file_put_contents(
                        $filePath,
                        fopen($file->filePath, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($file->filePath, $filePath);
                }
            } else {
                throw new Exception('File doesn\'t uploaded.');
            }

            $fileSize = $this->getFileSize($filePath, $appendFile);
            if ($fileSize !== $file->size) {
                $file->size = $fileSize;
                if (!$file->contentRange && $this->options['discard_aborted_uploads']) {
                    unlink($filePath);
                    $file->error = Yii::t('yiicod_fileupload', 'File upload aborted.');
                }
            }
        }

        return (object)(array)$file;
    }

    /**
     * Validate file
     *
     * @param $uploadedFile
     *
     * @return bool
     */
    protected function validate(UploadedFile &$uploadedFile)
    {
        foreach ($this->validators as $item) {
            /** @var ValidatorInterface $validator */
            $validator = Yii::createObject($item);
            if (false === $validator->validate($uploadedFile)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get file size
     *
     * @param string $filePath
     * @param bool $clearStatCache
     *
     * @return float
     */
    protected function getFileSize(string $filePath, bool $clearStatCache = false)
    {
        if ($clearStatCache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $filePath);
            } else {
                clearstatcache();
            }
        }

        return filesize($filePath);
    }

    /**
     * Get upload path
     *
     * @param null|string $fileName
     * @param null|string $version
     *
     * @return string
     */
    protected function getUploadPath(?string $fileName = null, ?string $version = null)
    {
        $fileName = $fileName ? $fileName : '';
        if (empty($version)) {
            $versionPath = '';
        } else {
            $versionPath = $version . '/';
        }

        return $this->options['upload_dir'] . $versionPath . $fileName;
    }

    /**
     * Check if uploaded file executable to prevent php injections
     *
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
}
