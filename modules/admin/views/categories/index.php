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

?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?= Yii::t('admin','List'); ?></h3>
            </div>
            <div class="box-body">
                <?= $this->render('_index',compact('searchModel','dataProvider','root')); ?>
            </div>
            <div class="box-footer">
                <a data-toggle="modal" data-target=".modal" href="<?php echo Url::to(['/admin/categories/create']); ?>" class="btn btn-primary btn-sm"><?= Yii::t('admin','Create'); ?></a>
            </div>
        </div>
    </div>
</div>
