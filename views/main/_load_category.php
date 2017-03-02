<?php
use app\helpers\Constants;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\helpers\Help;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $controller \app\controllers\MainController */
/* @var $mainPosts \app\models\Post[] */
/* @var $forumPosts \app\models\Post[] */

$user = Yii::$app->user->identity;
$controller = $this->context;
?>
<?php if(!empty($mainPosts)): ?>
<section class="content">
    <div class="container">
        <div class="row">
            <div class="hidden-md-down col-lg-2"></div>
            <div class="col-sm-8 col-lg-7 no-pad-r">

                <?php if($mainPosts[0]->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                    <?php Help::swap($mainPosts,0,1); ?>
                <?php endif; ?>

                <?php foreach ($mainPosts as $index => $post): ?>
                    <?php if($post->content_type_id == Constants::CONTENT_TYPE_VIDEO): ?>
                        <?= $this->render('_post_big',['category' => null, 'post' => $post]); ?>
                    <?php else: ?>
                        <?= $this->render('_post_small',['category' => null, 'post' => $post]); ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!--sidebar-->
            <div class="col-sm-4 col-lg-3 no-pad-l hidden-xs-down"><div class="content__sidebar"></div></div>

        </div>
    </div>
</section>
<?php endif; ?>

<?php if(!empty($forumPosts)): ?>
    <?= $this->render('/common/_forum_posts',['posts' => $forumPosts, 'label' => 'Форум']); ?>
<?php endif; ?>
