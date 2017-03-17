<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $posts \app\models\Post[] */
/* @var $controller \app\controllers\MainController */
/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $error string */
/* @var $label string */

$controller = $this->context;
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
                        <?php foreach ($posts as $index => $post): ?>
                            <div class="col-sm-4 col-lg-3 <?php if($index == 2): ?> hidden-xs-down <?php endif; ?> <?php if($index == 3): ?> hidden-md-down <?php endif; ?>">
                                <div class="bottomCards__card">
                                    <a href="<?= $post->getUrl(); ?>"><img class="img-fluid" src="<?= $post->getThumbnailUrl(440,280); ?>"></a>
                                    <a class="bottomCards__card__title" href="<?= $post->getUrl(); ?>"><?= $post->trl->name; ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>