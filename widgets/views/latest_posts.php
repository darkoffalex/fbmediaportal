<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $widget \app\widgets\MostCommentedWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<div class="single_sidebar">
    <h2><a href="#"><span>Последние новости</span></a></h2>
    <ul class="spost_nav">

        <?php foreach($posts as $post): ?>
            <li>
                <div class="media">
                    <?php $slugTitle = ArrayHelper::getValue($post->trl,'name',$post->name); ?>
                    <?php $url = Url::to(['posts/show', 'id' => $post->id, 'title' => Help::slug($slugTitle)]); ?>

                    <a href="<?= $url; ?>" class="media-left">
                        <img alt="<?= $slugTitle; ?>" src="<?= $post->getThumbnailUrl(); ?>">
                    </a>
                    <div class="media-body">
                        <?php $slugTitle = ArrayHelper::getValue($post->trl,'name',$post->name); ?>
                        <a href="<?= $url; ?>" class="catg_title"><?= $post->trl->name; ?></a>
                        <span><i class="fa fa-comment"></i>&nbsp;<span><?= count($post->comments); ?></span></span>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
