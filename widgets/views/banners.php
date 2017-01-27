<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\helpers\Help;
use yii\helpers\ArrayHelper;

/* @var $banners \app\models\Banner[] */
/* @var $attributes array */
/* @var $widget \app\widgets\BannersWidget */
/* @var $this \yii\web\View */
/* @var $user \yii\web\User */

$widget = $this->context;
$user = Yii::$app->user->identity;
?>

<?php foreach($banners as $banner): ?>
    <?php $url = Url::to(['site/banner-redirect','id' => $banner->id, 'title' => Help::slug($banner->name)]); ?>
    <?= Html::a(Html::img('@web/uploads/img/'.$banner->image_filename,['alt' => $banner->name]),$url,$attributes); ?>
<?php endforeach; ?>