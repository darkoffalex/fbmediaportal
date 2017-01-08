<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\Post */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;
?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Basic configurations'); ?></h4>
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
        <?= $form->field($model, 'name')->textInput()->label(Yii::t('admin','Internal name')); ?>

        <?= $form->field($model, 'status_id')->dropDownList([
            Constants::STATUS_ENABLED => Yii::t('admin','Enabled'),
            Constants::STATUS_DISABLED => Yii::t('admin','Disabled'),
        ]); ?>

        <?= $form->field($model, 'content_type_id')->dropDownList([
            Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
            Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
            Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
            Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
            Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
        ]); ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
        <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
    </div>

<?php ActiveForm::end(); ?>