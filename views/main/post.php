<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\helpers\Constants;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $controller \app\controllers\MainController */
/* @var $post \app\models\Post */
/* @var $carouselPosts \app\models\Post[] */

$user = Yii::$app->user->identity;
$controller = $this->context;
$this->registerJs('$(document).ready(function(){$(".click-auth").click(function(){$("#auth-link")[0].click();return false;})});',\yii\web\View::POS_END);
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

                        <h1 class="content__card__title">
                            <?= $post->trl->name; ?>
                        </h1>

                        <div class="content__card__crumbs">
                            <a  href="<?= Url::to(['main/index']); ?>">Главная</a><span class="crumb-seprator">-</span><?php if(!empty($post->categories[0])): ?><?php $crumbs = $post->categories[0]->getBreadCrumbs(true); ?><?php foreach ($crumbs as $cid => $name): ?><a  href="<?= Url::to(['main/category', 'id' => $cid, 'title' => \app\helpers\Help::slug($name)]); ?>"><?= $name; ?></a><span class="crumb-seprator">-</span><?php endforeach; ?><?php endif; ?><span class="current"><?= $post->trl->name; ?></span>
                        </div>

                        <div class="share-box clearfix">
                            <div class="content__card__share">
                                <span>Поделиться</span>
                            </div>
                            <script async src="https://usocial.pro/usocial/usocial.js?v=6.1.1" data-script="usocial" charset="utf-8"></script>
                            <div class="uSocial-Share" data-pid="4ef9f797785c35e48331c7832aa0d5eb" data-type="share" data-options="round-rect,style1,absolute,horizontal,upArrow-left,size24,eachCounter0,counter1,counter-after,nomobile" data-social="fb,vk,ok,bookmarks" data-mobile="vi,wa,telegram,sms"></div><!-- /uSocial -->
                        </div>

                        <?php if($post->content_type_id == Constants::CONTENT_TYPE_VIDEO && (!empty($post->video_key_fb) || !empty($post->video_key_yt))): ?>
                            <?php if(!empty($post->video_key_fb)): ?>
                                <?php $poster = !empty($post->postImages[0]) ? $post->getFirstImageUrlEx(706,311) : null; ?>
                                <video class="afterglow" <?php if(!empty($poster)): ?>poster="<?= $poster; ?>"<?php endif; ?> id="fb_vid_<?= $post->video_attachment_id_fb; ?>" width="665" height="294" src="<?= $post->video_key_fb; ?>"></video>
                            <?php endif; ?>
                            <?php if(!empty($post->video_key_yt)): ?>
                                <?php $poster = !empty($post->postImages[0]) ? $post->getFirstImageUrlEx(706,311) : null; ?>
                                <video class="afterglow" <?php if(!empty($poster)): ?>poster="<?= $poster; ?>"<?php endif; ?> data-youtube-id="<?= Help::youtubeid($post->video_key_yt); ?>" width="665" height="294"></video>
<!--                                <iframe width="100%" height="311px" src="--><?//= Help::youtubeurl($post->video_key_yt); ?><!--" frameborder="0" allowfullscreen></iframe>-->
                            <?php endif; ?>
                        <?php else: ?>
                            <div>
                                <?php $titleAlt = !empty($post->postImages[0]->trl) ? $post->postImages[0]->trl->name : ''; ?>
                                <img title="<?= $titleAlt ?>" alt="<?= $titleAlt; ?>" class="img-fluid" src="<?= $post->getFirstImageUrlEx(706,311); ?>">
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($post->postImages[0]->trl->signature)): ?>
                            <div class="content__card__copy"><?= $post->postImages[0]->trl->signature; ?></div>
                        <?php endif; ?>

                        <div class="content__card__pageContent">
                            <?= $post->trl->text; ?>
                        </div>

                        <div class="content__card__info">
                            <?php if(!empty($post->author)): ?>
                                <a  href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                    <?= $post->author->name.' '.$post->author->surname; ?>
                                </a>
                            <?php else: ?>
                                <a href=""><?= $post->author_custom_name; ?></a>
                            <?php endif; ?>
                            <span>• <?= Help::datefmt($post->delayed_at); ?></span>
                        </div>

                        <div class="share-box clearfix">
                            <div class="content__card__share">
                                <span>Поделиться</span>
                            </div>
                            <script async src="https://usocial.pro/usocial/usocial.js?v=6.1.1" data-script="usocial" charset="utf-8"></script>
                            <div class="uSocial-Share" data-pid="4ef9f797785c35e48331c7832aa0d5eb" data-type="share" data-options="round-rect,style1,absolute,horizontal,upArrow-left,size24,eachCounter0,counter1,counter-after,nomobile" data-social="fb,vk,ok,bookmarks" data-mobile="vi,wa,telegram,sms"></div><!-- /uSocial -->
                        </div>

                        <?= $this->renderDynamic('return \app\widgets\NonCacheComments::widget();'); ?>
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

<?php foreach ($carouselPosts as $index => $p): ?>
    <?php if($p->id == $post->id): ?>
        <?php unset($carouselPosts[$index]); ?>
    <?php endif; ?>
<?php endforeach; ?>

<?= $this->render('/common/_forum_posts',['posts' => array_slice($carouselPosts,0,4), 'label' => 'Похожие материалы']); ?>
