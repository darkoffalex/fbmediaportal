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
/* @var $type string */

/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->all();

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Stock');
$this->params['breadcrumbs'][] = $this->title;
$currentView = $this;

$batchMove = [];

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
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post */
            return !empty($model->author) ? Html::a($model->author->name.' '.$model->author->surname,['/admin/users/preview', 'id' => $model->author_id],['data-toggle'=>'modal','data-target'=>'.modal']) : $model->author_custom_name;
        },
    ],


    [
        'attribute' => 'published_at',
        'enableSorting' => true,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\Post*/
            return !empty($model->published_at) ? $model->published_at : Yii::t('admin','No data');
        },
    ],

    [
        'attribute' => 'created_at',
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
        'attribute' => 'need_update',
        'label' => Yii::t('admin','Updates'),
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($lng){
            /* @var $model \app\models\Post */
            return !empty($model->need_update) ? '<span class="label label-danger">'.Yii::t('admin','Waiting').'</span>' : '<span class="label label-success">'.Yii::t('admin','Updated').'</span>';
        },
    ],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 100px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {move} &nbsp {comments} &nbsp {fb_link} &nbsp; {archive}',
        'buttons' => [
            'move' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-filter"></span>', ['/admin/stock/move', 'id' => $model->id], ['title' => Yii::t('admin','Move to posts'), 'data-toggle'=>'modal', 'data-target'=>'.modal']);
            },

            'comments' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-comment"></span> <span style="font-size: 12px; position: relative; top: -3px;">('.count($model->comments).')</span>', ['/admin/posts/comments', 'id' => $model->id], ['title' => Yii::t('admin','View comments'), 'data-toggle'=>'modal', 'data-target'=>'.modal']);
            },

            'fb_link' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<i class="fa fa-facebook-f"></i>', $model->getFbUrl(), ['title' => Yii::t('admin','View on facebook'), 'target' => '_blank']);
            },

            'archive' => function ($url,$model,$key) use($type,&$batchMove) {
                /* @var $model \app\models\Post */
                $icon = $type == 'main' ? 'fa-sign-in' : 'fa-sign-out';
                $title = $type == 'main' ? 'To archive' : 'From archive';
                $batchMove[] = $model->id;
                return Html::a('<i class="fa '.$icon.'"></i>', ['/admin/stock/status', 'id' => $model->id, 'status' => ($type == 'main'  ? Constants::STATUS_DELETED : Constants::STATUS_IN_STOCK)], ['title' => Yii::t('admin',$title)]);
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
            <div class="box-header" style="padding-bottom: 0;">
                <ul class="nav nav-tabs">
                    <?php $type = empty($type) ? 'main' : $type; ?>
                    <li class="<?= $type=='main' ? 'active' : ''; ?>">
                        <a href="<?= Url::to(['/admin/stock/index','type' => 'main']); ?>"><?= Yii::t('admin','Main'); ?></a>
                    </li>
                    <li class="<?= $type=='archive' ? 'active' : ''; ?>">
                        <a href="<?= Url::to(['/admin/stock/index','type' => 'archive']); ?>"><?= Yii::t('admin','Archive'); ?></a>
                    </li>
                </ul>
            </div>
            <div class="box-body" style="padding-top: 0;">
                <div class="tab-content inner-block">
                    <?= $this->render('_filters',['model' => $searchModel]); ?>

                    <?= GridView::widget([
//                    'filterModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'columns' => $gridColumns,
                        'pjax' => false,
                    ]); ?>
                </div>
            </div>
            <?php if($type == 'main'): ?>
                <div class="box-footer">
                    <a href="<?= Url::to(['/admin/stock/batch-archive', 'ids' => implode(',',$batchMove)]); ?>" class="btn btn-primary"><?= Yii::t('admin','Move all items on page to archive'); ?></a>
                    <a data-target=".modal" data-toggle="modal" href="<?php echo Url::to(['/admin/stock/recommend-settings']); ?>" class="btn btn-primary"><?= Yii::t('admin','Recommend settings'); ?></a>
                    <a data-target=".modal" data-toggle="modal" href="<?php echo Url::to(['/admin/stock/groups']); ?>" class="btn btn-primary"><?= Yii::t('admin','Source-gropus settings'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
