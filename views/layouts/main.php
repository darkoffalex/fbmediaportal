<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \yii\web\User */
/* @var $controller \app\components\Controller */
/* @var $social kartik\social\Module */

use app\assets\FrontendAsset;
use yii\helpers\Url;
use yii\helpers\Html;
use app\widgets\MainMenuWidget;
use app\widgets\BannersWidget;

FrontendAsset::register($this);

$user = Yii::$app->user->identity;
$controller = $this->context;
$social = Yii::$app->getModule('social');
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<div id="preloader">
    <div id="status">&nbsp;</div>
</div>

<a class="scrollToTop" href="#"><i class="fa fa-angle-up"></i></a>
<div class="container">
    <header id="header">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="header_top">
                    <div class="header_top_left">
                        <ul class="top_nav">
                            <?php if(Yii::$app->user->isGuest): ?>
                                <?php $callback = Url::to(['/site/fb-login'],true); ?>
                                <li><?= $social->getFbLoginLink($callback,['email']); ?></li>
                            <?php else: ?>
                                <li><a href="<?= Url::to(['/site/logout']); ?>">Выход</a></li>
                                <li><a href="#">Личный кабинет</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="header_top_right">
                        <p><?= date('Y-m-d H:i', time()); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="header_bottom">
                    <div class="logo_area"><a href="/" class="logo"><img src="<?= Url::to('@web/img/logo.jpg'); ?>" alt=""></a></div>
                    <div class="add_banner">
                        <?= BannersWidget::widget(['position' => 'HEAD']); ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <?= MainMenuWidget::widget(); ?>

<!--    <section id="newsSection">-->
<!--        <div class="row">-->
<!--            <div class="col-lg-12 col-md-12">-->
<!--                <div class="latest_newsarea">-->
<!--                    <span>Latest News</span>-->
<!--                    <ul id="ticker01" class="news_sticker">-->
<!--                        <li><a href="#"><img src="--><?//= Url::to('@web/img/news_thumbnail3.jpg'); ?><!--" alt="">My First News Item</a></li>-->
<!--                    </ul>-->
<!--                    <div class="social_area">-->
<!--                        <ul class="social_nav">-->
<!--                            <li class="facebook"><a href="#"></a></li>-->
<!--                        </ul>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </section>-->

    <?= $content; ?>

    <footer id="footer">
        <div class="footer_top">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="footer_widget wow fadeInRightBig">
                        <h2>Контакты</h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
                        <address>
                            Perfect News,1238 S . 123 St.Suite 25 Town City 3333,USA Phone: 123-326-789 Fax: 123-546-567
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer_bottom">
            <p class="copyright">&copy; "Единая сеть РА" <?= date('Y') ?></p>
            <p class="developer">Alex Nem (design by Wpfreeware)</p>
        </div>
    </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>