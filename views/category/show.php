<?php

use app\widgets\MostCommentedWidget;
use app\widgets\LatestPostsWidget;
use app\widgets\BannersWidget;
use app\helpers\Help;
use yii\helpers\ArrayHelper;
use app\widgets\RelatedPostsWidget;

/* @var $this yii\web\View */
/* @var $controller \app\controllers\CategoryController */
/* @var $user \app\models\User */
/* @var $posts \app\models\Post[] */
/* @var $category \app\models\Category */
/* @var $pages \yii\data\Pagination */

$this->title = $category->trl->name;
$this->registerMetaTag(['name' => 'description', 'content' => $category->trl->meta_description]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $category->trl->meta_keywords]);
$controller = $this->context;
$user = Yii::$app->user->identity;

$this->registerCssFile('@web/frontend/css/comments.css');
use yii\helpers\Url;
?>

<section id="contentSection">
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8">
            <div class="left_content">
                <div class="single_page">

                    <ol class="breadcrumb">
                        <li><a href="<?= Url::to(['/site/index']); ?>">Главная</a></li>
                        <?php if(!empty($category)): ?>
                            <?php foreach($category->getBreadCrumbs(true) as $id => $name): ?>
                                <li><a href="<?= Url::to(['category/show','id' => $id, 'title' => Help::slug($name)]); ?>"><?= $name; ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>

                    <h1><?= $category->trl->name; ?></h1>

                    <?php if(!empty($category->children)): ?>
                        <div class="post_commentbox">
                            <?php foreach($category->children as $child): ?>
                                <?php $name = ArrayHelper::getValue($child->trl,'name',$child->name); ?>
                                <a href="<?= Url::to(['category/show','id' => $child->id, 'title' => Help::slug($name)]); ?>"><i class="fa fa-folder"></i><?= $child->trl->name; ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="post_commentbox">
                            <span>Нет подрубрик</span>
                        </div>
                    <?php endif; ?>

                    <div class="row">

                        <?php foreach($posts as $post): ?>
                            <div class="col-md-12 col-sm-12">
                                <ul class="business_catgnav wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown; border-bottom: 1px solid #ddd;">
                                    <li>
                                        <figure class="bsbig_fig" style="padding-bottom: 10px;">

                                            <figcaption>
                                                <a href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                            </figcaption>

                                            <div class="post_commentbox" style="margin: 0;padding: 0 0 5px 0;border: none;">
                                                <span><i class="fa fa-calendar"></i><?= substr($post->published_at,0,16); ?></span>
                                                <span><i class="fa fa-comment"></i><?= count($post->comments); ?></span>

                                                <?php if(!empty($post->author)): ?>
                                                    <a href="#"><i class="fa fa-user"></i><?= $post->author->name.' '.$post->author->surname; ?></a>
                                                <?php else: ?>
                                                    <span><i class="fa fa-user"></i><?= $post->author_custom_name; ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="row">
                                                <?php if(!empty($post->postImages)): ?>
                                                    <div class="col-md-4 col-sm-4">
                                                        <a href="<?= $post->getUrl(); ?>" class="featured_img">
                                                            <img class="img-responsive img-thumbnail" alt="" src="<?= $post->getThumbnailUrl(425,283); ?>">
                                                            <span class="overlay"></span>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php $col = !empty($post->postImages) ? 8 : 12; ?>
                                                <div class="col-md-<?= $col; ?> col-sm-<?= $col; ?>">
                                                    <p><?= $post->trl->small_text; ?></p>
                                                </div>
                                            </div>
                                        </figure>
                                    </li>
                                </ul>
                            </div>
                        <?php endforeach; ?>

                    </div>
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