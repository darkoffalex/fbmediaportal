<?php
use app\widgets\MainMenuWidget;
use app\widgets\CarouselWidget;
use app\widgets\BannersWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this \yii\web\View */
/* @var $user \yii\web\User */
/* @var $controller \app\controllers\SiteController */
/* @var $posts \app\models\Post[] */
/* @var $pages \yii\data\Pagination */
/* @var $type string */

$user = Yii::$app->user->identity;
$controller = $this->context;
$this->title = "Все материалы | ".ArrayHelper::getValue(['latest' => 'Последние', 'popular' => 'Популярные'],$type,'Последние');

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
    <div id="owlTop">
        <?= CarouselWidget::widget(); ?>
    </div>
</section>

<!-- CONTENT::START-->
<section class="content separatorCards">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2">
                <?= MainMenuWidget::widget(); ?>
            </div>

            <div class="col-sm-8 col-lg-7 no-pad-r">
                <div class="innerWrapper">
                    <h1><?= ArrayHelper::getValue(['latest' => 'Последние', 'popular' => 'Популярные'],$type,'Последние'); ?></h1>

                    <div class="sorting">
                        <a class="btn btn-outline <?= $type=='latest' ? 'active' : ''; ?>" href="<?= Url::to(['site/all','type' => 'latest']); ?>">
                            <span>Последнее</span>
                        </a>
                        <a class="btn btn-outline <?= $type=='popular' ? 'active' : ''; ?>" href="<?= Url::to(['site/all','type' => 'popular']); ?>">
                            <span>Популярное</span>
                        </a>
                    </div>

                    <?php foreach ($posts as $post): ?>
                        <!-- card-->
                        <div class="content__card">
                            <div class="content__card__image">
                                <a href="<?= $post->getUrl(); ?>">
                                    <img class="img-fluid" src="<?= $post->getThumbnailUrl(484,276); ?>">
                                </a>
                            </div>
                            <a class="content__card__title hidden-sm-up" href="#"><?= $post->trl->name; ?></a>
                            <div class="content__card__content"><a class="content__card__title hidden-xs-down" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                <div class="content__card__intro">
                                    <p><?= $post->trl->small_text; ?></p><a href="#"><?= $post->author->name.' '.$post->author->surname; ?></a><span>• <?= substr($post->published_at,0,16); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?= LinkPager::widget([
                        'pagination' => $pages,
                        'nextPageLabel' => '<i class="ico ico-paginate-next"></i>',
                        'nextPageCssClass' => 'last',
                        'prevPageLabel' => '<i class="ico ico-paginate-prev"></i>',
                        'prevPageCssClass' => 'last',
                        'maxButtonCount' => 5
                    ]); ?>

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
                        <?= BannersWidget::widget(['position' => 'TOP_RIGHT_1','imgAttributes' => ['class' => 'img-fluid']]); ?>
                    </div>

                    <div class="content__sidebar__banner">
                        <?= BannersWidget::widget(['position' => 'TOP_RIGHT_2','imgAttributes' => ['class' => 'img-fluid']]); ?>
                    </div>

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
