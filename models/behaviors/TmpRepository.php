<?php

namespace yiicod\fileupload\models\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Application;

class TmpRepository implements TmpRepositoryInterface
{
    /**
     * @var ActiveRecord
     */
    private $owner;

    public function __construct($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Generate session key for file
     *
     * @param string $field Field name
     *
     * @return string
     */
    public function generateKey(string $field): string
    {
        return 'file_' . get_class($this->owner)::tableName() . $field;
    }

    /**
     * Save file to session, after move to "getFilePath"
     *
     * @author Orlov Alexey <aaorlov88@gmail.com>
     *
     * @param string $file
     * @param string $field
     *
     * @return bool
     */
    public function setFile(string $file, string $field): bool
    {
        if (Yii::$app instanceof Application) {
            Yii::$app->session->set($this->generateKey($field), $file);
        }

        return true;
    }

    /**
     * Get file by session key
     *
     * @param string $field
     * @param bool $onlyName get only file name
     *
     * @return string Return string
     */
    public function getFile(string $field): string
    {
        if (Yii::$app instanceof Application) {
            $file = Yii::$app->session->get($this->generateKey($field), '');

            return $file;
        }

        return '';
    }

    public function getFileName(string $field): string
    {
        $file = $this->getFile($field);
        $name = end(explode('/', $file));

        return $name;
    }

    /**
     * Remove file by session key
     *
     * @param string $field
     *
     * @return bool
     */
    public function removeFile(string $field): bool
    {
        if (Yii::$app instanceof Application) {
            @unlink(Yii::$app->session->get($this->generateKey($field)));
            Yii::$app->session->set($this->generateKey($field), '');
        }

        return true;
    }
}
