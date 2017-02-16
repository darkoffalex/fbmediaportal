<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $widget \app\widgets\ForumPostsWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */
/* @var $type string */
/* @var $label string */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<?php if(!empty($posts)): ?>
    <section class="bottomCards">
        <div class="container">
            <div class="row">
                <div class="hidden-md-down col-lg-2"></div>
                <div class="col-md-12 col-lg-10">
                    <div class="row">
                        <div class="col-sm-12 text-xs-center">
                            <div class="bottomCards__heading"> <?= $label; ?></div>
                        </div>
                        <?php foreach ($posts as $post): ?>
                            <div class="col-sm-4 col-lg-3">
                                <a class="bottomCards__card" href="<?= $post->getUrl(); ?>">
                                    <img class="img-fluid" style="width: 220px; height: 140px;" src="<?= $post->getThumbnailUrl(220,140); ?>">
                                    <a class="bottomCards__card__title" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>