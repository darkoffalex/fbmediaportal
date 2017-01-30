<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;
use kartik\dropdown\DropdownX;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $model \app\models\Post */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\StockController */

$controller = $this->context;
Yii::$app->view->registerJsFile('/js/nested-multi-sel.js');
?>

    <div class="modal-header">
        <h4 class="modal-title"><?= Yii::t('admin','Move post to main list'); ?></h4>
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

        <div class="form-group dropdown inactive-links">
            <label class="control-label"><?= Yii::t('admin','Categories'); ?></label>

            <div class="form-control categories-tags" data-toggle="dropdown">
                <?php foreach($model->categories as $cat): ?>
                    <span class="label label-primary margin-r-5">
                        <?= $cat->name; ?>
                        <span class="fa fa-close icon-pointer" data-remove data-category-id="<?= $cat->id; ?>"></span>
                        <input type="hidden" name="Post[categoriesChecked][]" value="<?= $cat->id; ?>">
                    </span>
                <?php endforeach; ?>
            </div>

            <?php echo DropdownX::widget([
                'items' => Category::buildRecursiveArrayForDropDown(),
            ]);  ?>
        </div>

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
        ]) ?>

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
        <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Move to list') ?></button>
    </div>

<?php ActiveForm::end(); ?>