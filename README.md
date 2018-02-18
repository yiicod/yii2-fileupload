File uploader based on blueimp jquery-file-upload
================================================

File uploader base on blueimp jquery-file-upload. You can write easy themes for
uploader. This extension provide you all workflow for upload files on your server.


Install:
--------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiicod/yii2-fileupload "*"
```

or add

```json
"yiicod/yii2-fileupload": "*"
```

to the require section of your composer.json.

Config
------

Set to controller
```php
public function actions()
{
    return array(
        'fileUpload' => [
            'class' => 'yiicod\fileupload\actions\FileUploadAction',
        ],
    );
}
```

Add behavior for model
```php
public function behaviors()
{
    return [
        'FileUploadBehavior' => [
            'class' => 'yiicod\fileupload\models\behaviors\FileUploadBehavior',
            'sourceRepositoryClass' => [
                'class' => SourceRepository::class,
                'uploadDir' => Yii::getAlias('@webroot/uploads'), // Base dir for file
                'uploadUrl' => '/uploads', // Base url to folder
            ],
            'fields' => array('logo'),            
        ],
    ];
}
```

Usage
-----
```php
FileUploadWidget::widget([
    'id' => 'fileuploader',
    'model' => Model::class,
    'attribute' => 'modelAttribute',
    'allowedExtensions' => array('jpeg', 'jpg', 'gif', 'png'),
    'maxFileSize' => 2 * 1024 * 1024, // limit in server-side and in client-side 2mb
    'uploadDir' => Yii::getPathOfAlias('@webroot/uploads/temp'), // temp base dir
    'uploadUrl' => Yii::$app->getBaseUrl(true) . '/uploads/temp/', // temp base url
    'uploader' => 'UserAvatar',
    'userData' => [], // Any data for UploaderInterface
    'maxUploads' => -1, // defaults to -1 (unlimited)   
    'theme' => [
        'class' => BaseTheme::class, //Implements yiicod\fileupload\base\ThemeInterface
        'multiple' => false, // allow to add multiple file uploads
        'buttonText' => 'Upload file',
        'dropFilesText' => 'Upload or Drop here',
        'clientOptions' => array(
            //For chunk uploded
            'maxChunkSize' => 10000000
        ),
        'clientEvents' => array(
            //If is not chunk then
            'done' => 'function(e, data){ $(".avatar").html("<img src=" + data.result.files[0].url + " />"); }'
            //If uses chunk then
            'chunkdone' => 'function(e, data){
                $.fn.yiiListView.update("rewardList"); $("#cocowidget-photo .files").html(" "); 
            }',
        ),
    ],
    'options' => [],
    'defaultUrl' => 'site/fileUpload',    
]);
```

Then add uploader, which extends yiicod\fileupload\base\UploaderInterface and provides functionality to handle uploaded file

Upload immediately
------------------
```php
class UserAvatar implement UploaderInterface {
    /**
     * Event for coco uploader
     * @param string $fullFileName Full file path
     * @param Array $userdata Userdata from widget
     * @param Array $results Uploaded result file
     * @return Array or null
     */
    public function uploading($fullFileName, $userdata, $results)
    {  
        $model = new UserModel();
        //Save to temp
        $model->onAfterFileUploaded($fullFileName, 'logo');
    
        //After save requered set
        if ($model->save()) {
                return [
                    'url' => $model->getFileSrc('logo'),        
                    '...' => '...'
                ];
            )else{
                //Delete temp uploaded file
                $model->resetFile('logo');
                return [
                    'error' => 'Insert error message'
                    '...' => '...'
                ];
            };
        }
    }
}
```

Upload on submit
----------------
```php
class UserAvatar implement UploaderInterface{
    
    /**
     * Event for coco uploader
     * @param string $fullFileName Full file path
     * @param Array $userdata Userdata from widget
     * @param Array $results Uploaded result file
     * @return Array or null
     */
    public function uploading($fullFileName, $userdata, $results)
    { 
        $model = new UserModel();
        //Save to temp
        $model->onAfterFileUploaded($fullFileName, 'logo');
    }
}
```