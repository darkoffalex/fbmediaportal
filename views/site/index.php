<?php

use app\widgets\MostCommentedWidget;
use app\widgets\LatestPostsWidget;
use app\widgets\BannersWidget;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $controller \app\controllers\SiteController */
/* @var $user \app\models\User */
/* @var $stickyPosts \app\models\Post[] */
/* @var $categories \app\models\Category[] */

$this->title = 'Главная';
$controller = $this->context;
$user = Yii::$app->user->identity;

use yii\helpers\Url;
?>

<section id="sliderSection">
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8">
            <?php if(!empty($stickyPosts)): ?>
                <div class="slick_slider">
                    <?php foreach($stickyPosts as $post): ?>
                        <div class="single_iteam">
                            <?php $slugTitle = ArrayHelper::getValue($post->trl,'name',$post->name); ?>
                            <?php $url = Url::to(['posts/show', 'id' => $post->id, 'title' => Help::slug($slugTitle)]); ?>
                            <a href="<?= $url; ?>">
                                <img src="<?= $post->getThumbnailUrl(660,502); ?>" alt="">
                            </a>
                            <div class="slider_article">
                                <h2><a class="slider_tittle" href="<?= $url; ?>"><?= ArrayHelper::getValue($post->trl,'name') ?></a></h2>
                                <?php if(!empty($post->trl->small_text)): ?>
                                    <p><?= $post->trl->small_text; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4">
            <?= MostCommentedWidget::widget(); ?>
        </div>
    </div>
</section>

<section id="contentSection">
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8">
            <div class="left_content">

                <div class="row clearfix">
                    <?php $count = 0; ?>
                    <?php if(!empty($categories)): ?>
                        <?php foreach($categories as $category): ?>
                            <div class="col-md-<?= $count > 0 ? '6' : '12'; ?> col-sm-12">
                                <div class="single_post_content row clearfix">
                                    <h2><span><?= $category->trl->name; ?></span></h2>

                                    <?php $posts = $category->getPostsRecursive(true); ?>

                                    <?php if(!empty($posts)): ?>
                                        <?php /* @var $first \app\models\Post */ ?>
                                        <?php /* @var $sidePosts \app\models\Post[] */ ?>
                                        <?php $first = array_slice($posts,0,1)[0]; ?>
                                        <?php $sidePosts = array_slice($posts,1,3); ?>


                                        <div class="col-md-<?= $count == 0 ? '6' : '12'; ?> col-sm-12">
                                            <ul class="business_catgnav  wow fadeInDown">
                                                <li>
                                                    <figure class="bsbig_fig">
                                                        <a href="<?= $first->getUrl(); ?>" class="featured_img">
                                                            <img alt="" src="<?= $first->getThumbnailUrl(425,283); ?>">
                                                            <span class="overlay"></span>
                                                        </a>
                                                        <figcaption>
                                                            <a href="<?= $first->getUrl(); ?>"><?= $first->trl->name; ?></a>
                                                        </figcaption>
                                                        <?php if(!empty($first->trl->small_text)): ?>
                                                            <p><?= $first->trl->small_text; ?></p>
                                                        <?php endif; ?>
                                                    </figure>
                                                </li>
                                            </ul>
                                        </div>


                                        <?php if(!empty($sidePosts)): ?>
                                            <div class="col-md-<?= $count == 0 ? '6' : '12'; ?> col-sm-12">
                                                <ul class="spost_nav">
                                                    <?php foreach($sidePosts as $post): ?>
                                                        <li>
                                                            <div class="media wow fadeInDown">
                                                                <a href="<?= $post->getUrl(); ?>" class="media-left">
                                                                    <img alt="" src="<?= $post->getThumbnailUrl(90,70); ?>">
                                                                </a>
                                                                <div class="media-body">
                                                                    <a href="<?= $post->getUrl(); ?>" class="catg_title"><?= $post->trl->name; ?></a>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php $count = $count > 1 ? 0 : $count + 1; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>


            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <aside class="right_content">
                <?= LatestPostsWidget::widget(); ?>

                <div class="single_sidebar wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">
                    <h2><span>Баннеры</span></h2>
                    <?= BannersWidget::widget(['position' => 'RIGHT', 'attributes' => ['class' => 'sideAdd']]); ?>
                </div>
            </aside>
        </div>
    </div>
</section>
