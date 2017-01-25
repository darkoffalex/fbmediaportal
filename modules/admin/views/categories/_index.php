<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;

/* @var $searchModel \app\models\CategorySearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\CategoriesController */
/* @var $user \app\models\User */
/* @var $root int */

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Category list');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    [
        'attribute' => 'id',
        'filter' => false,
        'enableSorting' => false,
    ],

    [
        'class' => 'kartik\grid\ExpandRowColumn',
        'value' => function ($model, $key, $index, $column) {return GridView::ROW_COLLAPSED;},
        'expandIcon' => '<span class="glyphicon glyphicon-plus"></span>',
        'collapseIcon' => '<span class="glyphicon glyphicon-minus"></span>',
        'enableCache' => false,
        'allowBatchToggle' => false,
        'detailUrl' => Url::to(['/admin/categories/index']),
    ],

    [
        'attribute' => 'name',
        'filter' => false,
        'enableSorting' => false,
    ],

    [
        'attribute' => 'items',
        'label' => Yii::t('admin','Items'),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Category */
            return count($model->posts);
        },
    ],

    [
        'attribute' => 'created_at',
        'filter' => false,
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post*/
            return !empty($model->created_at) ? $model->created_at : Yii::t('admin','No data');
        },
    ],

    [
        'attribute' => 'status_id',
        'format' => 'raw',
        'enableSorting' => false,
        'contentOptions'=>['style'=>'font-size:12px; width: 300px;'],
        'value' => function ($model, $key, $index, $column) use ($lng, $currentView){
            /* @var $model \app\models\Category */
            return $model->status_id == Constants::STATUS_ENABLED ? "<span class='label label-success'>".Yii::t('admin','Enabled')."</span>" : "<span class='label label-danger'>".Yii::t('admin','Disabled')."</span>";
        },
    ],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 120px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {edit} &nbsp; {move-up} &nbsp {move-down}',
        'buttons' => [
            'delete' => function ($url,$model,$key) use ($root) {
                /* @var $model \app\models\Category */
                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/admin/categories/delete', 'id' => $model->id], ['title' => Yii::t('admin','Delete'), 'data-ajax-reloader' => '#gw_'.$root, 'data-load-parent'=>'yes', 'data-confirm-ajax' => Yii::t('yii','Are you sure you want to delete this item?')]);
            },
            'edit' => function ($url,$model,$key) use ($root) {
                /* @var $model \app\models\Category */
                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/admin/categories/edit', 'id' => $model->id], ['title' => Yii::t('admin','Edit')]);
            },
            'move-up' => function ($url,$model,$key) use ($root) {
                /* @var $model \app\models\Category */
                return Html::a('<span class="glyphicon glyphicon-arrow-up"></span>', ['/admin/categories/move', 'id' => $model->id, 'dir' => 'up'], ['title' => Yii::t('admin','Move up'), 'data-load-parent'=>'yes', 'data-ajax-reloader' => '#gw_'.$root]);
            },
            'move-down' => function ($url,$model,$key) use ($root) {
                /* @var $model \app\models\Category */
                return Html::a('<span class="glyphicon glyphicon-arrow-down"></span>', ['/admin/categories/move', 'id' => $model->id, 'dir' => 'down'], ['title' => Yii::t('admin','Move down'), 'data-load-parent'=>'yes', 'data-ajax-reloader' => '#gw_'.$root]);
            },
        ],
        'visibleButtons' => [
            'delete' => function ($model, $key, $index) {return true;},
            'edit' => function ($model, $key, $index) {return true;},
        ],
    ],
];

?>

<?= GridView::widget([
    'id' => 'gw_'.$root,
    'filterModel' => null,
    'dataProvider' => $dataProvider,
    'columns' => $gridColumns,
    'summary' => ' ',
    'hover' => true,
    'responsiveWrap' => false,
    'headerRowOptions' => ['class' => 'hidden-in-sub'],
    'containerOptions' => ['class' => 'no-margin-tbl'],
    'pjax' => false,
]); ?>
