<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\Label */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\LabelsController */
/* @var $languages \app\models\Language[] */

$languages = \app\models\Language::find()->all();
$controller = $this->context;
?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Configurations'); ?></h4>
    </div>

<?php $form = ActiveForm::begin([
    'id' => 'edit-language-form',
    'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
//    'enableClientValidation'=>true,
    'enableAjaxValidation'=>true,
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}\n",
        //'labelOptions' => ['class' => 'col-lg-1 control-label'],
    ],
]); ?>

    <div class="modal-body">
        <?= $form->field($model, 'source_word')->textInput(); ?>

        <?php foreach($languages as $lng): ?>
            <div class="form-group field-label_trl-word">
                <label class="control-label" for="label_trl-word_<?= $lng->prefix; ?>"><?= $lng->self_name.' ('.$lng->prefix.')'; ?></label>
                <input id="label_trl-word_<?= $lng->prefix; ?>" value="<?= $model->getATrl($lng->prefix)->word; ?>" class="form-control" name="Label[translations][<?= $lng->prefix; ?>]" type="text">
            </div>
        <?php endforeach; ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
        <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
    </div>

<?php ActiveForm::end(); ?>