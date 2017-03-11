<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\helpers\Constants;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $controller \app\controllers\MainController */
/* @var $carouselPosts \app\models\Post[] */
/* @var $title string */

$user = Yii::$app->user->identity;
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
<section class="content">
    <div class="container">
        <div class="row">

            <div class="hidden-md-down col-lg-2">
                <?= $this->render('/common/_main_menu',[
                    'categories' => $controller->mainMenu,
                    'active' => null
                ]); ?>
            </div>

            <div class="col-sm-8 col-lg-7 no-pad-r">
                <!-- cards-->
                <div class="content__card content__card--inner content__card--wide">

                    <h1 class="content__card__title"><?= $title ?></h1>

                    <div class="content__card__crumbs">
                        <a href="<?= Url::to(['main/index']); ?>">Главная</a><span class="crumb-seprator">-</span><span class="current"><?= $title ?></span>
                    </div>

                    <div class="content__card__pageContent">
                        <p>
                            Решили куда-то полететь? Давайте подберем авиабилеты от Aviasales:
                            <script charset="utf-8" src="//www.travelpayouts.com/widgets/dc4096fe17b4137a71a25c06341d65a8.js?v=958" async></script>
                        </p>

                        <p>
                            Билеты есть? Отлично! Забронируем отель?
                            <script charset="utf-8" src="//www.travelpayouts.com/widgets/5e35f9a49834431eabc7d8110b9cb734.js?v=958" async></script>
                        </p>

                        <p>
                            На границе вас наверняка спросят, есть ли у вас медицинская страховка. Да и для собственного спокойствия она вам не помешает. Здесь можно срывнить и купить самую выгодную страхову для поездки за рубеж:
                            <script src="//c49.travelpayouts.com/content?promo_id=1437&shmarker=123304&logo=true&sticker=false&hide_partners=true&country1=c-82" charset="utf-8" async></script>
                        </p>

                        <p>
                            Если вы любите передвигаться самостоятельно, наверняка вы захотите прямо в аэропорту прибытия сесть за руль? Самые выгодные варианты от от Renalcars:
                            <script src="//c13.travelpayouts.com/content?promo_id=1510&shmarker=123304" charset="utf-8"></script>
                            <script src="//c22.travelpayouts.com/content?promo_id=1504&shmarker=123304&locale=ru&color_scheme=bg&header=%D0%9D%D0%B5%20%D0%BB%D1%8E%D0%B1%D0%B8%D1%82%D0%B5%20%D0%B1%D1%80%D0%B0%D1%82%D1%8C%20%D0%BC%D0%B0%D1%88%D0%B8%D0%BD%D1%83%20%D0%B2%20%D0%B0%D1%80%D0%B5%D0%BD%D0%B4%D1%83%3F%20%D0%97%D0%B0%D0%BA%D0%B0%D0%B6%D0%B8%D1%82%D0%B5%20%D0%BA%D0%BE%D0%BC%D1%84%D0%BE%D1%80%D1%82%D0%BD%D1%8B%D0%B9%20%D1%82%D1%80%D0%B0%D0%BD%D1%81%D1%84%D0%B5%D1%80!&b_counter=false&b_benefit=false&b_descr=false&b_about=false&b_map=false&b_reviews=false&b_breadcrumbs=false" charset="utf-8"></script>
                        </p>

                        <p>
                            А может быть вы любите все и сразу "в одном флаконе"? Давайте выберем для вас готовый тур с перелетом и отелем?
                            <script src="https://c18.travelpayouts.com/content?promo_id=1492&shmarker=123304&countries=92" charset="utf-8" async></script>
                        </p>
                    </div>
                </div>
            </div>

            <!--sidebar-->
            <div class="col-sm-4 col-lg-3 no-pad-l">
                <div class="content__sidebar content__sidebar--top clearfix">
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
