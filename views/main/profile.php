<?php
use himiklab\thumbnail\EasyThumbnailImage;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View */
/* @var $user \app\models\User; */
/* @var $controller \app\controllers\MainController */
/* @var $items array[] */
/* @var $carouselPosts \app\models\Post[] */
/* @var $pages \yii\data\Pagination */
/* @var $type string */
/* @var $materialCount int */
/* @var $postCount int */

$controller = $this->context;
?>

<!-- BANNER MOBILE::START-->
<section class="topBanner hidden-sm-up">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-xs-center">
                <?= $this->render('/common/_banners',['banners' => ArrayHelper::getValue($controller->banners,'TOP_BANNER')]); ?>
            </div>
        </div>
    </div>
</section>

<!-- OWL DESKTOP::START-->
<section class="topCarousel hidden-xs-down">
    <div id="owlTop" data-current-page="1" data-loading="<?= Url::to(['main/category-ajax','carousel' => 1]); ?>">
        <?= $this->render('/common/_carousel',['posts' => $carouselPosts]); ?>
    </div>
</section>

<!-- CONTENT::START-->
<section class="content separatorCards">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2">
                <?= $this->render('/common/_main_menu',[
                    'categories' => $controller->mainMenu,
                    'active' => null
                ]); ?>
            </div>

            <div class="col-sm-8 col-lg-7 no-pad-r">
                <div class="innerWrapper">
                    <h1><?= $user->name.' '.$user->surname; ?></h1>
                    <div class="profile"><img class="img-fluid" src="<?= $user->getAvatar(); ?>">
                        <div class="profile__right">
                            <div class="profile__username">Профиль участника</div>
                            <div class="profile__stats">
                                <p>Количество материалов:<b><a href="<?= Url::to(['main/profile-details', 'id' => $user->id, 'type' => 'materials']); ?>"><?= $materialCount; ?></a></b></p>
                                <p>Количество постов:<b><a href="<?= Url::to(['main/profile-details', 'id' => $user->id, 'type' => 'posts']); ?>"><?= $postCount; ?></a></b></p>
                                <p>Количество комментариев:<b><a href="<?= Url::to(['main/profile-details', 'id' => $user->id, 'type' => 'comments']); ?>"><?= (int)$user->counter_comments; ?></a></b></p>
                            </div>

                            <?php if(!empty($user->fb_user_id)): ?>
                                <a target="_blank" class="btn btn-fb" href="<?= "https://www.facebook.com/{$user->fb_user_id}/" ?>">
                                    <i class="ico ico-fb"></i>
                                    <span>Профиль в facebook</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if(!empty($items)): ?>
                        <div class="headingBorder">
                            <h2>Лента Активности: <b><?= $user->name.' '.$user->surname; ?></b></h2>
                        </div>

                        <div class="content__card">
                            <?php foreach ($items as $item): ?>
                                <?php if($item['type'] == 'post'): ?>
                                    <!-- card-->
                                    <div class="content__card">
                                        <div class="content__card__image">
                                            <a rel="canonical" href="<?= Url::to(['main/post', 'id' => $item['post_id']]); ?>">
                                                <?php if(!empty($item['image_path'] && file_exists(Yii::getAlias('@webroot/uploads/img/'.$item['image_path'])))): ?>
                                                    <img class="img-fluid" src="<?= EasyThumbnailImage::thumbnailFileUrl(Yii::getAlias('@webroot/uploads/img/'.$item['image_path']),484,276); ?>">
                                                <?php elseif (!empty($item['image_url'])): ?>
                                                    <img class="img-fluid" src="<?= $item['image_url']; ?>">
                                                <?php else: ?>
                                                    <img class="img-fluid" src="http://placehold.it/484x276">
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <a class="content__card__title hidden-sm-up" href="#"><?= $item['post_name']; ?></a>
                                        <div class="content__card__content">
                                            <a rel="canonical" class="content__card__title hidden-xs-down" href="<?= Url::to(['main/post', 'id' => $item['post_id']]); ?>">
                                                <?= $item['post_name']; ?>
                                            </a>
                                            <div class="content__card__intro">
                                                <p><?= StringHelper::truncateWords(strip_tags($item['content']),20); ?></p>
                                                <?php if(!empty($item['author_id'])): ?>
                                                    <a rel="canonical" href="<?= Url::to(['main/profile','id'=> $item['author_id']]); ?>">
                                                        <?= $item['name'].' '.$item['surname']; ?>
                                                    </a>
                                                <?php endif; ?>
                                                <span>• <?= substr($item['published_at'],0,16); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($item['type'] == 'comment'): ?>
                                    <div class="contentComments__card content__card">
                                        <img class="img-fluid" src="<?= $item['avatar_file']; ?>">
                                        <div class="contentComments__card__content">
                                            <b><a rel="canonical" href="<?= Url::to(['main/profile','id'=> $item['author_id']]); ?>"><?= $item['name'].' '.$item['surname']; ?></a><span>-  <?= substr($item['published_at'],0,16); ?></span></b>
                                            <p><?= $item['content']; ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?= LinkPager::widget([
                            'pagination' => $pages,
                            'nextPageLabel' => '<i class="ico ico-paginate-next"></i>',
                            'nextPageCssClass' => 'last',
                            'prevPageLabel' => '<i class="ico ico-paginate-prev"></i>',
                            'prevPageCssClass' => 'last',
                            'maxButtonCount' => 5
                        ]); ?>
                    <?php endif; ?>
                </div>
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
