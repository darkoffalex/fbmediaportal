<?php

use app\widgets\MostCommentedWidget;
use app\widgets\LatestPostsWidget;
use app\widgets\BannersWidget;
use app\widgets\SearchFormWidget;
use app\helpers\Help;
use yii\helpers\ArrayHelper;
use app\widgets\RelatedPostsWidget;

/* @var $this yii\web\View */
/* @var $controller \app\controllers\SearchController */
/* @var $user \app\models\User */
/* @var $posts \app\models\Post[] */
/* @var $pages \yii\data\Pagination */

$this->title = $category->trl->name;
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
                        <li><a href="">Поиск</a></li>
                    </ol>

                    <h1>Результаты поиска</h1>
                    <?php if(!empty($posts)): ?>
                        <br>
                        <span>Всего найдено : <?= $pages->totalCount; ?></span>
                    <?php endif; ?>


                    <div class="row">
                        <?php if(!empty($posts)): ?>
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

                                                    <?php foreach($post->categories as $c): ?>
                                                        <?php $name = ArrayHelper::getValue($c->trl,'name',$c->name); ?>
                                                        <a href="<?= Url::to(['category/show','id' => $c->id, 'title' => Help::slug($name)]); ?>"><i class="fa fa-folder"></i><?= $c->trl->name; ?></a>
                                                    <?php endforeach; ?>
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
                        <?php else: ?>
                            <div class="col-md-12 col-sm-12">
                                <p>Нет материалов</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($pages)): ?>
                        <div class="row">
                            <?= \yii\widgets\LinkPager::widget(['pagination' => $pages]); ?>
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