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
/* @var $popularPosts \app\models\Post[] */
/* @var $turkeyPosts \app\models\Post[] */
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

                        <?php if($index == 0 || $post->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                            <?= $this->render('_post_big',['category' => $category, 'post' => $post]); ?>
                        <?php else: ?>
                            <?= $this->render('_post_small',['category' => $category, 'post' => $post]); ?>
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
                            <?= $this->render('/common/_banners',[
                                'imgAttributes' => ['class' => 'img-fluid'],
                                'banners' => ArrayHelper::getValue($controller->banners,'CURRENCY')
                            ]); ?>
                        </div>
                        <div class="content__sidebar__metrics">
                            <p class="weather-title">Погода в <b>Турции</b></p>
                            <?= $this->render('/common/_banners',[
                                'imgAttributes' => ['class' => 'img-fluid'],
                                'banners' => ArrayHelper::getValue($controller->banners,'WEATHER')
                            ]); ?>
                        </div>

                        <div class="content__sidebar__banner">
                            <?= $this->render('/common/_banners',[
                                'imgAttributes' => ['class' => 'img-fluid'],
                                'banners' => ArrayHelper::getValue($controller->banners,'TOP_RIGHT_1')
                            ]); ?>
                        </div>

                        <div class="content__sidebar__banner">
                            <?= $this->render('/common/_banners',[
                                'imgAttributes' => ['class' => 'img-fluid'],
                                'banners' => ArrayHelper::getValue($controller->banners,'TOP_RIGHT_2')
                            ]); ?>
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
                        <?= $this->render('/common/_posts_list',['posts' => $turkeyPosts, 'label' => 'Полезное о Турции', 'type' => 'turkey', 'ico' => 'ico-cat-turkey']); ?>
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
                                <?= $this->render('_post_big',['category' => $category, 'post' => $post]); ?>
                            <?php else: ?>
                                <?= $this->render('_post_small',['category' => $category, 'post' => $post]); ?>
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
