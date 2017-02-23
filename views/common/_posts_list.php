<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $controller \app\controllers\MainController */
/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $active int */
/* @var $label string */
/* @var $type string */

$controller = $this->context;
$user = Yii::$app->user->identity;
?>

<?php $posts = array_slice($posts,0,7); ?>
<div class="col-md-12 col-lg-4">
    <div class="categories__name"><i class="ico <?= !empty($ico) ? $ico : 'ico-cat-news'; ?>"></i><span><?= $label; ?></span></div>
    <ul class="categories__list">
        <?php foreach ($posts as $post): ?>
            <li><a rel="canonical" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php if(!empty($type)): ?>
        <a rel="canonical" class="categories__more" href="<?= Url::to(['main/all','type' => $type]); ?>">Все материалы</a>
    <?php endif; ?>
</div>
