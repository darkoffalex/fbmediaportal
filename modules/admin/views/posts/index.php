<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $searchModel \app\models\PostSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */
/* @var $user \app\models\User */
/* @var $lng string */

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Posts list');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

    ['attribute' => 'internal_name'],

    [
        'attribute' => 'type_id',
        'filter' => [
            Constants::POST_TYPE_CREATED => Yii::t('admin','Created'),
            Constants::POST_TYPE_IMPORTED => Yii::t('admin','Imported'),
        ],
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post */
            $types = [
                Constants::POST_TYPE_CREATED => Yii::t('admin','Created'),
                Constants::POST_TYPE_IMPORTED => Yii::t('admin','Imported'),
            ];

            return !empty($types[$model->type_id]) ? $types[$model->type_id] : Yii::t('admin','Unknown');
        },
    ],

    [
        'attribute' => 'content_type_id',
        'filter' => [
            Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
            Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
            Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
            Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
            Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
        ],
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post */
            $types = [
                Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
                Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
                Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
                Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
                Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
            ];

            return !empty($types[$model->content_type_id]) ? $types[$model->content_type_id] : Yii::t('admin','Unknown');
        },
    ],

    [
        'attribute' => 'name',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($lng){
            /* @var $model \app\models\Post */
            return $model->getATrl($lng)->name;
        },
    ],

    [
        'attribute' => 'content',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($lng){
            /* @var $model \app\models\Post */
            return 'IN PROGRESS..';
        },
    ],

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
                <a data-target=".modal" data-toggle="modal" href="<?php echo Url::to(['/admin/posts/create']); ?>" class="btn btn-primary"><?= Yii::t('admin','Create'); ?></a>
            </div>
        </div>
    </div>
</div>
