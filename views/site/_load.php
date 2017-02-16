<?php
use app\widgets\ForumPostsWidget;
use app\helpers\Constants;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $user \yii\web\User */
/* @var $controller \app\controllers\SiteController */
/* @var $posts \app\models\Post[] */
/* @var $forumPosts \app\models\Post[] */

$user = Yii::$app->user->identity;
$controller = $this->context;
?>

<!-- CONTENT::START-->
<section class="content">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2"></div>
            <div class="col-sm-8 col-lg-7 no-pad-r">

                <?php if($posts[0]->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                    <?php Help::swap($posts,0,1); ?>
                <?php endif; ?>

                <?php foreach ($posts as $index => $post): ?>
                    <?php if($post->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                        <div class="content__card content__card--wide">
                            <a href="<?= $post->getUrl(); ?>"><img width="706" class="img-fluid" src="<?= $post->getFirstImageUrlEx(706,311); ?>"></a>
                            <?php if(!empty($post->postImages[0]->trl->signature)): ?>
                                <div class="content__card__copy"><?= $post->postImages[0]->trl->signature; ?></div>
                            <?php endif; ?>
                            <a class="content__card__title" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                            <div class="content__card__intro">
                                <p><?= $post->trl->small_text; ?></p>
                            </div>
                            <div class="content__card__info">
                                <?php if(!empty($post->author)): ?>
                                    <a href="<?= Url::to(['site/profile','id'=> $post->author_id]); ?>">
                                        <?= $post->author->name.' '.$post->author->surname; ?>
                                    </a>
                                <?php else: ?>
                                    <a href=""><?= $post->author_custom_name; ?></a>
                                <?php endif; ?>
                                <span>• <?= substr($post->published_at,0,16); ?></span>
                            </div>

                            <?php if($post->content_type_id != Constants::CONTENT_TYPE_VIDEO): ?>
                                <div class="content__card__comments"><span><?= count($post->comments); ?> комментариев</span></div>
                            <?php else: ?>
                                <div class="content__card__comments"><span><?= count($post->comments); ?></span></div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="content__card">
                            <div class="content__card__image">
                                <a href="<?= $post->getUrl(); ?>"><img width="484" class="img-fluid" src="<?= $post->getThumbnailUrl(484,276); ?>"></a>
                            </div>

                            <a class="content__card__title hidden-sm-up" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>

                            <div class="content__card__content">
                                <a class="content__card__title hidden-xs-down" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                <div class="content__card__intro">
                                    <p><?= $post->trl->small_text; ?></p>
                                    <?php if(!empty($post->author)): ?>
                                        <a href="<?= Url::to(['site/profile','id'=> $post->author_id]); ?>">
                                            <?= $post->author->name.' '.$post->author->surname; ?>
                                        </a>
                                    <?php else: ?>
                                        <a href=""><?= $post->author_custom_name; ?></a>
                                    <?php endif; ?>
                                    <span>• <?= substr($post->published_at,0,16); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!--sidebar-->
            <div class="col-sm-4 col-lg-3 no-pad-l hidden-xs-down"><div class="content__sidebar"></div></div>

        </div>
    </div>
</section>
<!--CARDS-->

<?= ForumPostsWidget::widget(['label' => 'Форум', 'posts' => $forumPosts]); ?>
