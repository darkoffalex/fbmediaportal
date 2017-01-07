<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use app\helpers\Constants;
use yii\helpers\ArrayHelper;
use app\models\Category;

/* @var $model \app\models\User */
/* @var $this \yii\web\View */
/* @var $controller \app\modules\admin\controllers\UsersController */

$controller = $this->context;
?>

<div class="modal-header">
    <h4 class="modal-title"><?= Yii::t('admin','User information'); ?></h4>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-sm-12">
            <div class="col-xs-12 col-sm-8">
                <?php $roleNames = [
                    Constants::ROLE_ADMIN => Yii::t('admin','Administrator'),
                    Constants::ROLE_REDACTOR => Yii::t('admin','Redactor'),
                    Constants::ROLE_REGULAR_USER => Yii::t('admin','User'),
                ]; ?>

                <?php $typeNames = [
                    Constants::USR_TYPE_CREATED => Yii::t('admin','Created'),
                    Constants::USR_TYPE_IMPORTED => Yii::t('admin','Imported'),
                    Constants::USR_TYPE_FB_AUTHORIZED => Yii::t('admin','Authorized')
                ]; ?>

                <h2><?= $model->name.' '.$model->surname; ?></h2>
                <p><strong><?= Yii::t('admin','Username') ?>: </strong> <span class="tags"><?= $model->username; ?></span> </p>
                <p><strong><?= Yii::t('admin','Role ID') ?>: </strong> <span class="tags"><?= $roleNames[$model->role_id]; ?></span> </p>
                <p><strong><?= Yii::t('admin','Type ID') ?>: </strong> <span class="tags"><?= $typeNames[$model->type_id]; ?></span> </p>
                <p><strong><?= Yii::t('admin','Last activity') ?>: </strong> <span class="tags"><?= $model->last_online_at; ?></span> </p>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
                <figure>
                    <img src="<?= Url::to('@web/img/no_user.png'); ?>" alt="" class="img-circle img-responsive">
                </figure>
            </div>
        </div>
        <div class="col-xs-12 divider text-center">
            <div class="col-xs-12 col-sm-6 emphasis">
                <h2><strong> <?= (int)$model->counter_posts; ?> </strong></h2>
                <p><small><?= Yii::t('admin','Posts'); ?></small></p>
                <a href="#" class="btn btn-success btn-block"><span class="fa fa-eye"></span> <?= Yii::t('admin','View'); ?></a>
            </div>
            <div class="col-xs-12 col-sm-6 emphasis">
                <h2><strong> <?= (int)$model->counter_comments; ?></strong></h2>
                <p><small><?= Yii::t('admin','Comments'); ?></small></p>
                <a href="#" class="btn btn-success btn-block"><span class="fa fa-eye"></span> <?= Yii::t('admin','View'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Yii::t('admin','Close'); ?></button>
</div>