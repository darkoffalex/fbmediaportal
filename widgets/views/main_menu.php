<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $categories \app\models\Category[] */
/* @var $widget \app\widgets\MainMenuWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */
/* @var $active int */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<?php if(!empty(($categories))): ?>
    <div class="content__menu">
        <ul>
            <?php foreach ($categories as $category): ?>
                <li class="<?= !empty($category->children) ? 'have-ul' : ''; ?> <?= $active == $category->id || in_array($active,ArrayHelper::map($category->children,'id','id')) ? 'active' : ''; ?>">
                    <a href="<?= empty($category->children) ? $category->getUrl() : '#'; ?>"><?= $category->trl->name; ?></a>
                    <?php if(!empty($category->children)): ?>
                        <ul>
                            <?php foreach ($category->children as $child): ?>
                                <li class="<?= !empty($child->children) ? 'have-ul-second' : ''; ?> <?= $active == $child->id ? 'active' : ''; ?>">
                                    <a href="<?= $child->getUrl(); ?>"><?= $child->trl->name; ?></a>
                                    <?php if(!empty($child->children)): ?>
                                        <ul>
                                            <?php foreach ($child->children as $subChild): ?>
                                                <li><a href="<?= $subChild->getUrl(); ?>"><?= $subChild->trl->name; ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <div class="content__menu">
        <ul>
            <li><a href="#">Нет рубрик</a></li>
        </ul>
    </div>
<?php endif; ?>

