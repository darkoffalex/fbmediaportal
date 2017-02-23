<?php
use app\helpers\Constants;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $controller \app\controllers\MainController */
/* @var $mainPosts \app\models\Post[] */
/* @var $forumPosts \app\models\Post[] */

$user = Yii::$app->user->identity;
$controller = $this->context;
?>
<?php if(!empty($mainPosts)): ?>
<section class="content">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2"></div>
            <div class="col-sm-8 col-lg-7 no-pad-r">

                <?php if($mainPosts[0]->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                    <?php Help::swap($mainPosts,0,1); ?>
                <?php endif; ?>

                <?php foreach ($mainPosts as $index => $post): ?>
                    <?php if($post->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                        <div class="content__card content__card--wide">
                            <a rel="canonical" href="<?= $post->getUrl(); ?>"><img width="706" class="img-fluid" src="<?= $post->getFirstImageUrlEx(706,311); ?>"></a>
                            <?php if(!empty($post->postImages[0]->trl->signature)): ?>
                                <div class="content__card__copy"><?= $post->postImages[0]->trl->signature; ?></div>
                            <?php endif; ?>
                            <a rel="canonical" class="content__card__title" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                            <div class="content__card__intro">
                                <p><?= $post->trl->small_text; ?></p>
                            </div>
                            <div class="content__card__info">
                                <?php if(!empty($post->author)): ?>
                                    <a rel="canonical" href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                        <?= $post->author->name.' '.$post->author->surname; ?>
                                    </a>
                                <?php else: ?>
                                    <a href="#"><?= $post->author_custom_name; ?></a>
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
                                <a rel="canonical" href="<?= $post->getUrl(); ?>"><img style="width: 240px; height: 136px;" width="484" class="img-fluid" src="<?= $post->getThumbnailUrl(484,276); ?>"></a>
                            </div>

                            <a rel="canonical" class="content__card__title hidden-sm-up" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>

                            <div class="content__card__content">
                                <a class="content__card__title hidden-xs-down" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                <div class="content__card__intro">
                                    <p><?= $post->trl->small_text; ?></p>
                                    <?php if(!empty($post->author)): ?>
                                        <a rel="canonical" href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                            <?= $post->author->name.' '.$post->author->surname; ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="#"><?= $post->author_custom_name; ?></a>
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
<?php endif; ?>
<?php if(!empty($forumPosts)): ?>
    <?= $this->render('/common/_forum_posts',['posts' => $forumPosts, 'label' => 'Форум']); ?>
<?php endif; ?>
