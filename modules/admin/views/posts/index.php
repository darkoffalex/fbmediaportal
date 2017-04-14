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

$this->title = Yii::t('admin','Posts list');
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
            return $currentView->render('_cell_preview',compact('model','lng'));
        },
    ],

    [
        'attribute' => 'category_id',
        'label' => Yii::t('admin','Categories'),
        'enableSorting' => false,
        'filter' => ArrayHelper::map(Category::getRecursiveItemsEx(),'id',function($model,$defaultValue){
            /* @var $model Category */
            $result = "";
            for($i=1;$i<$model->getDepth();$i++){$result.= "-";}
            $result.= $model->name;
            return $result;
        }),
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

    [
        'attribute' => 'author_id',
        'enableSorting' => false,
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


//    [
//        'attribute' => 'type_id',
//        'filter' => [
//            Constants::POST_TYPE_CREATED => Yii::t('admin','Created'),
//            Constants::POST_TYPE_IMPORTED => Yii::t('admin','Imported'),
//        ],
//        'enableSorting' => false,
//        'format' => 'raw',
//        'value' => function ($model, $key, $index, $column){
//            /* @var $model \app\models\Post */
//            $types = [
//                Constants::POST_TYPE_CREATED => Yii::t('admin','Created'),
//                Constants::POST_TYPE_IMPORTED => Yii::t('admin','Imported'),
//            ];
//
//            return !empty($types[$model->type_id]) ? $types[$model->type_id] : Yii::t('admin','Unknown');
//        },
//    ],

    [
        'attribute' => 'content_type_id',
        'contentOptions'=>['style'=>'width: 100px;'],
        'filter' => [
            Constants::CONTENT_TYPE_ARTICLE => Yii::t('admin','Article'),
            Constants::CONTENT_TYPE_NEWS => Yii::t('admin','News'),
            Constants::CONTENT_TYPE_PHOTO => Yii::t('admin','Photo'),
            Constants::CONTENT_TYPE_VIDEO => Yii::t('admin','Video'),
            Constants::CONTENT_TYPE_VOTING => Yii::t('admin','Voting'),
            Constants::CONTENT_TYPE_POST => Yii::t('admin','Post')
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
                Constants::CONTENT_TYPE_POST => Yii::t('admin','Post')
            ];

            return !empty($types[$model->content_type_id]) ? $types[$model->content_type_id] : Yii::t('admin','Unknown');
        },
    ],

    [
        'attribute' => 'group_id',
        'label' => Yii::t('admin','Source'),
        'enableSorting' => false,
        'filter' => ArrayHelper::map(PostGroup::find()->all(),'id','name'),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($lng){
            /* @var $model \app\models\Post */
            return $model->group->name;
        },
    ],

    [
        'attribute' => 'published_at',
        'filter' => \kartik\daterange\DateRangePicker::widget([
            'model' => $searchModel,
            'convertFormat' => true,
            'attribute' => 'published_at',
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
            return !empty($model->published_at) ? $model->published_at : Yii::t('admin','No data');
        },
    ],

    [
        'attribute' => 'delayed_at',
        'filter' => \kartik\daterange\DateRangePicker::widget([
            'model' => $searchModel,
            'convertFormat' => true,
            'attribute' => 'delayed_at',
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
            return !empty($model->published_at) ? $model->published_at : Yii::t('admin','No data');
        },
    ],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 100px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {comments} &nbsp; {update} &nbsp; {link} &nbsp; {fb_link}',
        'buttons' => [
            'comments' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon-comment"></span> <span style="font-size: 12px; position: relative; top: -3px;">('.count($model->comments).')</span>', ['/admin/posts/comments', 'id' => $model->id], ['title' => Yii::t('admin','View comments'), 'data-toggle'=>'modal', 'data-target'=>'.modal']);
            },

            'link' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<span class="glyphicon glyphicon glyphicon-link"></span>', $model->getUrl(false,true), ['title' => Yii::t('admin','View on portal'), 'target' => '_blank']);
            },

            'fb_link' => function ($url,$model,$key) {
                /* @var $model \app\models\Post */
                return Html::a('<i class="fa fa-facebook-f"></i>', $model->getFbUrl(), ['title' => Yii::t('admin','View on facebook'), 'target' => '_blank']);
            },
        ],
        'visibleButtons' => [
            'delete' => function ($model, $key, $index) {return true;},
            'update' => function ($model, $key, $index) {return true;},
            'link' => function ($model, $key, $index) {return $model->status_id == Constants::STATUS_ENABLED;},
            'fb_link' => function ($model, $key, $index) {return !empty($model->fb_sync_id) && !empty($model->group);},
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

                    <li class="pull-right"><a href="<?= Url::to(['/admin/posts/index', 'lng' => $lng]) ?>" class="text-muted"><i class="fa fa-refresh"></i></a></li>
                </ul>

<!--                <h3 class="box-title">--><?//= Yii::t('admin','List'); ?><!--</h3>-->
            </div>
            <div class="box-body" style="padding-top: 0;">
                <div class="tab-content inner-block">

                    <?= $this->render('_filters',['model' => $searchModel]); ?>

                    <?= GridView::widget([
//                        'filterModel' => $searchModel,
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
