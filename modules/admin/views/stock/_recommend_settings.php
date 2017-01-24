<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;
use yii\web\JsExpression;
use kartik\select2\Select2;

/* @var $all \app\models\StockRecommendation[] */
/* @var $model \app\models\StockRecommendation */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();
?>

<div class="modal-header">
    <h4 class="modal-title"><?= Yii::t('admin','Stock recommendations'); ?></h4>
</div>


<div class="modal-body" style="max-height: 500px; overflow-y: scroll;">

    <?php $form = ActiveForm::begin([
        'id' => 'recommendations-form',
        'options' => ['role' => 'form', 'method' => 'post', 'enctype' => 'multipart/form-data'],
        'enableClientValidation'=>false,
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}\n",
        ],
    ]); ?>

    <?php $model->reason_type_id = empty($model->reason_type_id) ? Constants::OFFER_REASON_AUTHOR : $model->reason_type_id; ?>
    <?= $form->field($model, 'reason_type_id')->dropDownList([
        Constants::OFFER_REASON_AUTHOR => Yii::t('admin','Author matching'),
        Constants::OFFER_REASON_GROUP => Yii::t('admin','Group matching'),
        Constants::OFFER_REASON_CAT_TAG => Yii::t('admin','Category tag matching')
    ]);?>

    <div data-show-on="<?= Constants::OFFER_REASON_AUTHOR; ?>" class="reason-object <?= $model->reason_type_id != Constants::OFFER_REASON_AUTHOR ? 'hidden' : ''; ?>">
        <?= $form->field($model, 'author_id')->widget(Select2::classname(), [
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
        ]); ?>
    </div>

    <div data-show-on="<?= Constants::OFFER_REASON_GROUP; ?>" class="reason-object <?= $model->reason_type_id != Constants::OFFER_REASON_GROUP ? 'hidden' : ''; ?>">
        <?php $data = ArrayHelper::merge(['' => Yii::t('admin','[NONE]')],ArrayHelper::map(\app\models\PostGroup::find()->where(['is_group' => 1])->all(),'id','name')); ?>
        <?= $form->field($model,'group_id')->dropDownList($data)->label(Yii::t('admin','Group')); ?>
    </div>

    <div data-show-on="<?= Constants::OFFER_REASON_CAT_TAG; ?>" class="reason-object <?= $model->reason_type_id != Constants::OFFER_REASON_CAT_TAG ? 'hidden' : ''; ?>">
        <?= $form->field($model,'category_tag')->textInput(); ?>
    </div>

    <?= $form->field($model,'category_id')->dropDownList(ArrayHelper::map(Category::getRecursiveItems(),'id',function($model,$defaultValue){
        /* @var $model Category */
        $result = "";
        for($i=1;$i<$model->getDepth();$i++){$result.= "-";}
        $result.= $model->name;
        return $result;
    }))->label(Yii::t('admin','Offer this category')); ?>

    <button type="button" class="btn btn-primary submit-ajax-btn" data-ajax-form="#recommendations-form" data-ok-reload=""><?= Yii::t('admin','Add rule') ?></button>
    <?php ActiveForm::end(); ?>

    <hr>

    <?php if(!empty($all)): ?>
        <table class="table table-hover table-bordered">
            <tr>
                <th><?= Yii::t('admin','Reason'); ?></th>
                <th><?= Yii::t('admin','Category'); ?></th>
                <th><?= Yii::t('admin','Actions'); ?></th>
            </tr>
            <?php /* @var $recommendItem \app\models\StockRecommendation */ ?>
            <?php foreach($all as $recommendItem): ?>
                <tr>
                    <td>
                        <?php $reasons = [
                            Constants::OFFER_REASON_AUTHOR => Yii::t('admin','Author matching'),
                            Constants::OFFER_REASON_GROUP => Yii::t('admin','Group matching'),
                            Constants::OFFER_REASON_CAT_TAG => Yii::t('admin','Category tag matching')
                        ]; ?>
                        <?php $values = [
                            Constants::OFFER_REASON_AUTHOR => !empty($recommendItem->author) ? $recommendItem->author->name.' '.$recommendItem->author->surname : '',
                            Constants::OFFER_REASON_GROUP => !empty($recommendItem->group) ? $recommendItem->group->name : '',
                            Constants::OFFER_REASON_CAT_TAG => $recommendItem->category_tag
                        ]; ?>

                        <?= Yii::t('admin','Reason Type ID').' '.$reasons[$recommendItem->reason_type_id]. '('.$values[$recommendItem->reason_type_id].')' ?>
                    </td>
                    <td><?= !empty($recommendItem->category) ? $recommendItem->category->name : Yii::t('admin','Not set'); ?></td>
                    <td>
                        <a href="<?= Url::to(['/admin/stock/delete-recommend', 'id' => $recommendItem->id]); ?>" data-ajax-reloader=".modal-content" title="<?= Yii::t('admin','Delete'); ?>" aria-label="<?= Yii::t('admin','Delete'); ?>" data-confirm-ajax="<?= Yii::t('yii','Are you sure you want to delete this item?') ?>"><span class="glyphicon glyphicon-trash"></span></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('select[name="StockRecommendation[reason_type_id]"]').change(function(){
            var reason_type = $(this).val();
            $(".reason-object").addClass('hidden');
            $('[data-show-on="'+reason_type+'"]').removeClass('hidden');
        });
    });

</script>