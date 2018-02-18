<?php

namespace yiicod\fileupload\widgets;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\widgets\InputWidget;
use yiicod\fileupload\components\base\UploaderInterface;
use yiicod\fileupload\themes\base\BaseTheme;
use yiicod\fileupload\themes\ThemeInterface;

/**
 * Class FileUpload
 * File upload widget
 *
 * @author Alexey Orlov
 * @author Virchenko Maksim <muslim1992@gmail.com>
 *
 * @package yiicod\fileupload\widgets
 */
class FileUpload extends InputWidget
{
    /**
     * Widget id.
     *
     * @var string
     */
    public $id = 'fileupload';

    /**
     * Theme class
     *
     * @var string|array
     */
    public $theme = BaseTheme::class;

    /**
     * Html options.
     *
     * @var array
     */
    public $options = [];

    /**
     * Upload url
     *
     * @var string
     */
    public $uploadUrl = 'site/file-upload';

    /**
     * Remove url
     *
     * @var string
     */
    public $deleteUrl = 'site/file-delete';

    /**
     * Class name
     *
     * @var UploaderInterface string
     */
    public $uploader;

    /**
     * User data, send in receptorClass method.
     *
     * @var array
     */
    public $userData = [];

    /**
     * Run widget
     */
    public function run()
    {
        $theme = $this->buildTheme();
        $this->registerClientScripts();
        $theme->registerClientScripts();

        return $this->render($theme->getViewAlias(), ArrayHelper::merge($theme->getViewData(), [
            'id' => $this->id,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'options' => $this->generateHtmlOptions(),
        ]));
    }

    /**
     * Get theme
     *
     * @return ThemeInterface
     *
     * @throws Exception
     */
    protected function buildTheme(): ThemeInterface
    {
        $theme = null;

        if (is_a($this->theme, ThemeInterface::class)) {
            $theme = $this->theme;
        } elseif (is_array($this->theme)) {
            if (isset($this->theme['class']) && in_array(ThemeInterface::class, class_implements($this->theme['class']))) {
                $theme = new $this->theme['class']($this);

                foreach ($this->theme as $option => $value) {
                    if (property_exists($theme, $option)) {
                        $theme->{$option} = $value;
                    }
                }
            }
        } elseif ($this->theme && in_array(ThemeInterface::class, class_implements($this->theme))) {
            $theme = new $this->theme($this);
        }

        if (is_null($theme)) {
            throw new Exception(sprintf('Theme must be string of className or array("class" => className) ' .
                'and implement "%s"', ThemeInterface::class));
        }

        /* @var ThemeInterface $theme */
        return $theme;
    }

    /**
     * Generate html options for widget.
     *
     * @return array
     */
    public function generateHtmlOptions(): array
    {
        $this->options['id'] = $this->id;

        return $this->options;
    }

    /**
     * Generate upload url.
     *
     * @param string $url
     *
     * @return string
     */
    public function generateUrl(?string $url): ?string
    {
        if (is_null($url)) {
            return null;
        }

        $payload = self::encodeServerOptions([
            'uploader' => $this->uploader,
            'userData' => $this->userData,
        ]);

        if (is_array($url)) {
            return Url::to(ArrayHelper::merge($url, ['data' => $payload]), true);
        }

        return Url::to([$url, 'data' => $payload], true);
    }

    /**
     * Decode server options.
     *
     * @param string $data Data key for decode server options
     *
     * @return array $data Return array of data
     *
     * @throws Exception
     */
    public static function decodeServerOptions($data): array
    {
        $vars = Yii::$app->session->get($data);

        if (false === $vars && isset($_FILES['files'])) {
            throw new Exception('Incorrect file/files', 406);
        }

        $vars = @unserialize(HtmlPurifier::process(base64_decode($vars)));
        if (false === $vars) {
            throw new Exception('Your session was expired. Please reload your page and try again.', 400);
        }

        foreach ($vars['userData'] as $key => $value) {
            if ('?' === $value) {
                $vars['userData'][$key] = HtmlPurifier::process(Yii::$app->request->getQueryParam($key, null));
            }
        }

        return $vars;
    }

    /**
     * Encode server options.
     *
     * @return string $data Return encode array of data
     *
     * @param array $data Server options for uploader
     *
     * @throws Exception
     */
    public static function encodeServerOptions($data): string
    {
        $data = base64_encode(serialize($data));

        Yii::$app->session->set(md5($data), $data);

        return md5($data);
    }

    /**
     * Register client scripts
     */
    protected function registerClientScripts()
    {
        FileUploadJsAsset::register($this->getView());
        FileUploadCssAsset::register($this->getView());
    }
}
