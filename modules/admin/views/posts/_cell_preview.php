<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $model \app\models\Post */
/* @var $lng string */
?>

<?php $trl = $model->getATrl($lng); ?>

<?php if($model->content_type_id == Constants::CONTENT_TYPE_NEWS || $model->content_type_id == Constants::CONTENT_TYPE_ARTICLE): ?>

    <h4 style="font-size: 16px"><?= $trl->name; ?></h4>
    <p style="font-style: italic;"><?= $trl->small_text; ?></p>
    <p><?= strip_tags($trl->text); ?></p>
    <?php if(!empty($model->postImages)): ?>
        <?php foreach($model->postImages as $image): ?>
            <div style="display: inline-block">
                <img src="<?= $image->getFullUrl(); ?>" width="150" class="img-thumbnail" alt="Cinque Terre"><br>
                <span style="font-style: italic;"><?= $image->getATrl($lng)->signature; ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php elseif($model->content_type_id == Constants::CONTENT_TYPE_PHOTO): ?>

    <h4 style="font-size: 16px"><?= $trl->name; ?></h4>
    <p><?= strip_tags($trl->text); ?></p>
    <?php if(!empty($model->postImages)):?>
        <?php $image = $model->postImages[0]; ?>
        <div style="display: inline-block">
            <img src="<?= $image->getFullUrl(); ?>" width="300" class="img-thumbnail" alt="Cinque Terre"><br>
            <span style="font-style: italic;"><?= $image->getATrl($lng)->signature; ?></span>
        </div>
    <?php endif; ?>

<?php elseif($model->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>

    <h4 style="font-size: 16px"><?= $trl->name; ?></h4>
    <p><?= strip_tags($trl->text); ?></p>
    <?php if(!empty($model->video_key_yt)): ?>
        <iframe width="300" src="https://www.youtube.com/embed/<?= $model->video_key_yt; ?>" frameborder="0" allowfullscreen></iframe>
    <?php endif; ?>
    <?php if(!empty($model->video_key_fb)): ?>
        <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Ffacebook%2Fvideos%2F<?= $model->video_key_fb; ?>%2F&width=300&show_text=false&appId=915460531914741&height=150" width="300" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
    <?php endif; ?>

<?php elseif($model->content_type_id == Constants::CONTENT_TYPE_VOTING): ?>

    <h4 style="font-size: 16px"><?= $trl->name; ?></h4>
    <p><?= $trl->question; ?></p>
    <?php if(!empty($model->postVoteAnswers)): ?>
        <?php foreach($model->postVoteAnswers as $a): ?>
            <div><p><?= $a->getATrl($lng)->text; ?></p></div>
            <div class="progress progress-xs">
                <div class="progress-bar progress-bar-danger" style="width: <?= $a->getPercentage(); ?>%"></div>
            </div>
            <br>
        <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>

