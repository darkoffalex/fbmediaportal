<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $comments \app\models\Comment[] */
/* @var $viewOnly bool */
/* @var $timeLine bool */

$user = Yii::$app->user->identity;
?>

<?php foreach ($comments as $comment): ?>
    <div class="contentComments__card">
        <img class="img-fluid" src="<?= $comment->author->getAvatar(); ?>">
        <div class="contentComments__card__content">
            <b>
                <?php if(!$timeLine): ?>
                    <a href="<?= Url::to(['main/profile','id' => $comment->author_id]); ?>"><?= $comment->author->name.' '.$comment->author->surname; ?></a> -
                <?php endif; ?>
                <span><?= Help::datefmt($comment->created_at); ?></span>
            </b>

            <p><?= $comment->text; ?></p>

            <?php if($timeLine): ?>
                <div class="comment-for">
                    Комментарий к: <a href="<?= $comment->post->getUrl(); ?>"><?= $comment->post->trl->name; ?></a>
                </div>
            <?php endif; ?>

            <?php if(count($comment->children) > 0): ?>
                <a class="reloading-comments" data-click-load="#children-for-<?= $comment->id; ?>" href="<?= Url::to(['main/children-comments-ajax', 'id' => $comment->id]); ?>">
                    <?= count($comment->children); ?> ответов
                </a>
            <?php endif; ?>

            <div id="children-for-<?= $comment->id; ?>">
            </div>

            <?php if(!Yii::$app->user->isGuest && !$viewOnly): ?>
            <form data-container="#children-for-<?= $comment->id; ?>" class="contentComments__card__child" method="post" action="<?= Url::to(['main/children-comments-add','id'=>$comment->id]); ?>">
                <img class="img-fluid" src="<?= $user->getAvatar(); ?>">
                <?= Html::hiddenInput(Yii::$app->getRequest()->csrfParam,Yii::$app->getRequest()->getCsrfToken()); ?>
                <textarea class="child-comment-text" name="Comment[text]" placeholder="напишите ответ"></textarea>
                <button class="btn btn-small" type="submit">Отправить</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
