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

<?php foreach ($comments as $comment): ?>
    <div class="contentComments__card">
        <img class="img-fluid" src="<?= $comment->author->getAvatar(); ?>">
        <div class="contentComments__card__content">
            <b><a href="#"><?= $comment->author->name.' '.$comment->author->surname; ?></a><span>-  <?= substr($comment->created_at,0,16); ?></span></b>
            <p><?= $comment->text; ?></p>

            <?php if(count($comment->children) > 0): ?>
                <a class="reloading-comments" data-click-load="#children-for-<?= $comment->id; ?>" href="<?= Url::to(['posts/children-comments', 'id' => $comment->id]); ?>">
                    <?= count($comment->children); ?> ответов
                </a>
            <?php endif; ?>

            <div id="children-for-<?= $comment->id; ?>">
            </div>

            <?php if(!Yii::$app->user->isGuest): ?>
            <form data-container="#children-for-<?= $comment->id; ?>" class="contentComments__card__child" method="post" action="<?= Url::to(['posts/add-child-comment','cid'=>$comment->id]); ?>">
                <img class="img-fluid" src="<?= $user->getAvatar(); ?>">
                <?= Html::hiddenInput(Yii::$app->getRequest()->csrfParam,Yii::$app->getRequest()->getCsrfToken()); ?>
                <textarea class="child-comment-text" name="Comment[text]" placeholder="напишите ответ"></textarea>
                <button class="btn btn-small" type="submit">Отправить</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
