<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<header class="header">
    <div class="wrap">
        <div class="container">
            <div class="row">
                <div class="col-lg-2"><a href="/"><img class="img-responsive" src="<?= Url::to('@web/img/logo.jpg'); ?>"></a></div>
                <div class="col-lg-8"><a href="/"><img class="img-responsive" src="<?= Url::to('@web/img/banner.jpg'); ?>"></a></div>
                <div class="col-lg-2">ПОИСК И АВТОРИЗАЦИЯ</div>
            </div>
        </div>
    </div>
</header>

<div class="wrap">
    <div class="container">
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
