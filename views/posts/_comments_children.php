<?php
use yii\helpers\Url;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $controller \app\controllers\PostsController */
/* @var $comments \app\models\Comment[] */

$user = Yii::$app->user->identity;
$controller = $this->context;
?>

<?php foreach($comments as $comment): ?>
    <div class="contentComments__card__child">
        <a href="<?= Url::to(['site/profile','id' => $comment->author_id]); ?>"><img class="img-fluid" src="<?= $comment->author->getAvatar(); ?>"></a>
        <p><?= $comment->text; ?></p>
    </div>
<?php endforeach; ?>

