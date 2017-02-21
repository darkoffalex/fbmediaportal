<?php

use app\assets\FrontendAsset;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \yii\web\User */
/* @var $controller \app\components\Controller */
/* @var $social kartik\social\Module */

FrontendAsset::register($this);

$user = Yii::$app->user->identity;
$controller = $this->context;
$social = Yii::$app->getModule('social');
?>

<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <!-- MADE WITH LOVE BY <> SERGEY KHMELEVSKOY </>-->
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= Html::encode($this->title) ?></title>
    <!--[if lt IE 9]>
    <script src="<?= Url::to('@web/frontend/components/html5shiv/dist/html5shiv.js') ?>"></script>
    <![endif]-->
    <?php $this->head(); ?>
</head>

<body>
<?php $this->beginBody(); ?>
<!-- BANNER DESKTOP::START-->
<section class="topBanner hidden-xs-down">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-xs-center">
                <?= $this->render('/common/_banners',['banners' => ArrayHelper::getValue($controller->banners,'TOP_BANNER')]); ?>
            </div>
        </div>
    </div>
</section>
<!-- HEADER-->
<header class="header">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 text-sm-center">
                <a href="<?= Url::to(['main/index']); ?>"><i class="ico ico-logo"></i></a>

                <form method="get" action="<?= Url::to(['main/search']); ?>" class="header__searchbar hidden-xs-down">
                    <input name="q" class="form-control" type="text">
                    <input type="submit" style="display: none">
                </form>

                <?php if(Yii::$app->user->isGuest): ?>
                    <?php $callback = Url::to(['/site/fb-login'],true); ?>
                    <?= $social->getFbLoginLink($callback,['class' => 'header__login'],['email']); ?>
                <?php else: ?>
                    <a class="header__login" href="<?= Url::to(['/site/logout']); ?>">
                        <i class="ico ico-login"></i><span>Выйти</span>
                    </a>
                    <a class="header__login" href="<?= Url::to(['/main/profile']); ?>">
                        <span>Профиль</span>
                    </a>
                <?php endif; ?>



                <div class="header__navi hidden-lg-up hidden-xs-down"><i class="ico ico-hamb"></i><span>Наши <br> рубрики</span>
                    <div class="header__navi__drop">
                        <?= $this->render('/common/_main_menu',[
                            'categories' => $controller->mainMenu,
                            'active' => !empty($category) ? $category->id : null
                        ]); ?>
                    </div>
                </div>

                <div class="header__subMobile hidden-sm-up">
                    <form class="header__searchbar" method="get" action="<?= Url::to(['main/search']); ?>">
                        <input name="q" class="form-control" type="text">
                        <input type="submit" style="display: none">
                    </form>
                    <div class="header__navi"><span>Наши рубрики</span><i class="ico ico-hamb"></i>
                        <div class="header__navi__drop">
                            <?= $this->render('/common/_main_menu',[
                                'categories' => $controller->mainMenu,
                                'active' => !empty($category) ? $category->id : null
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<?= $content; ?>
<!-- FOOTER::START-->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2"></div>
            <div class="col-md-12 col-lg-10">
                <div class="row">
                    <div class="col-sm-8">
                        <div class="footer__navi"><a href="#">О проекте</a><a href="#">Реклама</a><a href="#">Спецпроекты</a><a href="#">iOS и Android</a></div>
                        <div class="footer__navi"><a href="#">secret@vc.ru</a><a href="#">Карта сайта</a><a href="#">Вакансии</a><a href="#">ИД «Комитет»</a></div>
                    </div>
                    <div class="col-sm-4 text-sm-right"><a class="footer__alert" href="#"><i class="ico ico-alert"></i><span>Включить уведомления</span></a>
                        <div class="footer__socials"><a href="#"><i class="ico ico-gplus"></i></a><a href="#"><i class="ico ico-twitter"></i></a><a href="#"><i class="ico ico-vk"></i></a><a href="#"><i class="ico ico-fb"></i></a><a href="#"><i class="ico ico-rss"></i></a><a href="#"><i class="ico ico-ok"></i></a></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-xs-center">
                        <div class="footer__copy">© RusTurkey. Копирайт 2016. Все права защишены</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- SCRIPTS-->
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>