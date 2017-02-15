<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $widget \app\widgets\CarouselWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */
/* @var $error string */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<?php foreach ($posts as $post): ?>
    <div class="topCarousel__card">
        <a href="<?= $post->getUrl(); ?>">
            <img width="130" height="130" src="<?= $post->getThumbnailUrl(130,130); ?>">
            <span><?= $post->trl->name; ?></span>
        </a>
    </div>
<?php endforeach; ?>

<?= $error; ?>
