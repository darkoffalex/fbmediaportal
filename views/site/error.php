<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>

<section id="contentSection">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="left_content">
                <div class="error_page">
                    <h3><?= Html::encode($this->title) ?></h3>
                    <h1>404</h1>
                    <p><?= nl2br(Html::encode($message)) ?></p>
                    <span></span>
                    <a href="<?= \yii\helpers\Url::to(['/site/index']); ?>" class="wow fadeInLeftBig animated" style="visibility: visible; animation-name: fadeInLeftBig;">На главную</a> </div>
            </div>
        </div>
    </div>
</section>