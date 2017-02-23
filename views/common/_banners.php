<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $banners \app\models\Banner[] */
/* @var $controller \app\controllers\MainController */
/* @var $this \yii\web\View */
/* @var $user \app\models\User */
/* @var $error string */
/* @var $attributes array */
/* @var $imgAttributes array */

$controller = $this->context;
$user = Yii::$app->user->identity;
$attributes = !empty($attributes) ? $attributes : [];
$imgAttributes = !empty($imgAttributes) ? $imgAttributes : [];
?>


<?php if(!empty($banners) && is_array($banners)): ?>
    <?php foreach($banners as $banner): ?>
        <?php if($banner->type_id == \app\helpers\Constants::BANNER_TYPE_IMAGE): ?>
            <?php $url = Url::to(['site/banner-redirect','id' => $banner->id, 'title' => Help::slug($banner->name)]); ?>
            <?= Html::a(Html::img('@web/uploads/img/'.$banner->image_filename,ArrayHelper::merge(['alt' => $banner->name, 'rel' => 'canonical'],$imgAttributes)),$url,$attributes); ?>
        <?php elseif($banner->type_id == \app\helpers\Constants::BANNER_TYPE_CODE): ?>
            <?= $banner->code; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
