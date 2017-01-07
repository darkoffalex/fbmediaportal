<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $searchModel \app\models\UserSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\UsersController */
/* @var $user \app\models\User */

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','User list');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],

    [
        'label' => 'FB',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\User */
            return !empty($model->fb_user_id) ? Yii::t('admin','Yes') : Yii::t('admin','No');
        },
    ],

    ['attribute' => 'username'],

    ['attribute' => 'name'],
    ['attribute' => 'surname'],

    [
        'attribute' => 'type_id',
        'filter' => [
            Constants::USR_TYPE_CREATED => Yii::t('admin','Created'),
            Constants::USR_TYPE_IMPORTED => Yii::t('admin','Imported'),
            Constants::USR_TYPE_FB_AUTHORIZED => Yii::t('admin','Authorized')
        ],
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\User */
            $types = [
                Constants::USR_TYPE_CREATED => Yii::t('admin','Created'),
                Constants::USR_TYPE_IMPORTED => Yii::t('admin','Imported'),
                Constants::USR_TYPE_FB_AUTHORIZED => Yii::t('admin','Authorized')
            ];

            return !empty($types[$model->type_id]) ? $types[$model->type_id] : Yii::t('admin','Unknown');
        },
    ],

    [
        'attribute' => 'role_id',
        'filter' => [
            Constants::ROLE_ADMIN => Yii::t('admin','Administrator'),
            Constants::ROLE_REDACTOR => Yii::t('admin','Redactor'),
            Constants::ROLE_REGULAR_USER => Yii::t('admin','User'),
        ],
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\User */
            $roles = [
                Constants::ROLE_ADMIN => Yii::t('admin','Administrator'),
                Constants::ROLE_REDACTOR => Yii::t('admin','Redactor'),
                Constants::ROLE_REGULAR_USER => Yii::t('admin','User'),
            ];

            return !empty($roles[$model->role_id]) ? $roles[$model->role_id] : Yii::t('admin','Unknown');
        },
    ],

//    [
//        'attribute' => 'last_online_at',
//        'filter' => \kartik\daterange\DateRangePicker::widget([
//            'model' => $searchModel,
//            'convertFormat' => true,
//            'attribute' => 'last_online_at',
//            'pluginOptions' => [
//                'locale' => [
//                    'format'=>'Y-m-d',
//                    'separator'=>' - ',
//                ],
//            ],
//        ]),
//        'enableSorting' => true,
//        'format' => 'raw',
//        'value' => function ($model, $key, $index, $column){
//            /* @var $model \app\models\User */
//            return !empty($model->last_online_at) ? $model->last_online_at : Yii::t('admin','No data');
//        },
//    ],

    [
        'attribute' => 'counter_posts',
        'label' => Yii::t('admin','Posts'),
        'filter' => \kartik\field\FieldRange::widget([
            'model' => $searchModel,
            'attribute1' => 'counter_posts_min',
            'attribute2' => 'counter_posts_max',
            'type' => \kartik\field\FieldRange::INPUT_TEXT,
            'separator' => Yii::t('admin','to'),
            'label' => null,
            'template' => '{widget}{error}'
        ]),
        'contentOptions'=>['style'=>'width: 150px;'],
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\User */
            return !empty($model->counter_posts) ? Html::a($model->counter_posts,['/admin/users/posts','id' => $model->id]) : 0;
        },
    ],

//    [
//        'attribute' => 'created_at',
//        'filter' => \kartik\daterange\DateRangePicker::widget([
//            'model' => $searchModel,
//            'convertFormat' => true,
//            'attribute' => 'created_at',
//            'pluginOptions' => [
//                'locale' => [
//                    'format'=>'Y-m-d',
//                    'separator'=>' - ',
//                ],
//            ],
//        ]),
//        'enableSorting' => true,
//        'format' => 'raw',
//        'value' => function ($model, $key, $index, $column){
//            /* @var $model \app\models\User */
//            return !empty($model->created_at) ? $model->created_at : Yii::t('admin','No data');
//        },
//    ],

    [
        'attribute' => 'status_id',
        'filter' => [
            Constants::STATUS_ENABLED => Yii::t('admin','Enabled'),
            Constants::STATUS_DISABLED => Yii::t('admin','Disabled'),
        ],
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column){
            /* @var $model \app\models\User */
            $statuses = [
                Constants::STATUS_ENABLED => '<span class="label label-success">'.Yii::t('admin','Enabled').'</span>',
                Constants::STATUS_DISABLED => '<span class="label label-danger">'.Yii::t('admin','Disabled').'</span>',
            ];

            return !empty($statuses[$model->status_id]) ? $statuses[$model->status_id] : Yii::t('admin','Unknown');
        },
    ],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 100px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {update} &nbsp; {change_status} &nbsp; {accounts}',
        'buttons' => [
            'change_status' => function ($url,$model,$key) {
                /* @var $model \app\models\User */
                $icon = $model->status_id == Constants::STATUS_ENABLED ? 'glyphicon glyphicon-check' : 'glyphicon glyphicon-unchecked';
                $message = $model->status_id == Constants::STATUS_ENABLED ? Yii::t('admin','Enable') : Yii::t('admin','Disable');
                return Html::a('<span class="'.$icon.'"></span>', Url::to(['/admin/users/status', 'id' => $model->id]), ['title' => $message]);
            },
        ],
        'visibleButtons' => [
            'delete' => function ($model, $key, $index) {return $model->id != Yii::$app->user->id;},
            'update' => true,
            'change_status' => function ($model, $key, $index) {return $model->id != Yii::$app->user->id;},
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
                <a href="<?php echo Url::to(['/admin/users/create']); ?>" class="btn btn-primary"><?= Yii::t('admin','Create new'); ?></a>
            </div>
        </div>
    </div>
</div>
