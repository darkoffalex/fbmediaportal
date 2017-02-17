<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $categories \app\models\Category[] */
/* @var $controller \app\controllers\MainController */
/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $active int */

$controller = $this->context;
$user = Yii::$app->user->identity;
?>

<?php if(!empty(($categories))): ?>
    <div class="content__menu">
        <ul>
            <?php foreach ($categories as $category): ?>
                <li class="<?= !empty($category->childrenActive) ? 'have-ul' : ''; ?> <?= $active == $category->id || in_array($active,ArrayHelper::map($category->childrenActive,'id','id')) ? 'active' : ''; ?>">
                    <a href="<?= empty($category->childrenActive) ? $category->getUrl() : '#'; ?>"><?= $category->trl->name; ?></a>
                    <?php if(!empty($category->childrenActive)): ?>
                        <ul>
                            <?php foreach ($category->childrenActive as $child): ?>
                                <li class="<?= !empty($child->childrenActive) ? 'have-ul-second' : ''; ?> <?= $active == $child->id ? 'active' : ''; ?>">
                                    <a href="<?= $child->getUrl(); ?>"><?= $child->trl->name; ?></a>
                                    <?php if(!empty($child->childrenActive)): ?>
                                        <ul>
                                            <?php foreach ($child->childrenActive as $subChild): ?>
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

