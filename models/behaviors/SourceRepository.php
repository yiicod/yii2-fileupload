<?php

namespace yiicod\fileupload\models\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Application;

/**
 * Class SourceRepository
 *
 * @package yiicod\fileupload\models\behaviors
 */
class SourceRepository implements SourceRepositoryInterface
{
    /**
     * @var ActiveRecord
     */
    public $owner;

    /**
     * The public url for file
     * 'url' => 'uploads'
     */
    public $uploadUrl;

    /**
     * The directory path for file
     * 'uploadDir' => Yii::getAlias('@webroot/uploads')
     */
    public $uploadDir;

    /**
     * Get relative folder path
     *
     * @return string
     */
    public function getRelativeFolderPath(): string
    {
        $primaryKey = ActiveRecord::getDb()
            ->getSchema()
            ->getTableSchema(get_class($this->owner)::collectionName())
            ->primaryKey;

        return '/' . lcfirst(get_class($this->owner)::tableName()) . '/' . $this->owner->{$primaryKey};
    }

    /**
     * Get folder path
     *
     * @param string $field Field name
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @return string Return path to entity img with|out field name
     */
    public function getFolderPath(string $field = ''): string
    {
        $path = rtrim($this->getUploadDir(), '/') . $this->getRelativeFolderPath();

        if ('' === $field) {
            return $path;
        }

        return rtrim($path, '/') . '/' . $field . '/';
    }

    /**
     * Get file path by "getFolderPath"
     *
     * @param string $field Field name
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @return string Return path to file
     */
    public function getFilePath(string $field): string
    {
        return rtrim($this->getFolderPath($field), '/') . '/' . $this->owner->{$field};
    }

    /**
     * Get file type
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @param string $field Field name
     * @param bool $full Full or not full file type
     *
     * @todo If no need then delete in the next version
     *
     * @return string Return file type
     */
    public function getFileType(string $field, $full = true)
    {
        $filePath = $this->getFilePath($field);
        if (empty($this->owner->{$field}) || !file_exists($filePath)) {
            return '';
        }

        $mimeType = FileHelper::getMimeType($filePath);
        if (null === $mimeType) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $mimeType = FileHelper::getMimeTypeByExtension($ext);
        }

        if (true === $full) {
            return $mimeType;
        }

        $mimeContentType = explode('/', $mimeType);

        return isset($mimeContentType[1]) ? $mimeContentType[1] : false;
    }

    /**
     * Get file src
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @param $field
     * @param null $default
     *
     * @return string File src
     *
     * @throws Exception
     */
    public function getFileSrc(string $field, $default = null): string
    {
        if (empty($this->owner->{$field})) {
            $url = null === $default ? '' : $default;
        } else {
            $url = '';
            $url .= $this->getUploadUrl();
            $url .= rtrim($this->getRelativeFolderPath(), '/') . '/' . $field . '/' . $this->owner->{$field};
        }

        return $url;
    }

    /**
     * Base upload dir
     *
     * @return string
     */
    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }

    /**
     * Base upload url
     *
     * @return string
     */
    public function getUploadUrl(): string
    {
        if (Yii::$app instanceof Application) {
            $this->uploadUrl = 0 === strpos($this->uploadUrl, Url::base(true)) ?
                $this->uploadUrl : trim(Url::base(true), '/') . '/' . trim($this->uploadUrl, '/');
        }

        return $this->uploadUrl;
    }
}
