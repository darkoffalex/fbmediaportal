<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $categories \app\models\Category[] */
/* @var $widget \app\widgets\MainMenuWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<section id="navArea">
    <nav class="navbar navbar-inverse" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav main_nav">
                <li class="active">
                    <a href="<?= Url::to(['/site/index']); ?>">
                        <span class="fa fa-home desktop-home"></span><span class="mobile-show"></span>
                    </a>
                </li>
                <?php foreach($categories as $category): ?>
                    <?php if(empty($category->children)): ?>
                        <?php $slugTitle = ArrayHelper::getValue($category->trl,'name',$category->name); ?>
                        <li><a href="<?= Url::to(['category/show','id' => $category->id, 'title' => Help::slug($slugTitle)]); ?>"><?= $category->trl->name; ?></a></li>
                    <?php else: ?>
                        <li class="dropdown">
                            <?php $slugTitle = ArrayHelper::getValue($category->trl,'name',$category->name); ?>
                            <a href="<?= Url::to(['category/show','id' => $category->id, 'title' => Help::slug($slugTitle)]); ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?= $category->trl->name; ?></a>
                            <ul class="dropdown-menu" role="menu">
                                <?php foreach($category->children as $child): ?>
                                    <?php $slugTitle = ArrayHelper::getValue($child->trl,'name',$child->name); ?>
                                    <li><a href="<?= Url::to(['/category/show','id' => $child->id, 'title' => Help::slug($slugTitle)]); ?>"><?= $child->trl->name; ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</section>