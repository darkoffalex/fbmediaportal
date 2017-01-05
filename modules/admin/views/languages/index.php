<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $searchModel \app\models\LanguagesSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\LanguagesController */
/* @var $user \app\models\User */

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = Yii::t('admin','Languages list');
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],
    ['attribute' => 'name'],
    ['attribute' => 'self_name'],
    ['attribute' => 'prefix'],

    [
        'class' => 'yii\grid\ActionColumn',
        'contentOptions'=>['style'=>'width: 100px; text-align: center;'],
        'header' => Yii::t('admin','Actions'),
        'template' => '{delete} &nbsp; {update}',
        'buttons' => [
            'update' => function ($url,$model,$key) {
                /* @var $model \app\models\User */
                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::to(['/admin/languages/update', 'id' => $model->id]), ['data-toggle'=>'modal', 'data-target'=>'.modal', 'title' => Yii::t('admin','Edit')]);
            },
        ],
        'visibleButtons' => [
            'delete' => function ($model, $key, $index) {return \app\models\Language::find()->count() > 1;},
            'update' => true,
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
                <a data-target=".modal" data-toggle="modal" href="<?php echo Url::to(['/admin/languages/create']); ?>" class="btn btn-primary"><?= Yii::t('admin','Create'); ?></a>
            </div>
        </div>
    </div>
</div>
