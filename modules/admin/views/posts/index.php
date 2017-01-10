<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

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

$this->title = Yii::t('admin','Posts list');
$this->params['breadcrumbs'][] = $this->title;
$currentView = $this;

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

    [
        'attribute' => 'name',
        'label' => Yii::t('admin','Internal name')
    ],

    [
        'attribute' => 'author_id',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post */
            return !empty($model->author) ? Html::a($model->author->name.' '.$model->author->surname,['/admin/users/preview', 'id' => $model->author_id],['data-toggle'=>'modal','data-target'=>'.modal']) : $model->author_custom_name;
        },
    ],


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
        'label' => Yii::t('admin','Categories'),
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($lng){
            /* @var $model \app\models\Post */
            $resultString = "";
            foreach($model->categories as $category){
                $resultString.="<span class='label label-primary'>{$category->name}</span><br><br>";
            }
            return $resultString;
        },
    ],

//    [
//        'attribute' => 'trl_name',
//        'enableSorting' => false,
//        'format' => 'raw',
//        'value' => function ($model, $key, $index, $column) use ($lng){
//            /* @var $model \app\models\Post */
//            return $model->getATrl($lng)->name;
//        },
//    ],

    [
        'attribute' => 'content',
        'format' => 'raw',
        'contentOptions'=>['style'=>'font-size:12px; width: 360px;'],
        'value' => function ($model, $key, $index, $column) use ($lng, $currentView){
            /* @var $model \app\models\Post */
            return $currentView->render('_cell_preview',compact('model','lng'));
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
            <div class="box-header" style="padding-bottom: 0;">
                <ul class="nav nav-tabs">

                    <?php foreach($languages as $index => $language): ?>
                        <li class="<?= $lng == $language->prefix ? 'active' : '' ?>">
                            <a href="<?= Url::to(['/admin/posts/index','lng' => $language->prefix]); ?>"><?= $language->self_name.' ('.$language->prefix.')'; ?></a>
                        </li>
                    <?php endforeach; ?>

                    <li class="pull-right"><a href="#" class="text-muted"><i class="fa fa-globe"></i></a></li>
                </ul>

<!--                <h3 class="box-title">--><?//= Yii::t('admin','List'); ?><!--</h3>-->
            </div>
            <div class="box-body" style="padding-top: 0;">
                <div class="tab-content inner-block">
                    <?= GridView::widget([
                        'filterModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'columns' => $gridColumns,
                        'pjax' => false,
                    ]); ?>
                </div>
            </div>
            <div class="box-footer">
                <a data-target=".modal" data-toggle="modal" href="<?php echo Url::to(['/admin/posts/create']); ?>" class="btn btn-primary"><?= Yii::t('admin','Create'); ?></a>
            </div>
        </div>
    </div>
</div>
