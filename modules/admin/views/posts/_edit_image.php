<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\PostImage */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Image settings'); ?></h4>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'create-image-form',
//    'action' => Yii::$app->request->url,
    'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
    'enableClientValidation'=>false,
//    'enableAjaxValidation' => true,
//    'validateOnChange' => false,
//    'validateOnSubmit' => true,
//    'validateOnBlur' => false,
//    'validateOnType' => false,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}\n",
        //'labelOptions' => ['class' => 'col-lg-1 control-label'],
    ],
]); ?>

    <div class="modal-body">
        <?= $form->field($model, 'is_external')->checkbox(); ?>

        <div class="url_field" style="display: <?= $model->is_external ? 'block' : 'none'; ?>;">
            <?= $form->field($model, 'file_url')->textInput(); ?>
        </div>

        <div class="file_field" style="display: <?= $model->is_external ? 'none' : 'block'; ?>">
            <?= $form->field($model, 'image')->fileInput(); ?>
        </div>

        <?= $form->field($model, 'status_id')->dropDownList([
            Constants::STATUS_ENABLED => Yii::t('admin','Enabled'),
            Constants::STATUS_DISABLED => Yii::t('admin','Disabled'),
        ]); ?>

        <hr>

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <?php foreach($languages as $index => $lng): ?>
                    <li class="<?= $index == 0 ? 'active' : '' ?>">
                        <a href="#tab_image_<?= $index; ?>" data-toggle="tab" aria-expanded="true"><?= $lng->self_name.' ('.$lng->prefix.')'; ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content">
                <?php foreach($languages as $index => $lng): ?>
                    <div class="tab-pane <?= $index == 0 ? 'active' : '' ?>" id="tab_image_<?= $index; ?>">
                        <div class="form-group field-post_image_trl-name">
                            <label class="control-label" for="post_image_trl-name_<?= $lng->prefix; ?>"><?= Yii::t('admin','Name'); ?></label>
                            <input id="post_image_trl-name_<?= $lng->prefix; ?>" value="<?= $model->getATrl($lng->prefix)->name; ?>" class="form-control" name="PostImage[translations][<?= $lng->prefix; ?>][name]" type="text">
                        </div>

                        <div class="form-group field-post_image_trl-meta_small_text">
                            <label class="control-label" for="post_image_trl-meta_small_text_<?= $lng->prefix; ?>"><?= Yii::t('admin','Signature (under image)'); ?></label>
                            <textarea id="post_image_trl-meta_small_text_<?= $lng->prefix; ?>" class="form-control" name="PostImage[translations][<?= $lng->prefix; ?>][signature]"><?= $model->getATrl($lng->prefix)->signature; ?></textarea>
                        </div>
                    </div><!-- /.tab-pane -->
                <?php endforeach; ?>
            </div><!-- /.tab-content -->
        </div><!-- nav-tabs-custom -->
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
        <button type="button" class="btn btn-primary submit-btn"><?= Yii::t('admin','Save') ?></button>
    </div>

<?php ActiveForm::end(); ?>
