<?php

use app\assets\FrontendAsset;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Facebook\Facebook;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $user \yii\web\User */
/* @var $controller \app\components\Controller */

FrontendAsset::register($this);

if(!session_id()) {
    session_start();
}

$user = Yii::$app->user->identity;
$controller = $this->context;
$fb = new Facebook([
    'app_id' => Yii::$app->params['facebook']['app_id'],
    'app_secret' => Yii::$app->params['facebook']['app_secret'],
]);
?>

<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <!-- MADE WITH LOVE BY <> SERGEY KHMELEVSKOY </>-->
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<!--    <meta http-equiv="expires" content="--><?//= date(DATE_RFC850,(time()+86400)) ?><!--">-->

    <title><?= Html::encode($this->title) ?></title>

    <!--[if lt IE 9]>
    <script src="<?= Url::to('@web/frontend/components/html5shiv/dist/html5shiv.js') ?>"></script>
    <![endif]-->

<!--    <meta http-equiv="cache-control" content="private">-->
<!--    <meta http-equiv="cache-control" content="max-age=3600, must-revalidate">-->

    <link rel="canonical" href="<?= Help::canonical(); ?>"/>
    <link rel="shortcut icon" href="<?= Url::to('@web/favicon.ico'); ?>" type="image/x-icon">
    <?php $this->head(); ?>

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-96926007-1', 'auto');
        ga('send', 'pageview');

    </script>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (d, w, c) {
            (w[c] = w[c] || []).push(function() {
                try {
                    w.yaCounter44021489 = new Ya.Metrika({
                        id:44021489,
                        clickmap:true,
                        trackLinks:true,
                        accurateTrackBounce:true,
                        webvisor:true
                    });
                } catch(e) { }
            });

            var n = d.getElementsByTagName("script")[0],
                s = d.createElement("script"),
                f = function () { n.parentNode.insertBefore(s, n); };
            s.type = "text/javascript";
            s.async = true;
            s.src = "https://mc.yandex.ru/metrika/watch.js";

            if (w.opera == "[object Opera]") {
                d.addEventListener("DOMContentLoaded", f, false);
            } else { f(); }
        })(document, window, "yandex_metrika_callbacks");
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/44021489" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
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
                <?php $id = Yii::$app->request->get('id',null); ?>
                <?php $actionId = Yii::$app->controller->action->id; ?>

                <?php if(!empty($id) || in_array($actionId,['profile','all','profile-details','search','pages'])):?>
                    <a href="<?= Url::to(['main/index']); ?>"><i class="ico ico-logo"></i></a>
                <?php else: ?>
                    <i class="ico ico-logo"></i>
                <?php endif; ?>


                <form method="get" action="<?= Url::to(['main/search']); ?>" class="header__searchbar hidden-xs-down">
                    <input name="q" class="form-control" type="text">
                    <input type="submit" style="display: none">
                </form>

                <?php if(Yii::$app->user->isGuest): ?>

                    <?php $callback = Url::to(['/site/fb-login'],true); ?>

                    <?php $loginUrl = $fb->getRedirectLoginHelper()->getLoginUrl($callback, [
                        'email',
                        'user_posts',
                        'publish_actions',
                        'manage_pages',
                        'publish_pages',
                        'user_managed_groups'
                    ]); ?>

                    <a id="auth-link" class="header__login" href="<?= $loginUrl;?>">
                        <i class="ico ico-login"></i><span>Войти</span>
                    </a>

                <?php else: ?>
                    <a class="header__login" href="<?= Url::to(['/site/logout']); ?>">
                        <i class="ico ico-login"></i><span>Выйти</span>
                    </a>
<!--                    <a class="header__login" href="--><?//= Url::to(['/main/profile']); ?><!--">-->
<!--                        <span>Профиль</span>-->
<!--                    </a>-->
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
                        <div class="footer__navi">
                            <a href="<?= Url::to(['/main/pages','type' => 'about']); ?>">О проекте</a>
                            <a href="<?= Url::to(['/main/pages','type' => 'agreement']); ?>">Пользовательское соглашение</a>
                        </div>
                        <div class="footer__navi">
                            <a href="<?= Url::to(['/main/pages','type' => 'widgets']) ?>">Полезные виджеты</a>
                            <a href="mailto:<?= Yii::$app->params['adminEmail']; ?>"><?= Yii::$app->params['adminEmail']; ?></a>
                        </div>
<!--                        <div class="footer__navi">-->
<!--                            <a href="#">secret@vc.ru</a>-->
<!--                            <a href="#">Карта сайта</a>-->
<!--                            <a href="#">Вакансии</a-->
<!--                            ><a href="#">ИД «Комитет»</a>-->
<!--                        </div>-->
                    </div>
                    <div class="col-sm-4 text-sm-right">
<!--                        <a class="footer__alert" href="#">-->
<!--                            <i class="ico ico-alert"></i><span>Включить уведомления</span>-->
<!--                        </a>-->
                        <div class="footer__socials">
<!--                            <a href="#"><i class="ico ico-gplus"></i></a>-->
<!--                            <a href="#"><i class="ico ico-twitter"></i></a>-->
<!--                            <a href="#"><i class="ico ico-vk"></i></a>-->
                            <a target="_blank" href="https://www.facebook.com/RusTurkey-1217534681658311/?fref=ts"><i class="ico ico-fb"></i></a>
<!--                            <a href="#"><i class="ico ico-rss"></i></a>-->
<!--                            <a href="#"><i class="ico ico-ok"></i></a>-->
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-xs-center">
                        <div class="footer__copy">© RusTurkey.com, <?= date('Y'); ?>. Все права защищены</div>
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