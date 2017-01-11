<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $searchModel \app\models\BannerSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\BannerController */
/* @var $user \app\models\User */

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Banner list');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

    ['attribute' => 'name'],

    [
        'attribute' => 'type_id',
        'filter' => [
            Constants::BANNER_TYPE_IMAGE => Yii::t('admin','Image'),
            Constants::BANNER_TYPE_CODE => Yii::t('admin','Code'),
        ],
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Banner */
            $types = [
                Constants::BANNER_TYPE_IMAGE => Yii::t('admin','Image'),
                Constants::BANNER_TYPE_CODE => Yii::t('admin','Code'),
            ];

            return !empty($types[$model->type_id]) ? $types[$model->type_id] : Yii::t('admin','Unknown');
        },
    ],

    ['attribute' => 'clicks'],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 100px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {update}',
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
                <a href="<?php echo Url::to(['/admin/banner/create']); ?>" class="btn btn-primary"><?= Yii::t('admin','Create'); ?></a>
            </div>
        </div>
    </div>
</div>
