<?php

use app\widgets\MostCommentedWidget;
use app\widgets\LatestPostsWidget;
use app\widgets\SearchFormWidget;
use app\widgets\BannersWidget;
use app\helpers\Help;
use yii\helpers\ArrayHelper;
use app\widgets\RelatedPostsWidget;

/* @var $this yii\web\View */
/* @var $controller \app\controllers\PostsController */
/* @var $user \app\models\User */
/* @var $post \app\models\Post */
/* @var $comments \app\models\Comment[] */
/* @var $pages \yii\data\Pagination */

$this->title = $post->trl->name;
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
                            <img class="img-center" src="<?= $post->postImages[0]->getCroppedUrl(); ?>" alt="">
                        <?php endif; ?>

                        <?php if(!empty($post->trl->small_text)): ?>
                            <blockquote><?= $post->trl->small_text; ?></blockquote>
                        <?php endif; ?>

                        <?= !empty($post->trl->text) ? $post->trl->text : ''; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h2>Комментарии <i class="fa fa-comments"></i></h2>
                            <section class="comment-list">
                                <!-- First Comment -->
                                <?php foreach($comments as $comment): ?>
                                    <article class="row">
                                        <div class="col-md-2 col-sm-2 <?php if(!empty($comment->parent)): ?> col-md-offset-1 <?php endif; ?> hidden-xs">
                                            <figure class="thumbnail">
                                                <img class="img-responsive" src="<?= $comment->author->getAvatar(); ?>" />
                                                <figcaption class="text-center"><?= $comment->author->name.' '.$comment->author->surname; ?></figcaption>
                                            </figure>
                                        </div>
                                        <?php $size = !empty($comment->parent) ? '9' : '10';  ?>
                                        <div class="col-md-<?= $size; ?> col-sm-<?= $size; ?>">
                                            <div class="panel panel-default arrow left">
                                                <div class="panel-body">
                                                    <header class="text-left">
                                                        <div class="comment-user"><i class="fa fa-user"></i> <?= $comment->author->name.''.$comment->author->surname; ?></div>
                                                        <time class="comment-date" datetime="<?= $comment->created_at; ?>"><i class="fa fa-clock-o"></i> <?= substr($comment->created_at,0,16); ?></time>
                                                    </header>
                                                    <div class="comment-post">
                                                        <p>
                                                            <?= $comment->text; ?>
                                                        </p>
                                                    </div>
                                                    <?php if(empty($comment->parent)): ?>
                                                        <p class="text-right"><a href="<?= Url::to(['/posts/add-comment','cid' => $comment->id]); ?>" data-toggle="modal" data-target=".modal" class="btn btn-default btn-sm"><i class="fa fa-reply"></i> ответить</a></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </section>

                            <div class="row clearfix">
                                <div class="col-md-9 col-sm-12">
                                    <?= \yii\widgets\LinkPager::widget(['pagination' => $pages]); ?>
                                </div>
                                <div class="col-md-3 col-sm-12">
                                    <a href="<?= Url::to(['/posts/add-comment','pid' => $post->id]); ?>" data-toggle="modal" data-target=".modal" class="btn btn-default pull-right" style="margin: 20px 0;"><i class="fa fa-comment"></i> добавть</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if(!empty($post->categories[0]->posts) && count($post->categories[0]->posts) > 1): ?>
                        <?php /* @var $related \app\models\Post[] */ ?>
                        <?php $related = array_slice($post->categories[0]->posts,0,3); ?>
                        <div class="related_post">

                            <h2>Возможно вас заинтересует <i class="fa fa-thumbs-o-up"></i></h2>

                            <?= RelatedPostsWidget::widget(['post' => $post]); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4">
            <aside class="right_content">
                <?= SearchFormWidget::widget(); ?>
                <?= LatestPostsWidget::widget(); ?>
                <div class="single_sidebar wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">
                    <h2><span>Баннеры</span></h2>
                    <?= BannersWidget::widget(['position' => 'RIGHT', 'attributes' => ['class' => 'sideAdd']]); ?>
                </div>
            </aside>
        </div>
    </div>
</section>