<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\PostVoteAnswer */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Answer settings'); ?></h4>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'create-answer-form',
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
        <?= $form->field($model, 'voted_qnt')->textInput(); ?>

        <hr>

        <ul class="nav nav-tabs">
            <?php foreach($languages as $index => $lng): ?>
                <li class="<?= $index == 0 ? 'active' : '' ?>">
                    <a href="#tab_image_<?= $index; ?>" data-toggle="tab" aria-expanded="true"><?= $lng->self_name.' ('.$lng->prefix.')'; ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content inner-block">
            <?php foreach($languages as $index => $lng): ?>
                <div class="tab-pane <?= $index == 0 ? 'active' : '' ?>" id="tab_image_<?= $index; ?>">
                    <div class="form-group field-post_vote_answer_trl-name">
                        <label class="control-label" for="post_vote_answer_trl-name_<?= $lng->prefix; ?>"><?= Yii::t('admin','Answer text'); ?></label>
                        <input id="post_vote_answer_trl-name_<?= $lng->prefix; ?>" value="<?= $model->getATrl($lng->prefix)->text; ?>" class="form-control" name="PostVoteAnswer[translations][<?= $lng->prefix; ?>][text]" type="text">
                    </div>
                </div><!-- /.tab-pane -->
            <?php endforeach; ?>
        </div><!-- /.tab-content -->
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
        <button type="button" class="btn btn-primary submit-ajax-btn" data-ajax-form="#create-answer-form" data-ok-reload=".ajax-reloadable-answers"><?= Yii::t('admin','Save') ?></button>
    </div>

<?php ActiveForm::end(); ?>
