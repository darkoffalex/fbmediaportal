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
/* @var $popularPosts  */
/* @var $category \app\models\Category */

/* @var $currentIds int[] */
/* @var $siblingIds int[] */

$user = Yii::$app->user->identity;
$controller = $this->context;
?>

    <!-- BANNER MOBILE::START-->
    <section class="topBanner hidden-sm-up">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 text-xs-center">
<!--                    --><?//= BannersWidget::widget(['position' => 'TOP_BANNER']); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- OWL DESKTOP::START-->
    <section class="topCarousel hidden-xs-down">
        <div id="owlTop" data-current-page="1" data-loading="<?= Url::to(['main/category-ajax','carousel' => 1,'id' => !empty($category) ? $category->id : null]); ?>">
            <?= $this->render('/common/_carousel',['posts' => $mainPosts]); ?>
        </div>
    </section>

    <!-- CONTENT::START-->
    <section class="content">
        <div class="container">
            <div class="row">
                <div class="hidden-md-down col-lg-2">

                    <?= $this->render('/common/_main_menu',[
                        'categories' => $controller->mainMenu,
                        'active' => !empty($category) ? $category->id : null
                    ]); ?>

                </div>

                <div class="col-sm-8 col-lg-7 no-pad-r">

                    <!-- cards-->

                    <?php /* @var $slicedPart1 \app\models\Post[] */ ?>
                    <?php $slicedPart1 = array_slice($mainPosts,0,5); ?>

                    <?php foreach ($slicedPart1 as $index => $post): ?>

                        <?php if($index == 0): ?>
                            <div class="content__card content__card--wide">
                                <div class="heading"><?= $category->trl->name; ?></div>
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
                                        <a href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                            <?= $post->author->name.' '.$post->author->surname; ?>
                                        </a>
                                    <?php else: ?>
                                        <a href=""><?= $post->author_custom_name; ?></a>
                                    <?php endif; ?>
                                    <span>• <?= substr($post->published_at,0,16); ?></span>
                                </div>
                                <?php if($post->content_type_id != \app\helpers\Constants::CONTENT_TYPE_VIDEO): ?>
                                    <div class="content__card__comments"><span><?= $post->comment_count; ?> комментариев</span></div>
                                <?php else: ?>
                                    <div class="content__card__comments"><span><?= $post->comment_count; ?></span></div>
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
                                            <a href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
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
                                <div id="owlTopMobile" data-current-page="1" data-loading="<?= Url::to(['main/category-ajax','carousel' => 1,'id' => !empty($category) ? $category->id : null]); ?>">
                                    <?= $this->render('/common/_carousel',['posts' => $mainPosts]); ?>
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
                                <?= $this->render('/common/_banners',[
                                    'imgAttributes' => ['class' => 'img-fluid'],
                                    'banners' => ArrayHelper::getValue($controller->banners,'TOP_RIGHT_1')
                                ]); ?>
                            </a>
                        </div>

                        <div class="content__sidebar__banner">
                            <a href="#">
                                <?= $this->render('/common/_banners',[
                                    'imgAttributes' => ['class' => 'img-fluid'],
                                    'banners' => ArrayHelper::getValue($controller->banners,'TOP_RIGHT_2')
                                ]); ?>
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
                        <?= $this->render('/common/_posts_list',['posts' => $mainPosts, 'label' => 'Последнее', 'type' => 'latest']); ?>
                        <?= $this->render('/common/_posts_list',['posts' => $popularPosts, 'label' => 'Популярное', 'type' => 'popular', 'ico' => 'ico-cat-hot']); ?>
                        <?= $this->render('/common/_posts_list',['posts' => $mainPosts, 'label' => 'Полезное о Турции', 'ico' => 'ico-cat-turkey']); ?>
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

                    <?php /* @var $slicedPart2 \app\models\Post[] */ ?>
                    <?php $slicedPart2 = array_slice($mainPosts,5,3); ?>

                    <?php if(!empty($slicedPart2)): ?>
                        <?php if($slicedPart2[0]->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                            <?php Help::swap($mainPosts,0,1); ?>
                        <?php endif; ?>

                        <?php foreach ($slicedPart2 as $index => $post): ?>
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
                                            <a href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                                <?= $post->author->name.' '.$post->author->surname; ?>
                                            </a>
                                        <?php else: ?>
                                            <a href=""><?= $post->author_custom_name; ?></a>
                                        <?php endif; ?>
                                        <span>• <?= substr($post->published_at,0,16); ?></span>
                                    </div>

                                    <?php if($post->content_type_id != Constants::CONTENT_TYPE_VIDEO): ?>
                                        <div class="content__card__comments"><span><?= $post->comment_count; ?> комментариев</span></div>
                                    <?php else: ?>
                                        <div class="content__card__comments"><span><?= $post->comment_count; ?></span></div>
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
                                                <a href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
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
                    <?php endif; ?>
                </div>
                <!--sidebar-->
                <div class="col-sm-4 col-lg-3 no-pad-l hidden-xs-down">
                    <div class="content__sidebar">
                        <div class="content__sidebar__banner">
                            <?= $this->render('/common/_banners',[
                                'imgAttributes' => ['class' => 'img-fluid'],
                                'banners' => ArrayHelper::getValue($controller->banners,'BOTTOM_RIGHT_1')
                            ]); ?>
                        </div>

                        <div class="content__sidebar__banner">
                            <?= $this->render('/common/_banners',[
                                'imgAttributes' => ['class' => 'img-fluid'],
                                'banners' => ArrayHelper::getValue($controller->banners,'BOTTOM_RIGHT_2')
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--CARDS-->
<?= $this->render('/common/_forum_posts',['posts' => $forumPosts, 'label' => 'Форум']); ?>

<div class="loadable-content" data-current-page="1" data-postload="<?= Url::to(['main/category-ajax','id' => !empty($category) ? $category->id : null]); ?>"></div>
