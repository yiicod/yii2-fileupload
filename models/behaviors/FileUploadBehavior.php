<?php

namespace yiicod\fileupload\models\behaviors;

use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yiicod\fileupload\events\RemoveAllFiles;
use yiicod\fileupload\events\RemoveFile;
use yiicod\fileupload\helpers\FileUpload;

/**
 * Coco behavior uploader
 *
 * @author Orlov Alexey <aaorlov88@gmail.com>
 */
class FileUploadBehavior extends Behavior
{
    /**
     * Events
     */
    const EVENT_REMOVE_FILE = 'removeFile';
    const EVENT_REMOVE_ALL_FILES = 'removeAllFiles';

    /**
     * File max name length
     *
     * @var int
     */
    public $maxLength = 50;

    /**
     * An array where keys are fields that contain file
     */
    public $fields = [];
    /**
     * mkdir mode
     *
     * @var int
     */
    public $mode = 0755;

    /**
     * @var array TmpRepositoryInterface config
     */
    public $tmpRepositoryClass = [
        'class' => TmpRepository::class,
    ];

    /**
     * @var array TmpRepositoryInterface config
     */
    public $sourceRepositoryClass = [
        'class' => SourceRepository::class,
        'uploadUrl' => '',
        'uploadDir' => '',
    ];

    /**
     * Prepared values
     *
     * @var array
     */
    protected $fieldsValues = [];

    /**
     * Origin values
     *
     * @var array
     */
    protected $originValues = [];

    /**
     * @var TmpRepositoryInterface
     */
    private $tmpRepository;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * Set tmp file
     *
     * @param $fileName
     * @param $field
     *
     * @return bool
     */
    public function setTmpFile($filePath, $field): bool
    {
        return $this->getTmpRepository()->setFile($filePath, $field);
    }

    /**
     * Get tmp file
     *
     * @param $field
     *
     * @return string
     */
    public function getTmpFile($field): string
    {
        return $this->getTmpRepository()->getFile($field);
    }

    /**
     * Remove file by session key
     *
     * @param string $field
     * @param bool $fieldReset
     */
    public function removeTmpFile(string $field, bool $fieldReset = false)
    {
        $this->getTmpRepository()->removeFile($field);
        if (true === $fieldReset) {
            $this->owner->{$field} = '';
        }
    }

    /**
     * Remove field value and relative physical file
     *
     * @param string $field
     */
    public function removeFile(string $field)
    {
        Event::trigger($this, self::EVENT_REMOVE_FILE, new RemoveFile($field, $this->getSourceRepository()->getUploadDir(), $this->getFilePath($field)));

        $this->owner->{$field} = '';
        $this->owner->save(false);

        $fs = new Filesystem();
        $fs->remove($this->getFilePath($field));
    }

    /**
     * Remove all files relative to model
     */
    public function removeFiles()
    {
        Event::trigger($this, self::EVENT_REMOVE_ALL_FILES, new RemoveAllFiles($this->getSourceRepository()->getUploadDir(), $this->getFolderPath()));

        $fs = new Filesystem();
        $fs->remove($this->getFolderPath());
    }

    /**
     * @return TmpRepositoryInterface
     */
    public function getTmpRepository(): TmpRepositoryInterface
    {
        if (null === $this->tmpRepository) {
            $this->tmpRepository = Yii::createObject($this->tmpRepositoryClass, [$this->owner]);
        }

        return $this->tmpRepository;
    }

    /**
     * @return SourceRepositoryInterface
     */
    public function getSourceRepository(): SourceRepositoryInterface
    {
        if (null === $this->sourceRepository) {
            $this->sourceRepository = Yii::createObject(array_merge($this->sourceRepositoryClass, [
                'owner' => $this->owner,
            ]));
        }

        return $this->sourceRepository;
    }

    /**
     * Get folder path
     *
     * @param string $field Field name
     *
     * @return string Return path to entity img with|out field name
     */
    public function getFolderPath(string $field = ''): string
    {
        return $this->getSourceRepository()->getFolderPath($field);
    }

    /**
     * Get file path by "getFolderPath"
     *
     * @param string $field Field name
     *
     * @return string Return path to file
     */
    public function getFilePath(string $field): string
    {
        return $this->getSourceRepository()->getFilePath($field);
    }

    /**
     * Get file src
     *
     * @param string $field
     * @param null $default
     * @param array $params
     *
     * @return string File src
     */
    public function getFileSrc(string $field, $default = '', array $params = []): string
    {
        $repository = $this->getSourceRepository();
        $result = $repository->getFileSrc($field, $default, $params);

        return $result;
    }

    /**
     * Clean data if was exception or return old data if record is not new
     *
     * @param $field
     */
    protected function cleanOnException($field)
    {
        $this->removeTmpFile($field);
        if (false === $this->owner->isNewRecord) {
            $this->owner->attributes = $this->originValues;
            $this->owner->update();
        }
    }

    /**
     * Prepare field for model
     */
    protected function prepareFields()
    {
        foreach ($this->fields as $field) {
            if (false === isset($this->fieldsValues[$field])) {
                $file = $this->getTmpRepository()->getFile($field);
                if ($file) {
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $basename = basename($file);
                    $maxLength = min(mb_strlen($basename) - mb_strlen($ext) - 1, $this->maxLength - mb_strlen($ext) - 1);
                    $filename = sprintf('%s.%s', mb_substr(basename($file), 0, $maxLength), $ext);
                    $this->owner->{$field} = $filename;
                } elseif (false === file_exists($this->getFilePath($field))) {
                    //@todo Think about this. Because can not be record without files
                    Yii::info('File does not exist: ' . $this->getFilePath($field), 'fileupload');
                }
            }
            $this->fieldsValues[$field] = $this->owner->{$field};
        }
    }

    /**
     * Events
     *
     * @return array
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Save origin attributes to temp data
     *
     * @param Event $event event parameter
     */
    public function afterFind($event)
    {
        foreach ($this->fields as $field) {
            if ($this->owner->hasAttribute($field)) {
                $this->originValues[$field] = $this->owner->{$field};
            }
        }
    }

    /**
     * Prepare fields before validate
     *
     * @param Event $event parameter
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     */
    public function beforeValidate($event)
    {
        $this->prepareFields();
    }

    /**
     * If model save with flag false, call method for prepare field
     *
     * @param Event $event parameter
     */
    public function beforeSave($event)
    {
        $this->prepareFields();
    }

    /**
     * After save, file move in folder for model and delete temp file
     *
     * @param Event $event event parameter
     *
     * @throws Exception
     */
    public function afterSave($event)
    {
        foreach ($this->fields as $field) {
            if ($tmpPath = $this->getTmpFile($field)) {
                if (false === is_dir($this->getFolderPath($field))) {
                    if (false === FileHelper::createDirectory($this->getFolderPath($field), $this->mode, true)) {
                        $this->cleanOnException($field);
                        throw new FileException('Can\'t create directory!', 'Can\'t create directory: ' . $this->getFilePath($field), 500);
                    }
                }
                $this->owner->{$field} = $this->fieldsValues[$field];
                if (!@copy($tmpPath, $this->getFilePath($field))) {
                    $this->cleanOnException($field);
                    throw new FileException('File can\'t copy from!', 'File can\'t copy from ' . $tmpPath . ' to dest: ' . $this->getFilePath($field), 500);
                }
                $this->removeTmpFile($field);
            }
            $this->removeTmpFile($field);
        }
    }

    /**
     * Delete files from the server before removing data from the database.
     *
     * @param Event $event event parameter
     */
    public function afterDelete($event)
    {
        $this->removeFiles();
    }
}
