<?php

use app\widgets\MostCommentedWidget;
use app\widgets\LatestPostsWidget;
use app\widgets\BannersWidget;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $controller \app\controllers\PostsController */
/* @var $user \app\models\User */
/* @var $post \app\models\Post */

$this->title = $post->trl->name;
$controller = $this->context;
$user = Yii::$app->user->identity;

use yii\helpers\Url;
?>

<section id="contentSection">
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8">
            <div class="left_content">
                <div class="single_page">

                    <ol class="breadcrumb">
                        <li><a href="<?= Url::to(['/site/index']); ?>">Главная</a></li>
                        <?php $category = !empty($post->categories) ? $post->categories[0] : null; ?>
                        <?php if(!empty($category)): ?>
                            <?php foreach($category->getBreadCrumbs(true) as $id => $name): ?>
                                <li><a href="<?= Url::to(['category/show','id' => $id, 'title' => Help::slug($name)]); ?>"><?= $name; ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>

                    <h1><?= $post->trl->name; ?></h1>
                    <div class="post_commentbox">
                        <?php foreach($post->categories as $category): ?>
                            <?php $name = ArrayHelper::getValue($category->trl,'name',$category->name); ?>
                            <a href="<?= Url::to(['category/show','id' => $category->id, 'title' => Help::slug($name)]); ?>"><i class="fa fa-tags"></i><?= $category->trl->name; ?></a>
                        <?php endforeach; ?>
                        <span><i class="fa fa-calendar"></i><?= $post->published_at; ?></span>
                    </div>
                    <div class="single_page_content">
                        <?php if(!empty($post->postImages)): ?>
                            <img class="img-center" src="<?= $post->getFirstImageUrl(); ?>" alt="">
                        <?php endif; ?>

                        <?php if(!empty($post->trl->small_text)): ?>
                            <blockquote><?= $post->trl->small_text; ?></blockquote>
                        <?php endif; ?>

                        <?= !empty($post->trl->text) ? $post->trl->text : ''; ?>
                    </div>

                    <div class="social_link">

                    </div>

                    <?php if(!empty($post->categories[0]->posts) && count($post->categories[0]->posts) > 1): ?>
                        <?php /* @var $related \app\models\Post[] */ ?>
                        <?php $related = array_slice($post->categories[0]->posts,0,3); ?>
                        <div class="related_post">

                            <h2>Возможно вас заинтересует <i class="fa fa-thumbs-o-up"></i></h2>

                            <ul class="spost_nav wow fadeInDown animated animated" style="visibility: visible; animation-name: fadeInDown;">

                                <?php foreach($related as $rp): ?>
                                    <?php if($rp->id == $post->id): continue; endif; ?>
                                    <li>
                                        <div class="media">
                                            <a class="media-left" href="<?= $rp->getUrl(); ?>">
                                                <img src="<?= $rp->getThumbnailUrl(); ?>" alt="">
                                            </a>
                                            <div class="media-body">
                                                <a class="catg_title" href="<?= $rp->getUrl(); ?>"><?= $rp->trl->name; ?></a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
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