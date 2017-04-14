<?php
use app\models\Post;
use app\helpers\Constants;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\helpers\ArrayHelper;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $user \app\models\User; */
/* @var $controller \app\controllers\MainController */
/* @var $posts \app\models\Post[] */
/* @var $comments \app\models\Comment[] */
/* @var $carouselPosts \app\models\Post[] */
/* @var $pages \yii\data\Pagination */
/* @var $type string */
/* @var $materialCount int */
/* @var $postCount int */

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
<section class="content separatorCards">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2">
                <?= $this->render('/common/_main_menu',[
                    'categories' => $controller->mainMenu,
                    'active' => null
                ]); ?>
            </div>

            <div class="col-sm-8 col-lg-7 no-pad-r">
                <div class="innerWrapper">
                    <div class="headingBorder">
                        <h2>Записи участника: <b><?= $user->name.' '.$user->surname; ?></b></h2>
                    </div>
                    <div class="profileControl">
                        <a href="<?= Url::to(['main/profile','id' => $user->id]); ?>">Вернуться к профилю</a><b> Показать </b>
                        <select name="type" data-reloading-select="<?= Url::to(['main/profile-details','id' => $user->id]); ?>" class="ui-select">
                            <option <?= $type == 'posts' ? 'selected' : ''; ?> value="posts">Посты</option>
                            <option <?= $type == 'materials' ? 'selected' : ''; ?> value="materials">Материалы</option>
                            <option <?= $type == 'comments' ? 'selected' : ''; ?> value="comments">Комментарии</option>
                        </select>
                    </div>

                    <?php if(!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <!-- card-->
                            <div class="content__card">
                                <div class="content__card__image">
                                    <a href="<?= $post->getUrl(); ?>">
                                        <img class="img-fluid" src="<?= $post->getThumbnailUrl(484,276); ?>">
                                    </a>
                                </div>
                                <a class="content__card__title hidden-sm-up" href="#"><?= $post->trl->name; ?></a>
                                <div class="content__card__content"><a class="content__card__title hidden-xs-down" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                    <div class="content__card__intro">
                                        <p><?= $post->trl->small_text; ?></p>
                                        <?php if(!empty($post->author)): ?>
                                            <a href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                                <?= $post->author->name.' '.$post->author->surname; ?>
                                            </a>
                                        <?php else: ?>
                                            <a href=""><?= $post->author_custom_name; ?></a>
                                        <?php endif; ?>
                                        <span> • <?= Help::datefmt($post->delayed_at); ?></span>

                                        <?php if($post->comment_count > 0): ?>
                                            <div class="content__card__comments">
                                                <span><?= $post->comment_count; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($type != 'comments'): ?>
                        <div class="content__card">
                            <p>Ничего не найдено</p>
                        </div>
                    <?php endif;?>

                    <?php if(!empty($comments)): ?>
                        <div class="content__card content__card--wide">
                            <div class="contentComments">
                                <?= $this->render('_load_comments',['comments' => $comments, 'viewOnly' => true, 'timeLine' => true]); ?>
                            </div>
                        </div>
                    <?php elseif ($type == 'comments'): ?>
                        <div class="content__card content__card--wide">
                            <div class="contentComments">
                                <p>Ничего не найдено</p>
                            </div>
                        </div>
                    <?php endif;?>

                    <?= LinkPager::widget([
                        'pagination' => $pages,
                        'nextPageLabel' => '<i class="ico ico-paginate-next"></i>',
                        'nextPageCssClass' => 'last',
                        'prevPageLabel' => '<i class="ico ico-paginate-prev"></i>',
                        'prevPageCssClass' => 'last',
                        'maxButtonCount' => 5
                    ]); ?>
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
                            'banners' => ArrayHelper::getValue($controller->banners,'TOP_RIGHT_1')
                        ]); ?>
                    </div>

                    <div class="content__sidebar__banner">
                        <?= $this->render('/common/_banners',[
                            'imgAttributes' => ['class' => 'img-fluid'],
                            'banners' => ArrayHelper::getValue($controller->banners,'TOP_RIGHT_2')
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
