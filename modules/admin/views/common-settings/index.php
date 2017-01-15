<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\models\User;
use app\helpers\Constants;

$this->title = Yii::t('admin','Common settings');
$this->params['breadcrumbs'][] = $this->title;

/* @var $model \app\models\Banner */
?>

<?php Yii::$app->view->registerCssFile('/js/imperavi-redactor/redactor.css'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/redactor.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/lang/ru.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fontsize/fontsize.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fontcolor/fontcolor.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/fullscreen/fullscreen.js'); ?>
<?php Yii::$app->view->registerJsFile('/js/imperavi-redactor/plugins/table/table.js'); ?>

<?php
$editorInit = "
    $('textarea.editor-area').redactor({
        minHeight : 180,
        maxHeight : 180,
        toolbarFixed : false,
        scroll : true,
        autoSize : false,
//        imageUpload: '".Url::to(['/site/upload'])."',
        plugins: ['fontsize','fontcolor','fullscreen','table'],
        lang : '".Yii::$app->language."'
    });";
Yii::$app->view->registerJs($editorInit,\yii\web\View::POS_END);
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Settings'); ?></h3></div>

            <?php $form = ActiveForm::begin([
                'id' => 'edit-banner-form',
                'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
                'enableClientValidation'=>false,
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}\n",
                    //'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
            ]); ?>


            <div class="box-body">
                <?php if(!$model->hasErrors() && Yii::$app->request->isPost): ?>
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">?</button>
                        <h4><i class="icon fa fa-check"></i><?= Yii::t('admin','Saved'); ?></h4>
                        <?= Yii::t('admin','All changes accepted'); ?>
                    </div>
                <?php endif; ?>

                <?= $form->field($model, 'meta_description')->textarea(); ?>
                <?= $form->field($model, 'meta_keywords')->textarea(); ?>

                <hr>

                <?= $form->field($model, 'footer_content')->textarea(['class' => 'form-control editor-area']); ?>
                <?= $form->field($model, 'image')->fileInput()->label(Yii::t('admin','Header logo image')); ?>

                <?php if($model->hasFile()): ?>
                    <img src="<?= $model->getImageUrl(); ?>" width="400" class="img-thumbnail"><br>
                    <?= Html::a(Yii::t('admin','Delete'),'/admin/common-settings/del-logo'); ?>
                <?php endif; ?>
            </div>

            <div class="box-footer">
                <a class="btn btn-primary" href="<?php echo Url::to(['/admin/banner/index']); ?>"><?= Yii::t('admin','Back'); ?></a>
                <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>