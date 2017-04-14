<?php
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $category \app\models\Category */
/* @var $post \app\models\Post */
?>

<div class="content__card">
    <div class="content__card__image">
        <?php if($post->content_type_id == Constants::CONTENT_TYPE_VIDEO && (!empty($post->video_key_fb) || !empty($post->video_key_yt))): ?>
            <?php if(!empty($post->video_key_fb)): ?>
                <video width="100%" height="136px" controls style="background-color: rgb(204,204,204);">
                    <source src="<?= $post->video_key_fb; ?>" type="video/mp4">
                </video>
            <?php endif; ?>
            <?php if(!empty($post->video_key_yt)): ?>
                <iframe width="100%" src="<?= $post->video_key_yt; ?>" frameborder="0" allowfullscreen></iframe>
            <?php endif; ?>
        <?php else: ?>
            <a  href="<?= $post->getUrl(); ?>">
<!--                style="width: 240px; height: 136px;"-->
                <?php $titleAlt = !empty($post->postImages[0]->trl) ? $post->postImages[0]->trl->name : ''; ?>
                <img title="<?= $titleAlt; ?>" alt="<?= $titleAlt; ?>" class="img-fluid" src="<?= $post->getThumbnailUrl(484,276); ?>">
            </a>
        <?endif; ?>
    </div>

    <h2>
        <a  class="content__card__title hidden-sm-up" href="<?= $post->getUrl(); ?>">
            <?= $post->trl->name; ?>
        </a>
    </h2>

    <div class="content__card__content" style="position: relative">
        <h2>
            <a  class="content__card__title hidden-xs-down" href="<?= $post->getUrl(); ?>">
                <?= $post->trl->name; ?>
            </a>
        </h2>

        <div class="content__card__intro">
            <p><?= $post->trl->small_text; ?></p>
            <?php if(!empty($post->author)): ?>
                <a  href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                    <?= $post->author->name.' '.$post->author->surname; ?>
                </a>
            <?php else: ?>
                <a href="#"><?= $post->author_custom_name; ?></a>
            <?php endif; ?>
            <span> • <?= Help::datefmt($post->delayed_at); ?></span>

            <?php if($post->comment_count > 0): ?>
                <div class="content__card__comments">
                    <span><?= $post->comment_count; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>