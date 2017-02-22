<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\Category;
use yii\web\JsExpression;
use app\helpers\Constants;
use app\models\PostGroup;
use kartik\date\DatePicker;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $model app\models\PostSearch */
/* @var $form \yii\bootstrap\ActiveForm */
?>

<div class="filters" style="border-bottom: 1px solid #F9F9F9; margin-bottom: 15px;">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'enableClientValidation'=>false,
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model,'name')->textInput()->error(false); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model,'id')->textInput()->error(false); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model,'fb_sync_id')->textInput()->error(false); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model,'author_id')->widget(Select2::classname(), [
                'initValueText' => !empty($model->author) ? $model->author->name.' '.$model->author->surname : '',
                'options' => ['placeholder' => Yii::t('admin','Search for a user...')],
                'language' => Yii::$app->language,
                'theme' => Select2::THEME_DEFAULT,
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 2,
                    'language' => [
                        'noResults' => new JsExpression("function () { return '".Yii::t('admin','No results found')."'; }"),
                        'searching' => new JsExpression("function () { return '".Yii::t('admin','Searching...')."'; }"),
                        'inputTooShort' => new JsExpression("function(args) {return '".Yii::t('admin','Type more characters')."'}"),
                        'errorLoading' => new JsExpression("function () { return '".Yii::t('admin','Waiting for results')."'; }"),
                    ],
                    'ajax' => [
                        'url' => Url::to(['/admin/users/ajax-search']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(user) { return user.text; }'),
                    'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                ],
            ])->error(false) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model,'is_parsed')->dropDownList([
                'YES' => Yii::t('admin','Parsed'),
                'NO' => Yii::t('admin','Imported')
            ],['prompt' => ''])->label(Yii::t('admin','Is Parsed'))->error(false); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'created_at')->widget(DateRangePicker::className(),[
                'convertFormat' => true,
                'pluginOptions' => [
                    'locale' => [
                        'format'=>'Y-m-d',
                        'separator'=>' - ',
                    ],
                ]
            ])->error(false); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'published_at')->widget(DateRangePicker::className(),[
                'convertFormat' => true,
                'pluginOptions' => [
                    'locale' => [
                        'format'=>'Y-m-d',
                        'separator'=>' - ',
                    ],
                ]
            ])->error(false); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model,'group_id')->dropDownList(ArrayHelper::map(PostGroup::find()->all(),'id','name'),['prompt' => ''])->error(false); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'need_update')->dropDownList([
                'YES' => Yii::t('admin','Waiting'),
                'NO' => Yii::t('admin','Updated')
            ],['prompt' => ''])->label(Yii::t('admin','Updates'))->error(false); ?>
        </div>
        <div class="col-md-2" style="margin-top: 25px;">
            <?= Html::submitButton(Yii::t('admin','Filter'), ['class' => 'btn btn-primary box-btn']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
