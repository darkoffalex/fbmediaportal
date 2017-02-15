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
        <div class="col-md-2">
            <?= $form->field($model,'content')->textInput()->error(false); ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model,'id')->textInput()->error(false); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model,'category_id')->dropDownList(ArrayHelper::map(Category::getRecursiveItemsEx(),'id',function($model,$defaultValue){
                /* @var $model Category */
                $result = "";
                for($i=1;$i<$model->getDepth();$i++){$result.= "-";}
                $result.= $model->name;
                return $result;
            }),['prompt' => ''])->label(Yii::t('admin','Category'))->error(false); ?>
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
            <?= $form->field($model,'content_type_id')->dropDownList([
                Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
                Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
                Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
                Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
                Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
                Constants::CONTENT_TYPE_POST => Yii::t('admin','Post')
            ],['prompt' => '']); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model,'type_id')->dropDownList([
                Constants::POST_TYPE_CREATED => Yii::t('admin','Created'),
                Constants::POST_TYPE_IMPORTED => Yii::t('admin','Imported'),
            ],['prompt' => ''])->label(Yii::t('admin','Imported')); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model,'group_id')->dropDownList(ArrayHelper::map(PostGroup::find()->all(),'id','name'),['prompt' => ''])->error(false); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'kind_id')->dropDownList([
                Constants::KIND_INTERESTING_CONTENT => Yii::t('admin','Useful content'),
                Constants::KIND_INTERESTING_COMMENTS => Yii::t('admin','Interesting discussion'),
                Constants::KIND_FORUM => Yii::t('admin','Forum'),
            ],['prompt' => ''])->label(Yii::t('admin','Type'))->error(false); ?>
        </div>
        <div class="col-md-2">
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
            <?= $form->field($model, 'need_finish')->dropDownList([
                "YES" => Yii::t('admin','Yes'),
                "NO" => Yii::t('admin','No')
            ],['prompt' => '']); ?>
        </div>
        <div class="col-md-2" style="margin-top: 25px;">
            <?= Html::submitButton(Yii::t('admin','Filter'), ['class' => 'btn btn-primary box-btn']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
