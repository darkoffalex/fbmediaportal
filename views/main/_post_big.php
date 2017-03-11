<?php
use yii\helpers\Url;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $category \app\models\Category */
/* @var $post \app\models\Post */
?>

<div class="content__card content__card--wide">
    <?php if(!empty($category->trl->name)): ?>
        <h1 class="heading"><?= $category->trl->name; ?></h1>
    <?php endif; ?>

    <?php if($post->content_type_id == Constants::CONTENT_TYPE_VIDEO && (!empty($post->video_key_fb) || !empty($post->video_key_yt))): ?>
        <?php if(!empty($post->video_key_fb)): ?>
            <?php $poster = !empty($post->postImages[0]) ? $post->getFirstImageUrlEx(706,311) : null; ?>
            <video class="afterglow" <?php if(!empty($poster)): ?>poster="<?= $poster; ?>"<?php endif; ?> id="fb_vid_<?= $post->video_attachment_id_fb; ?>" width="665" height="294" src="<?= $post->video_key_fb; ?>"></video>
        <?php endif; ?>
        <?php if(!empty($post->video_key_yt)): ?>
            <iframe width="100%" height="301px" src="<?= Help::youtubeurl($post->video_key_yt); ?>" frameborder="0" allowfullscreen></iframe>
        <?php endif; ?>
    <?php else: ?>
        <a  href="<?= $post->getUrl(); ?>">
            <?php $titleAlt = !empty($post->postImages[0]->trl) ? $post->postImages[0]->trl->name : ''; ?>
            <img title="<?= $titleAlt; ?>" alt="<?= $titleAlt; ?>" width="706" class="img-fluid" src="<?= $post->getFirstImageUrlEx(706,311); ?>">
        </a>
        <?php if(!empty($post->postImages[0]->trl->signature)): ?>
            <div class="content__card__copy"><?= $post->postImages[0]->trl->signature; ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <h2>
        <a  class="content__card__title" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
    </h2>

    <div class="content__card__intro">
        <p><?= $post->trl->small_text; ?></p>
    </div>

    <div class="content__card__info">
        <?php if(!empty($post->author)): ?>
            <a href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>"><?= $post->author->name.' '.$post->author->surname; ?></a>
        <?php else: ?>
            <a href="#"><?= $post->author_custom_name; ?></a>
        <?php endif; ?>
        <span>• <?= Help::datefmt($post->published_at); ?></span>
    </div>

    <?php if($post->comment_count > 0): ?>
        <?php if($post->content_type_id != \app\helpers\Constants::CONTENT_TYPE_VIDEO): ?>
            <div class="content__card__comments"><span><?= $post->comment_count; ?> комментариев</span></div>
        <?php else: ?>
            <div class="content__card__comments"><span><?= $post->comment_count; ?></span></div>
        <?php endif; ?>
    <?php endif; ?>
</div>