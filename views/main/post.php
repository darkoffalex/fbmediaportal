<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $controller \app\controllers\MainController */
/* @var $post \app\models\Post */
/* @var $carouselPosts \app\models\Post[] */
/* @var $comments \app\models\Comment[] */

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
        <div id="owlTop" data-current-page="1" data-loading="<?= Url::to(['main/category-ajax','carousel' => 1,'id' => !empty($post->categories[0]) ? $post->categories[0]->id : null]); ?>">
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
                        'active' => !empty($post->categories[0]) ? $post->categories[0]->id : null
                    ]); ?>
                </div>

                <div class="col-sm-8 col-lg-7 no-pad-r">
                    <!-- cards-->
                    <div class="content__card content__card--inner content__card--wide">
                        <div class="content__card__title"><?= $post->trl->name; ?></div>
                        <div class="content__card__info">
                            <?php if(!empty($post->author)): ?>
                                <a rel="canonical" href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                    <?= $post->author->name.' '.$post->author->surname; ?>
                                </a>
                            <?php else: ?>
                                <a href=""><?= $post->author_custom_name; ?></a>
                            <?php endif; ?>
                            <span>• <?= substr($post->published_at,0,16); ?></span>
                        </div>

                        <div class="content__card__crumbs">
                            <a href="<?= Url::to(['main/index']); ?>">Главная</a><span class="crumb-seprator">-</span><?php if(!empty($post->categories[0])): ?><?php $crumbs = $post->categories[0]->getBreadCrumbs(true); ?><?php foreach ($crumbs as $cid => $name): ?><a href="<?= Url::to(['main/category', 'id' => $cid, 'title' => \app\helpers\Help::slug($name)]); ?>"><?= $name; ?></a><span class="crumb-seprator">-</span><?php endforeach; ?><?php endif; ?><span class="current"><?= $post->trl->name; ?></span>
                        </div>

                        <div class="content__card__share">
                            <span>Поделиться</span>
                        </div>

                        <div>
                            <img class="img-fluid" src="<?= $post->getFirstImageUrlEx(706,311); ?>">
                        </div>

                        <div class="content__card__pageContent">
                            <?= $post->trl->text; ?>
                        </div>

                        <div class="content__card__share">
                            <span>Поделиться</span>
                        </div>


                        <!-- Comment section-->
                        <div class="contentComments" data-current-page="1" data-postload="<?= Url::to(['main/comments-ajax', 'id' => $post->id]); ?>">
                            <h2>Комментарии</h2>

                            <?php if(!Yii::$app->user->isGuest): ?>
                            <form method="post" class="contentComments__post">
                                <img class="img-fluid" src="<?= $user->getAvatar(); ?>">
                                <div class="form-group">
                                    <label for="comment">Сообщение</label>
                                    <?= Html::hiddenInput(Yii::$app->getRequest()->csrfParam,Yii::$app->getRequest()->getCsrfToken()); ?>
                                    <textarea name="Comment[text]" id="comment" class="form-control"></textarea>
                                </div>
                                <button data-no-empty=".contentComments__post" class="btn" type="submit">Отправить</button>
                            </form>
                            <?php endif; ?>

                            <?= $this->render('_load_comments',['comments' => $comments]); ?>
                        </div>
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

<?php foreach ($carouselPosts as $index => $p): ?>
    <?php if($p->id == $post->id): ?>
        <?php unset($carouselPosts[$index]); ?>
    <?php endif; ?>
<?php endforeach; ?>

<?= $this->render('/common/_forum_posts',['posts' => array_slice($carouselPosts,0,4), 'label' => 'Похожие материалы']); ?>