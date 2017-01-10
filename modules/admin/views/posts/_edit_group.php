<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\PostGroup */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Group settings'); ?></h4>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'create-group-form',
    'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
    'enableClientValidation'=>false,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}\n",
    ],
]); ?>

    <div class="modal-body">
        <?= $form->field($model, 'name')->textInput()->label(Yii::t('admin','Name')); ?>
        <?= $form->field($model, 'url')->textInput()->label(Yii::t('admin','Group page URL')); ?>
        <?= $form->field($model, 'fb_sync_id')->textInput()->label(Yii::t('admin','FB group ID')); ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
        <button type="button" class="btn btn-primary submit-ajax-btn" data-ajax-form="#create-group-form" data-ok-reload=".reload-ids"><?= Yii::t('admin','Save') ?></button>
    </div>

<?php ActiveForm::end(); ?>
