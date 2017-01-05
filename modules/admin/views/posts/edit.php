<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\models\User;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

$this->title = Yii::t('admin',$model->isNewRecord ? 'Create post' : 'Update post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin','Posts'), 'url' => Url::to(['/admin/posts/index'])];
$this->params['breadcrumbs'][] = $this->title;

/* @var $model \app\models\Post */
/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
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


<?php $form = ActiveForm::begin([
    'id' => 'edit-post-form',
    'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
    'enableClientValidation'=>false,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}\n",
        //'labelOptions' => ['class' => 'col-lg-1 control-label'],
    ],
]); ?>

<?php if(!$model->hasErrors() && Yii::$app->request->isPost): ?>
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i><?= Yii::t('admin','Saved'); ?></h4>
        <?= Yii::t('admin','All changes accepted'); ?>
    </div>
<?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">

                    <?php foreach($languages as $index => $lng): ?>
                        <li class="<?= $index == 0 ? 'active' : '' ?>">
                            <a href="#tab_<?= $index; ?>" data-toggle="tab" aria-expanded="true"><?= $lng->self_name.' ('.$lng->prefix.')'; ?></a>
                        </li>
                    <?php endforeach; ?>

                    <li class="pull-right"><a href="#" class="text-muted"><i class="fa fa-globe"></i></a></li>
                </ul>
                <div class="tab-content">
                    <?php foreach($languages as $index => $lng): ?>
                        <div class="tab-pane <?= $index == 0 ? 'active' : '' ?>" id="tab_<?= $index; ?>">
                            <div class="form-group field-post_trl-name">
                                <label class="control-label" for="post_trl-name_<?= $lng->prefix; ?>"><?= Yii::t('admin','Name'); ?></label>
                                <input id="post_trl-name_<?= $lng->prefix; ?>" value="<?= $model->getATrl($lng->prefix)->name; ?>" class="form-control" name="Post[translations][<?= $lng->prefix; ?>][name]" type="text">
                            </div>

                            <div class="form-group field-post_trl-meta_small_text">
                                <label class="control-label" for="post_trl-meta_small_text_<?= $lng->prefix; ?>"><?= Yii::t('admin','Small text (excerpt)'); ?></label>
                                <textarea id="post_trl-meta_small_text_<?= $lng->prefix; ?>" class="form-control" name="Post[translations][<?= $lng->prefix; ?>][small_text]"><?= $model->getATrl($lng->prefix)->small_text; ?></textarea>
                            </div>

                            <div class="form-group field-post_trl-meta_keywords">
                                <label class="control-label" for="post_trl-full_text_<?= $lng->prefix; ?>"><?= Yii::t('admin','Full text'); ?></label>
                                <textarea id="post_trl-full_text_<?= $lng->prefix; ?>" class="form-control editor-area" name="Post[translations][<?= $lng->prefix; ?>][text]"><?= $model->getATrl($lng->prefix)->text; ?></textarea>
                            </div>
                        </div><!-- /.tab-pane -->
                    <?php endforeach; ?>
                </div><!-- /.tab-content -->
            </div><!-- nav-tabs-custom -->
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Basic configurations'); ?></h3></div>

                <div class="box-body">
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

                <div class="box-footer">
                    <a class="btn btn-primary" href="<?php echo Url::to(['/admin/posts/index']); ?>"><?= Yii::t('admin','Back'); ?></a>
                    <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
                </div>

            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>