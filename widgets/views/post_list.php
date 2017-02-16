<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $widget \app\widgets\LatestPostsWidget|\app\widgets\PopularPostWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */
/* @var $type string */
/* @var $label string */
/* @var $ico string */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<div class="col-md-12 col-lg-4">
    <div class="categories__name"><i class="ico <?= !empty($ico) ? $ico : 'ico-cat-news'; ?>"></i><span><?= $label; ?></span></div>
    <ul class="categories__list">
        <?php foreach ($posts as $post): ?>
            <li><a href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <a class="categories__more" href="<?= Url::to(['site/all','type' => $type]); ?>">Все материалы</a>
</div>
