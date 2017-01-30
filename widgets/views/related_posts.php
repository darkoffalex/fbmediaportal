<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $widget \app\widgets\RelatedPostsWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<ul class="spost_nav wow fadeInDown animated animated" style="visibility: visible; animation-name: fadeInDown;">

    <?php foreach($posts as $post): ?>
        <li>
            <div class="media">
                <a class="media-left" href="<?= $post->getUrl(); ?>">
                    <img src="<?= $post->getThumbnailUrl(); ?>" alt="">
                </a>
                <div class="media-body">
                    <a class="catg_title" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>