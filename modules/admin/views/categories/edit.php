<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\models\User;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $model Category */
/* @var $languages \app\models\Language[] */
/* @var $selectedTurkey array */

$this->title = Yii::t('admin',$model->isNewRecord ? 'Create category' : 'Update category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('admin','Categories'), 'url' => Url::to(['/admin/categories/index'])];
$this->params['breadcrumbs'][] = $this->title;

$languages = \app\models\Language::find()->all();
?>



<div class="row">

    <?php $form = ActiveForm::begin([
        'id' => 'edit-category-form',
        'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
        'enableClientValidation'=>false,
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}\n",
            //'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>


    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('admin','Settings'); ?></h3></div>

            <div class="box-body">
                <?php if(!$model->hasErrors() && Yii::$app->request->isPost): ?>
                    <div class="alert alert-success alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i><?= Yii::t('admin','Saved'); ?></h4>
                        <?= Yii::t('admin','All changes accepted'); ?>
                    </div>
                <?php endif; ?>

                <?= $form->field($model, 'name')->textInput()->label(Yii::t('admin','Internal name')); ?>
                <?= $form->field($model, 'status_id')->dropDownList([
                    Constants::STATUS_ENABLED => Yii::t('admin','Enabled'),
                    Constants::STATUS_DISABLED => Yii::t('admin','Disabled'),
                ]); ?>

                <?php $all = ArrayHelper::map(Category::getRecursiveItemsEx(),'id',function($model,$defaultValue){
                    /* @var $model Category */
                    $result = "";
                    for($i=1;$i<$model->getDepth();$i++){$result.= "-";}
                    $result.= $model->name;
                    return $result;
                }); ?>
                
                <?php if(!$model->isNewRecord): ?>
                    <?php unset($all[$model->id]); ?>
                <?php endif; ?>

                <?= $form->field($model, 'parent_category_id')->dropDownList([0 => Yii::t('admin','[NONE]')]+$all); ?>

                <?= $form->field($model, 'turkey_posts')->widget(Select2::className(),[
                    'showToggleAll' => false,
                    'theme' => Select2::THEME_DEFAULT,
                    'maintainOrder' => false,
                    'data' => $selectedTurkey,
                    'options' => [
                        'placeholder' => 'Выбрать',
                        'multiple' => true,
                    ],
                    'pluginOptions' => [
                        'multiple' => true,
                        'allowClear' => true,
                        'minimumInputLength' => 2,
                        'language' => [
                            'noResults' => new JsExpression("function () { return '".Yii::t('admin','No results found')."'; }"),
                            'searching' => new JsExpression("function () { return '".Yii::t('admin','Searching...')."'; }"),
                            'inputTooShort' => new JsExpression("function(args) {return '".Yii::t('admin','Type more characters')."'}"),
                            'errorLoading' => new JsExpression("function () { return '".Yii::t('admin','Waiting for results')."'; }"),
                        ],
                        'ajax' => [
                            'url' => Url::to(['/admin/categories/ajax-post-search']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(user) { return user.text; }'),
                        'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                    ],
                ])->label(Yii::t('admin','Useful about turkey'));?>
            </div>

            <div class="box-footer">
                <a class="btn btn-primary" href="<?php echo Url::to(['/admin/categories/index']); ?>"><?= Yii::t('admin','Back'); ?></a>
                <button type="submit" class="btn btn-primary"><?= Yii::t('admin','Save') ?></button>
            </div>

        </div>

    </div>


    <div class="col-md-6">
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
                    <div class="form-group field-category_trl-name">
                        <label class="control-label" for="category_trl-name_<?= $lng->prefix; ?>"><?= Yii::t('admin','Name'); ?></label>
                        <input id="category_trl-name_<?= $lng->prefix; ?>" value="<?= htmlentities($model->getATrl($lng->prefix)->name); ?>" class="form-control" name="Category[translations][<?= $lng->prefix; ?>][name]" type="text">
                    </div>
                    <hr>
                    <div class="form-group field-category_trl-meta_description">
                        <label class="control-label" for="category_trl-meta_description_<?= $lng->prefix; ?>"><?= Yii::t('admin','META description'); ?></label>
                        <textarea id="category_trl-meta_description_<?= $lng->prefix; ?>" class="form-control" name="Category[translations][<?= $lng->prefix; ?>][meta_description]"><?= htmlentities($model->getATrl($lng->prefix)->meta_description); ?></textarea>
                    </div>
                    <div class="form-group field-category_trl-meta_keywords">
                        <label class="control-label" for="category_trl-meta_keywords_<?= $lng->prefix; ?>"><?= Yii::t('admin','META keywords'); ?></label>
                        <textarea id="category_trl-meta_keywords_<?= $lng->prefix; ?>" class="form-control" name="Category[translations][<?= $lng->prefix; ?>][meta_keywords]"><?= htmlentities($model->getATrl($lng->prefix)->meta_keywords); ?></textarea>
                    </div>
                </div><!-- /.tab-pane -->
                <?php endforeach; ?>
            </div><!-- /.tab-content -->
        </div><!-- nav-tabs-custom -->
    </div>

    <?php ActiveForm::end(); ?>
</div>