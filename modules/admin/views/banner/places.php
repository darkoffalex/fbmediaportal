<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $searchModel \app\models\BannerPlaceSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\BannerController */
/* @var $user \app\models\User */

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Banner place list');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

    ['attribute' => 'name'],
    ['attribute' => 'alias'],

    [
        'label' => Yii::t('admin','Banners'),
        'filter' => false,
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\BannerPlace */
            return !empty($model->banners) ? implode(', ',ArrayHelper::map($model->banners,'id','name')) : null;
        },
    ],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 100px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {update} &nbsp; {calendar}',
        'buttons' => [
            'delete' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/admin/banner/delete-place', 'id' => $model->id], ['title' => Yii::t('admin','Delete'), 'data-confirm' => Yii::t('yii','Are you sure you want to delete this item?')]);
            },
            'update' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/admin/banner/update-place', 'id' => $model->id], ['title' => Yii::t('admin','View comments'), 'data-toggle'=>'modal', 'data-target'=>'.modal']);
            },
            'calendar' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-calendar"></span>', ['/admin/banner/place-scheduler', 'id' => $model->id], ['title' => Yii::t('admin','Manage banner display schedule')]);
            },
        ],
        'visibleButtons' => [
            'delete' => function ($model, $key, $index) {return true;},
            'update' => function ($model, $key, $index) {return true;},
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
            <div class="box-body">
                <?= GridView::widget([
                    'filterModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'pjax' => false,
                ]); ?>
            </div>
            <div class="box-footer">
                <a data-target=".modal" data-toggle="modal" href="<?php echo Url::to(['/admin/banner/create-place']); ?>" class="btn btn-primary"><?= Yii::t('admin','Create'); ?></a>
            </div>
        </div>
    </div>
</div>
