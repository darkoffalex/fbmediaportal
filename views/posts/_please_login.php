<?php
use yii\helpers\Url;

/* @var $model \app\models\Comment */
/* @var $comment \app\models\Comment */
/* @var $post \app\models\Post */
/* @var $this \yii\web\View */
/* @var $controller \app\controllers\PostsController */
/* @var $social kartik\social\Module */

$controller = $this->context;

$social = Yii::$app->getModule('social');
?>

<div class="modal-header">
    <h4 class="modal-title">Пожалуйста войдите</h4>
</div>

<div class="modal-body text-center">
    <?php $callback = Url::to(['/site/fb-login'],true); ?>
    <?= $social->getFbLoginLink($callback,['class' => 'btn btn-primary'],['email']); ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Закрыть</button>
</div>

