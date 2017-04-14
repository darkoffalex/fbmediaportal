<?php

/* @var $post \app\models\Post */
/* @var $user \app\models\User */
/* @var $comments \app\models\Comment[] */

use yii\helpers\Url;
use kartik\helpers\Html;
?>

<!-- Comment section-->
<div class="contentComments" data-current-page="1" data-postload="<?= Url::to(['main/comments-ajax', 'id' => $post->id]); ?>">
    <h2>Комментарии <span class="comment-muted"><?= $post->comment_count; ?></span></h2>

    <a name="_=_" id="_=_" title="_=_"></a>
    <form method="post" class="contentComments__post <?php if(Yii::$app->user->isGuest):?>click-auth<?php endif; ?>">
        <img class="img-fluid" src="<?= !Yii::$app->user->isGuest ?  $user->getAvatar() : Url::to('@web/img/no_user.png'); ?>">
        <div class="form-group">
            <label for="comment">Сообщение</label>
            <?= Html::hiddenInput(Yii::$app->getRequest()->csrfParam,Yii::$app->getRequest()->getCsrfToken()); ?>
            <textarea name="Comment[text]" id="comment" class="form-control"></textarea>
        </div>
        <button data-no-empty=".contentComments__post" class="btn" type="submit">Отправить</button>
    </form>

    <?= $this->render('_load_comments',['comments' => $comments, 'post' => $post]); ?>
</div>