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
                        <p>RusTurkey.com – уникальная, крупнейшая информационно-дискуссионная площадка о Турции для русскоязычных пользователей интернета, содержащая исключительно полезные, актуальные и обсуждаемые темы и вопросы, посвященные Турции, жизни в этой стране, адаптации и многому другому.</p>
                        <p>Ежедневно мы публикуем более 50 авторских материалов и обсуждений по темам, имеющих повышенную актуальность на текущий день. Каждый материал дает максимум исчерпывающей информации по касающемуся вопросу благодаря активным обсуждениям, в которых принимают участие люди, проживающие на территории Турции и способные дать информацию “из первых рук”.</p>
                        <p>Материалы RusTurkey.com будут полезны как для посещающих Турцию в качестве туристов, так и для тех, кто рассматривает возможность или собирается переехать в Турцию на постоянное место жительство или уже сделал это: множество вопросов посвящено адаптации, которая станет гораздо проще, если прочитать советы тех, кто уже прошел этот этап после переезда в Турцию.</p>
                        <p>Турция - уникальная и выгодная страна для посещения с целью медицинского туризма. Множество публикаций посвящено именно этой теме и помогут вам составить идеальный план путешествия с этой целью.</p>
                        <p>RusTurkey.com также будет полезен и интересен тем, кто рассматривает организацию бизнеса в Турции или расширение существующего бизнеса на территорию этой страны, хочет найти партнеров в различных областях – от розничной торговли до энергетики.</p>
                        <p>Портал тесно сотрудничает с крупнейшими турецкими русскоязычными сообществами в сети интернет, в частности с самой популярной и большой по численности локальной русскоязычной группой в Facebook - “Русские в Анталии”. В основу материалов портала ложатся советы и опыт людей, успевших столкнуться с какими-либо вопросами и успешно решившими их. Поэтому в материалах портала можно найти ответы на вопросы по любым темам – начиная от <strong>правил ввоза автомобилей и заканчивая тем, как можно объяснить свое нежелание соблюдать некоторые семейные традиции, принятые в Турции, чтобы о вас не подумали ничего дурного.</strong></p>
                        <p>Посвятив время изучению материалов RusTurkey.com и комментариев к ним, вы сможете найти не только ответы на вопросы, но и прочитать отзывы и получить нужные и полезные рекомендации по любым интересующим вас вопросам. Например, о том, какой спортивный зал лучше выбрать для занятий определенным типом аэробики, в какую школу отправить учиться вашего ребенка и целесообразно ли приобретение максимального пакета медицинской страховки.</p>
                        <p>Зарегистрировавшись на портале всего в два клика с помощью социальной сети Facebook, вы сможете задать уточняющие вопросы, а все те, кто принимал участие в обсуждении заданного вами вопроса, мгновенно получат уведомление об этом и многие в кратчайшие сроки постараются ответить вам.</p>
                        <p>Полезная информация, интересные дискуссии, глубина колорита жизни в Турции: все эти темы откроются для Вас на страницах портала RusTurkey.com.</p>
                        <p>Если Вы хотите предложить интересный материал или заметили в одном из материалов неточность*, пишите в редакцию: <a href="mailto:editors@rusturkey.com">editors@rusturkey.com</a></p>
                        <p style="font-style: italic">* - Обратите внимание: многие материалы строятся на вопросах и ответах пользователей Интернета, в формате дискуссий, поэтому в случае, если вы считаете, что кто-либо из участников обсуждения владеет неверной или неактуальной информацией, вы можете сообщать об этом непосредственно в обсуждении. Редакция портала не несет ответственности за содержание публикаций пользователей, импортируемых в полуавтоматическим режиме из сторонних источников. Мнение сотрудников портала не всегда совпадает с мнением инициаторов обсуждений и принимающих в этом обсуждении участие лиц.</p>
                        <p>По вопросам размещения рекламы: adv@rusturkey.com
                        </p>По остальным вопросам: info@rusturkey.com
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
