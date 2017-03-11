<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $controller \app\controllers\MainController */
/* @var $posts \app\models\Post[] */
/* @var $carouselPosts \app\models\Post[] */
/* @var $pages \yii\data\Pagination */
/* @var $type string */

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
                    <h1>Категория материалов</h1>

                    <div class="sorting">
                        <a  class="btn btn-outline <?= $type=='latest' ? 'active' : ''; ?>" href="<?= Url::to(['main/all','type' => 'latest']); ?>">
                            <span>Последнее</span>
                        </a>
                        <a  class="btn btn-outline <?= $type=='popular' ? 'active' : ''; ?>" href="<?= Url::to(['main/all','type' => 'popular']); ?>">
                            <span>Популярное</span>
                        </a>
                        <a  class="btn btn-outline <?= $type=='turkey' ? 'active' : ''; ?>" href="<?= Url::to(['main/all','type' => 'turkey']); ?>">
                            <span>Полезное о Турции</span>
                        </a>
                    </div>

                    <?php foreach ($posts as $post): ?>
                        <!-- card-->
                        <div class="content__card">
                            <div class="content__card__image">
                                <a  href="<?= $post->getUrl(); ?>">
                                    <img class="img-fluid" src="<?= $post->getThumbnailUrl(484,276); ?>">
                                </a>
                            </div>
                            <a  class="content__card__title hidden-sm-up" href="#"><?= $post->trl->name; ?></a>
                            <div class="content__card__content"><a  class="content__card__title hidden-xs-down" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                <div class="content__card__intro">
                                    <p><?= $post->trl->small_text; ?></p>
                                    <?php if(!empty($post->author)): ?>
                                        <a  href="<?= Url::to(['main/profile','id'=> $post->author_id]); ?>">
                                            <?= $post->author->name.' '.$post->author->surname; ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="#"><?= $post->author_custom_name; ?></a>
                                    <?php endif; ?>

                                    <span> • <?= substr($post->published_at,0,16); ?></span>

                                    <?php if($post->comment_count > 0): ?>
                                        <div class="content__card__comments" style="margin-left: 10px;">
                                            <span><?= $post->comment_count; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

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
                <div class="content__sidebar content__sidebar--top">
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
