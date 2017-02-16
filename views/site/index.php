<?php
use app\widgets\MainMenuWidget;
use app\widgets\CarouselWidget;
use app\widgets\BannersWidget;
use app\widgets\LatestPostsWidget;
use app\widgets\PopularPostWidget;
use app\widgets\TurkeyPostsWidget;
use app\widgets\ForumPostsWidget;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $user \yii\web\User */
/* @var $controller \app\controllers\SiteController */
/* @var $posts \app\models\Post[] */
/* @var $forumPosts \app\models\Post[] */

$user = Yii::$app->user->identity;
$controller = $this->context;

//meta tags
$this->registerMetaTag(['name' => 'description', 'content' => $controller->commonSettings->meta_description]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $controller->commonSettings->meta_keywords]);

//open-graph meta tags
$this->registerMetaTag(['property' => 'og:description', 'content' => ""]);
$this->registerMetaTag(['property' => 'og:url', 'content' => ""]);
$this->registerMetaTag(['property' => 'og:site_name', 'content' => ""]);
$this->registerMetaTag(['property' => 'og:title', 'content' => ""]);
$this->registerMetaTag(['property' => 'og:image', 'content' => ""]);
$this->registerMetaTag(['property' => 'og:image:width', 'content' => '200']);
$this->registerMetaTag(['property' => 'og:image:height', 'content' => '200']);
?>

<!-- BANNER MOBILE::START-->
<section class="topBanner hidden-sm-up">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-xs-center">
                <?= BannersWidget::widget(['position' => 'TOP_BANNER']); ?>
            </div>
        </div>
    </div>
</section>

<!-- OWL DESKTOP::START-->
<section class="topCarousel hidden-xs-down">
    <div id="owlTop" data-current-page="1" data-loading="<?= Url::to(['site/carousel-load']); ?>">
        <?= CarouselWidget::widget(); ?>
    </div>
</section>

<!-- CONTENT::START-->
<section class="content">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2">
                <?= MainMenuWidget::widget(); ?>
            </div>

            <div class="col-sm-8 col-lg-7 no-pad-r">

                <!-- cards-->

                <?php /* @var $slicedPart1 \app\models\Post[] */ ?>
                <?php $slicedPart1 = array_slice($posts,0,3); ?>

                <?php foreach ($slicedPart1 as $index => $post): ?>

                    <?php if($index == 0): ?>
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
                            <div class="content__card__comments"><span><?= count($post->comments); ?> комментариев</span></div>
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

                    <?php if($index == 0): ?>
                        <!-- OWL DESKTOP::START-->
                        <section class="topCarousel hidden-sm-up">
                            <div id="owlTopMobile" data-current-page="1" data-loading="<?= Url::to(['site/carousel-load']); ?>">
                                <?= CarouselWidget::widget(); ?>
                            </div>
                        </section>
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>

            <!--sidebar-->
            <div class="col-sm-4 col-lg-3 no-pad-l">
                <div class="content__sidebar content__sidebar--top">
                    <div class="content__sidebar__metrics text-xs-center">
                        <div class="content__sidebar__metricCurrency"><span>USD 59.3</span><i class="ico ico-growth-up"></i></div>
                        <div class="content__sidebar__metricCurrency"><span>EUR 63.12</span><i class="ico ico-growth-down"></i></div>
                    </div>
                    <div class="content__sidebar__metrics">
                        <div class="content__sidebar__metricWeather"><span>Погода в <b>Анталии</b></span>
                            <div class="content__sidebar__metricWeather__row">
                                <div class="content__sidebar__metricWeather__left"><i class="ico ico-weather-rain"></i></div>
                                <div class="content__sidebar__metricWeather__right"><span>+31 C</span>
                                    <p>Временами дожди</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content__sidebar__metrics">
                        <div class="content__sidebar__metricWeather"><span>Погода в <b>Стамбуле</b></span>
                            <div class="content__sidebar__metricWeather__row">
                                <div class="content__sidebar__metricWeather__left"><i class="ico ico-weather-rain"></i></div>
                                <div class="content__sidebar__metricWeather__right"><span>+31 C</span>
                                    <p>Временами дожди</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content__sidebar__banner">
                        <a href="#">
                            <?= BannersWidget::widget(['position' => 'TOP_RIGHT_1','imgAttributes' => ['class' => 'img-fluid']]); ?>
                        </a>
                    </div>

                    <div class="content__sidebar__banner">
                        <a href="#">
                            <?= BannersWidget::widget(['position' => 'TOP_RIGHT_2','imgAttributes' => ['class' => 'img-fluid']]); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!--CATEGORIES-->
<section class="categories">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2"></div>
            <div class="col-md-12 col-lg-10">
                <div class="row">
                    <?= LatestPostsWidget::widget(['label' => 'Последнее']); ?>
                    <?= PopularPostWidget::widget(['label' => 'Популярное']); ?>
<!--                    --><?//= TurkeyPostsWidget::widget(['label' => 'Полезное о Турции']); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTENT::START-->
<section class="content">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2"></div>
            <div class="col-sm-8 col-lg-7 no-pad-r">

                <?php /* @var $slicedPart1 \app\models\Post[] */ ?>
                <?php $slicedPart1 = array_slice($posts,3,3); ?>

                <?php foreach ($slicedPart1 as $index => $post): ?>
                    <?php if($index == 0): ?>
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

                            <?php if($post->content_type_id != \app\helpers\Constants::CONTENT_TYPE_VIDEO): ?>
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
            <div class="col-sm-4 col-lg-3 no-pad-l hidden-xs-down">
                <div class="content__sidebar">
                    <div class="content__sidebar__banner">
                        <?= BannersWidget::widget(['position' => 'BOTTOM_RIGHT_1','imgAttributes' => ['class' => 'img-fluid']]); ?>
                    </div>

                    <div class="content__sidebar__banner">
                        <?= BannersWidget::widget(['position' => 'BOTTOM_RIGHT_2','imgAttributes' => ['class' => 'img-fluid']]); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!--CARDS-->

<?= ForumPostsWidget::widget(['label' => 'Форум', 'posts' => $forumPosts]); ?>

<div class="loadable-content" data-current-page="1" data-postload="<?= Url::to(['site/post-load']); ?>"></div>
