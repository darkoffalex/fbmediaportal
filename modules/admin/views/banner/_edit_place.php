<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\BannerPlace */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\BannerController */

$controller = $this->context;
?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Banner place configuration'); ?></h4>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'create-post-form',
    'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
    'enableClientValidation'=>false,
    'enableAjaxValidation' => true,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}\n",
        //'labelOptions' => ['class' => 'col-lg-1 control-label'],
    ],
]); ?>

    <div class="modal-body">
        <?= $form->field($model, 'name')->textInput()->label(); ?>
        <?= $form->field($model, 'alias')->textInput()->label()->label(Yii::t('admin','Alias (used in page code)')); ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
        <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
    </div>

<?php ActiveForm::end(); ?>