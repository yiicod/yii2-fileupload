<?php

namespace yiicod\fileupload\validators;

use yii\base\Object;
use yiicod\fileupload\components\common\UploadedFile;
use yiicod\fileupload\components\traits\ServerVariableTrait;

/**
 * Class PostSizeValidator
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\validators
 */
class PostSizeValidator extends Object implements ValidatorInterface
{
    use ServerVariableTrait;

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'The uploaded file exceeds the post_max_size directive in php.ini';

    /**
     * Validate file
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function validate(UploadedFile &$file): bool
    {
        $contentLength = (int)$this->getServerVar('CONTENT_LENGTH');
        $postMaxSize = $this->getConfigBytes(ini_get('post_max_size'));
        if ($postMaxSize && ($contentLength > $postMaxSize)) {
            $file->error = $this->message;

            return false;
        }

        return true;
    }

    /**
     * Get config bytes
     *
     * @param $val
     *
     * @return float
     */
    protected function getConfigBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int)$val;

        switch ($last) {
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}
