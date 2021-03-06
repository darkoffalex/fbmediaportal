<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;
use app\helpers\Help;
use yii\imagine\BaseImage;
use yii\imagine\Image;
use Imagine\Image\ManipulatorInterface;

/* @var $post \app\models\Post */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\PostsController */

$controller = $this->context;
/* @var $languages \app\models\Language[] */
$languages = \app\models\Language::find()->orderBy('id ASC')->all();
?>

<tbody>
<tr>
    <th><?= Yii::t('admin','Preview'); ?></th>
    <th><?= Yii::t('admin','Sizes'); ?></th>
    <th><?= Yii::t('admin','Status'); ?></th>
    <th><?= Yii::t('admin','Actions'); ?></th>
</tr>
<?php if(!empty($post->postImages)): ?>
    <?php foreach($post->postImages as $image): ?>
        <tr>
            <td>
                <?php $name = $image->getATrl($languages[0]->prefix)->name;?>
                <img title="<?= $name; ?>" alt="<?= $name; ?>" class="img-thumbnail" width="300" src="<?= $image->need_crop ? $image->getCroppedUrl().'?'.Help::rds(6) : $image->getFullUrl(); ?>">
            </td>
            <td>
                <?php $filePath = $image->need_crop ? $image->getCroppedUrl(706,311,true,true) : $image->getFullPath(); ?>
                <?php
                if(!empty($filePath) && !$image->is_external){
                    try{
                        $img = Image::getImagine()->open($filePath);
                        $w = $img->getSize()->getWidth();
                        $h = $img->getSize()->getHeight();
                        echo "{$w} x {$h}";
                    }catch (Exception $ex){
                        echo Yii::t('admin','Unknown');
                    }
                }else{
                    echo Yii::t('admin','Unknown');
                }
                ?>
            </td>
            <td>
                <?php if($image->status_id == Constants::STATUS_ENABLED): ?>
                    <span class="label label-success"><?= Yii::t('admin','Enabled'); ?></span>
                <?php elseif($image->status_id == Constants::STATUS_DISABLED): ?>
                    <span class="label label-danger"><?= Yii::t('admin','Disabled'); ?></span>
                <?php endif; ?>
            </td>
            <td>
                <a href="<?= Url::to(['/admin/posts/delete-image', 'id' => $image->id]); ?>" data-ajax-reloader=".ajax-reloadable" title="<?= Yii::t('admin','Delete'); ?>" aria-label="<?= Yii::t('admin','Delete'); ?>" data-confirm-ajax="<?= Yii::t('yii','Are you sure you want to delete this item?') ?>"><span class="glyphicon glyphicon-trash"></span></a>
                &nbsp;
                <a href="<?= Url::to(['/admin/posts/edit-image', 'id' => $image->id]); ?>" data-toggle="modal" data-target=".modal"><span class="glyphicon glyphicon-pencil"></span></a>
                &nbsp;
                <a href="<?= Url::to(['/admin/posts/move-image', 'id' => $image->id, 'dir' => 'up']); ?>" data-ajax-reloader=".ajax-reloadable"><span class="glyphicon glyphicon-arrow-up"></span></a>
                &nbsp;
                <a href="<?= Url::to(['/admin/posts/move-image', 'id' => $image->id, 'dir' => 'down']); ?>" data-ajax-reloader=".ajax-reloadable"><span class="glyphicon glyphicon-arrow-down"></span></a>
                <?php if(!$image->is_external && $image->need_crop): ?>
                    &nbsp;
                    <a href="<?= Url::to(['/admin/posts/crop-image', 'id' => $image->id]); ?>" data-toggle="modal" data-target=".modal"><i class="fa fa-crop"></i></a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="3"><?= Yii::t('admin','Post has no images'); ?></td>
    </tr>
<?php endif; ?>
</tbody>

