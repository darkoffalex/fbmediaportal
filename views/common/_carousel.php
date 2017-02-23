<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $controller \app\controllers\MainController */
/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $error string */

$controller = $this->context;
$user = Yii::$app->user->identity;
?>

<?php if(!empty($posts) && is_array($posts)): ?>
    <?php foreach ($posts as $post): ?>
        <div class="topCarousel__card">
            <a rel="canonical" href="<?= $post->getUrl(); ?>">
                <img width="130" height="130" src="<?= $post->getThumbnailUrl(130,130); ?>">
                <span><?= $post->trl->name; ?></span>
            </a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>