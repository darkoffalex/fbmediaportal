<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $post \app\models\Post */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;
$comments = $post->getNestedOrderedComments();
?>

<div class="modal-header">
    <h4 class="modal-title"><?= Yii::t('admin','Comments for'); ?> : <?= $post->name; ?></h4>
</div>

<div class="modal-body box-comments" style="max-height: 500px; overflow-y: scroll;">

    <?php if(!empty($comments)): ?>
        <?php foreach($comments as $comment): ?>
            <div class="box-comment" <?php if(!empty($comment->parent)): ?> style="padding-left: 20px; font-size: 12px;" <?php endif; ?>>
                <img class="img-circle img-sm" src="<?= $comment->author->getAvatar(); ?>" alt="user image">
                <div class="comment-text">
                    <span class="username"><?= $comment->author->name.' '.$comment->author->surname; ?><span class="text-muted pull-right"><?= $comment->created_at; ?></span></span>
                    <?= $comment->text; ?>
                </div><!-- /.comment-text -->
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="box-comment">
            <p><?= Yii::t('admin','No comments found for this post'); ?></p>
        </div>
    <?php endif; ?>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
</div>

