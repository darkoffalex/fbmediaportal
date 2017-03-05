<?php
/* @var $posts \app\models\Post[] */
/* @var $controller \app\controllers\MainController */
/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $error string */

$controller = $this->context;
$user = Yii::$app->user->identity;
?>

<?php foreach ($posts as $post): ?>
    <div class="topCarousel__card">
        <a  href="<?= $post->getUrl(); ?>">
            <img width="130" height="130" src="<?= $post->getThumbnailUrl(130,130); ?>">
            <span><?= $post->trl->name; ?></span>
        </a>
    </div>
<?php endforeach; ?>