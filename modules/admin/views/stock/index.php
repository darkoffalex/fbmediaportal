<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;
use app\models\Category;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\models\PostGroup;

/* @var $searchModel \app\models\PostSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */
/* @var $user \app\models\User */
/* @var $lng string */

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Stock');
$this->params['breadcrumbs'][] = $this->title;
$currentView = $this;

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

//    [
//        'attribute' => 'name',
//        'label' => Yii::t('admin','Internal name')
//    ],

    [
        'attribute' => 'content',
        'format' => 'raw',
        'contentOptions'=>['style'=>'font-size:12px; width: 300px;'],
        'value' => function ($model, $key, $index, $column) use ($lng, $currentView){
            /* @var $model \app\models\Post */
            return $currentView->render('/posts/_cell_preview',compact('model','lng'));
        },
    ],

    [
        'attribute' => 'group_id',
        'label' => Yii::t('admin','Group'),
        'enableSorting' => false,
        'filter' => ArrayHelper::map(PostGroup::find()->where(['is_group' => 1])->all(),'id','name'),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($lng){
            /* @var $model \app\models\Post */
            return $model->group->name;
        },
    ],

    [
        'attribute' => 'author_id',
        'filter' => Select2::widget([
            'model' => $searchModel,
            'attribute' => 'author_id',
            'initValueText' => !empty($searchModel->author) ? $searchModel->author->name.' '.$searchModel->author->surname : '',
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
        ]),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post */
            return !empty($model->author) ? Html::a($model->author->name.' '.$model->author->surname,['/admin/users/preview', 'id' => $model->author_id],['data-toggle'=>'modal','data-target'=>'.modal']) : $model->author_custom_name;
        },
    ],

    [
        'attribute' => 'created_at',
        'filter' => \kartik\daterange\DateRangePicker::widget([
            'model' => $searchModel,
            'convertFormat' => true,
            'attribute' => 'created_at',
            'pluginOptions' => [
                'locale' => [
                    'format'=>'Y-m-d',
                    'separator'=>' - ',
                ],
            ],
        ]),
        'enableSorting' => true,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post*/
            return !empty($model->created_at) ? $model->created_at : Yii::t('admin','No data');
        },
    ],

    [
        'label' => Yii::t('admin','Recommendations'),
        'enableSorting' => false,
        'filter' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($lng){
            /* @var $model \app\models\Post */
            $recommendations = [];

            if(!empty($model->author->stockRecommendations)){
                foreach($model->author->stockRecommendations as $recommendation){
                    $recommendations[] = $recommendation->category->name;
                }
            }

            if(!empty($model->group->stockRecommendations)){
                foreach($model->author->stockRecommendations as $recommendation){
                    if(!in_array($recommendation->category->name,$recommendations)){
                        $recommendations[] = $recommendation->category->name;
                    }
                }
            }

            return !empty($recommendation) ? implode(', ',$recommendations) : Yii::t('admin','Нет');
        },
    ],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 100px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {move} &nbsp {comments} &nbsp {fb_link}',
        'buttons' => [
            'move' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-filter"></span>', ['/admin/stock/move', 'id' => $model->id], ['title' => Yii::t('admin','Move to posts'), 'data-toggle'=>'modal', 'data-target'=>'.modal']);
            },

            'comments' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-comment"></span>', ['/admin/posts/comments', 'id' => $model->id], ['title' => Yii::t('admin','View comments'), 'data-toggle'=>'modal', 'data-target'=>'.modal']);
            },

            'fb_link' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<i class="fa fa-facebook-f"></i>', $model->getFbUrl(), ['title' => Yii::t('admin','View on facebook'), 'target' => '_blank']);
            },
        ],
        'visibleButtons' => [
            'delete' => function ($model, $key, $index) {return true;},
            'update' => function ($model, $key, $index) {return true;},
            'fb_link' => function ($model, $key, $index) {return !empty($model->group);},
        ],
    ],
];

?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?= Yii::t('admin','List'); ?></h3>
            </div>
            <div class="box-body" style="padding-top: 0;">
                <?= GridView::widget([
                    'filterModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'pjax' => false,
                ]); ?>
            </div>
            <div class="box-footer">
                <a data-target=".modal" data-toggle="modal" href="<?php echo Url::to(['/admin/stock/recommend-settings']); ?>" class="btn btn-primary"><?= Yii::t('admin','Recommend settings'); ?></a>
            </div>
        </div>
    </div>
</div>
