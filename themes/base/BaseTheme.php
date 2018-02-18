<?php

namespace yiicod\fileupload\themes\base;

use Yii;
use yii\helpers\ArrayHelper;
use yiicod\fileupload\themes\ThemeAbstract;

/**
 * Class BaseTheme
 * Base file upload widget theme
 *
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\themes\base
 */
class BaseTheme extends ThemeAbstract
{
    /**
     * Base view name.
     *
     * @var string
     */
    public $viewAlias = '@yiicod/yii2-fileupload/themes/base/views/base';

    /**
     * Button text.
     *
     * @var string
     */
    public $buttonText = 'Find & Upload';

//    /**
//     * Button text on drag&drop.
//     *
//     * @var string
//     */
//    public $dropFilesText = 'Drop Files Here';

    /**
     * Multiple file upload
     *
     * @var bool
     */
    public $multiple = false;

    /**
     * Accept type
     *
     * @var string
     */
    public $allowedAccept = 'video/*,image/*';

    /**
     * jQuery file upload options (client side).
     *
     * @var array
     */
    public $uploaderClientOptions = [];

    /**
     * Theme options
     *
     * @var array
     */
    public $themeClientOptions = [];

    /**
     * Get theme view name
     *
     * @return string
     */
    public function getViewAlias(): string
    {
        return $this->viewAlias;
    }

    /**
     * Get theme view data
     *
     * @return array
     */
    public function getViewData(): array
    {
        return [
            'buttonText' => $this->buttonText,
//            'dropFilesText' => $this->dropFilesText,
            'multiple' => $this->multiple,
            'allowedAccept' => $this->allowedAccept,
            'options' => [
                'uploader-params' => $this->getUploaderClientOptions(),
            ],
        ];
    }

    /**
     * Register theme client scripts
     */
    public function registerClientScripts()
    {
        BaseThemeAsset::register($this->widget->getView());
    }

    /**
     * @return array
     */
    protected function getUploaderClientOptions(): array
    {
        $clientOptions = ArrayHelper::merge([
            'dataType' => 'json',
            'minFileSize' => 0,
            'maxFileSize' => 10000000,
            'maxChunkSize' => 20000000,
            'maxNumberOfFiles' => '',
            'dropZone' => '#' . $this->widget->id . ' .dropzone',
            'messages' => [
                'maxNumberOfFiles' => Yii::t('fileupload', 'Maximum number of files exceeded'),
                'acceptFileTypes' => isset($this->uploaderClientOptions['acceptFileTypes']) ? Yii::t('fileupload', 'File type not allowed. Allowed extensions are: {extensions}', [
                    'extensions' => implode(',', $this->uploaderClientOptions['acceptFileTypes']),
                ]) : '',
                'maxFileSize' => Yii::t('fileupload', 'File is too large'),
                'minFileSize' => Yii::t('fileupload', 'File is too small'),
            ],
        ], $this->uploaderClientOptions);

        $clientOptions['url'] = $this->widget->generateUrl($this->widget->uploadUrl);

        return [
            'config' => $clientOptions,
//            'messages' => [
//                'done' => Yii::t('fileupload', 'Done.'),
//                'error' => Yii::t('fileupload', 'Error.'),
//                'drop' => $this->dropFilesText,
//                'select' => $this->buttonText
//            ],
        ];
    }

    /**
     * Get wrapper client options
     *
     * @return array
     */
    protected function getThemeClientOptions(): array
    {
        return ArrayHelper::merge([
            'error' => $this->widget->model->getFirstError($this->widget->attribute),
            'buttonText' => $this->buttonText,
//            'dropFilesText' => $this->dropFilesText,
            'multiple' => $this->multiple,
            'allowedAccept' => $this->allowedAccept,
        ], $this->themeClientOptions);
    }
}
