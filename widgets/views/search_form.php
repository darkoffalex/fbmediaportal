<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $attributes array */
/* @var $widget \app\widgets\SearchFormWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<form style="margin-bottom: 10px;" method="get" action="<?= Url::to(['search/index']); ?>" class="row clearfix">
    <div class="col-lg-8 col-md-8 col-sm-12">
        <input value="<?= Yii::$app->request->get('q'); ?>" type="text" name="q" class="form-control">
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <input class="btn btn-primary btn-block" type="submit" value="Поиск">
    </div>
</form>
