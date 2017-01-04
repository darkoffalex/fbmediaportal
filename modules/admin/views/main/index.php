<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\MainController */
/* @var $user \app\models\User */

$controller = $this->context;
$user = Yii::$app->user->identity;

$this->title = "Название страницы";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Заголовок</h3>
            </div>
            <div class="box-body">
                <p>Здесь какой-то текст</p>
            </div>
            <div class="box-footer">
                <a href="#" class="btn btn-primary">Действие</a>
            </div>
        </div>
    </div>
</div>