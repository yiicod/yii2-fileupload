<?php

use yii\helpers\Html;

/* @var array $options */
/* @var \yii\base\Model $model */
/* @var string $attribute */
/* @var string $buttonText */
/* @var bool $multiple */
/* @var string $allowedAccept */
?>
<?= Html::beginTag('div', $options); ?>
<span class="fileinput-button dropzone">
    <span class="button-wrap"><?= $buttonText; ?></span>
    <input class="fileupload" type="file"
           name="files[]"<?= $multiple ? ' multiple' : ''; ?><?= $allowedAccept ? ' accept=' . $allowedAccept : ''; ?>>
</span>
<?= Html::endTag('div'); ?>
<?= (false === is_null($model) && property_exists($model, $attribute)) ? Html::activeHiddenInput($model, $attribute) : ''; ?>
